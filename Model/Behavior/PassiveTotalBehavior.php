<?php

class PassiveTotalBehavior extends ModelBehavior 
{
	public $settings = array(
		'apikey' => false,
	);
	
	public $fail_count = 0;
	
	public $PT_disabled = false;
	
	public $disableLengths = array();
	
	public function setup(Model $Model, $settings = array())
	{
		// load the settings for this behavior/vendor
		$this->settings = Configure::read('PassiveTotal.settings');
		
		// make sure the Nslookup Behavior is loaded
		if (!isset($this->settings['apikey']))
		{
			throw new NotFoundException('No API key is set');
		}
		
		// make sure the Nslookup Behavior is loaded
		if (!$Model->Behaviors->loaded('Nslookup'))
		{
			throw new NotImplementedException('Nslookup Behavior isn\'t loaded');
		}
		
		$Model->PT_disabled = $this->PT_disabled = false;
		$this->disableLengths = array('minute' => date('YmdHi'), 'hour' => date('YmdH'), 'day' => date('Ymd'), 'week' => date('YW'), 'month' => date('Ym'));
		
		if(!$Model->Behaviors->loaded('Usage.Usage'))
		{
			$Model->Behaviors->load('Usage.Usage');
		}
		
		if(!$Model->Behaviors->enabled('Usage.Usage'))
		{
			$Model->Behaviors->enable('Usage.Usage');
		}
	}
	
	public function PT_getStats(Model $Model)
	{
	// used to get stats from passive total
		
		$out = array();
		
return array('passivetotal' => $out);
		
		// weird situation where the debug setting is being blown out
		$debug = Configure::read('debug');
		
		$Model->modelError = false;
		$parser_keys = array_keys($this->parser_settings);
		
		
		// go through the parsers
		shuffle($parser_keys); // randomize the array to choose a random one to go first
		foreach($parser_keys as $parser_key) 
		{
			$method = 'stat_'. $parser_key;
			if($this->isDisabledParser($Model, $parser_key)) continue;
			if(!method_exists($this, $method)) continue;
			
			$Model->shellOut(__('Retrieving Stats for: %s', $parser_key), 'source_stats');
			$out[$parser_key] = $this->{$method}($Model);
			Configure::write('debug', $debug);
		}
		
		return $out;
	}
	
	public function PT_getIps(Model $Model, $hostname, $automatic = false)
	{
		return $this->PT_getContent($Model, $hostname, 'hostname', $automatic);
	}
	
	public function PT_getHostnames(Model $Model, $ip, $automatic = false)
	{
		return $this->PT_getContent($Model, $ip, 'ip', $automatic);
	}
	
	public function PT_getContent(Model $Model, $host = null, $type = null, $automatic = false)
	{
		$out = array();
		
		if(!$host) return $out;
		if($type != 'ip' and $type != 'hostname') return $out;
		
		$Model->modelError = false;
		
		
		if($this->PT_isDisabled($Model))
		{
			
			$Model->modelError = __('PassiveTotal is temporarly disabled.');
			$Model->shellOut($Model->modelError, 'passivetotal', 'error');
			$Model->PT_disabled = $this->PT_disabled = true;
			return $out;
		}
		
		// check to see if the Nslookup Behavior has been added to the Model
		// if so, use some of its methods to validate the host
		
		// see if it's exempt, if so, ignore this host
		if($Model->NS_exemptCheck($host, $type))
		{
			return $out;
		}
		
		// see if it's a local, if so, do a local lookup
		if($Model->NS_localCheck($host, $type))
		{
			$out['local'] = $Model->NS_localLookup($host, $type);
			return $out;
		}
		
		// weird situation where the debug setting is being blown out
		$debug = Configure::read('debug');
		
		$out['passivetotal'] = $this->PT_getResults($Model, $host, $type, $automatic);
		
		Configure::write('debug', $debug);
		
		return $out;
	}
	
	public function PT_getResults(Model $Model, $host = false, $type = false, $automatic = false)
	{
		$out = array();
		
		if($this->PT_isDisabled($Model))
		{
			$Model->modelError = __('PassiveTotal is temporarly disabled.');
			$Model->shellOut($Model->modelError, 'passivetotal', 'error');
			return $out;
		}
		
		$Model->shellOut(__('Looking up: %s', $host), 'passivetotal', 'info');
		
		if(!$host or !$type)
		{
			$Model->shellOut(__('Host or type isn\' set'), 'passivetotal', 'error');
			return $out;
		}
		
		if(!isset($this->settings['apikey']))
		{
			$Model->shellOut(__('No apikey set for looking up a host.'), 'passivetotal', 'error');
			return $out;
		}
		
		if(!isset($this->settings['uri']))
		{
			$Model->shellOut(__('No uri set for looking up a host.'), 'passivetotal', 'error');
			return $out;
		}
		
		$results = false;
		$uri = false;
		$query = array(
			'apikey' => $this->settings['apikey'],
			'value' => $host,
		);
		
		if($type == 'ip')
		{
			$Model->Usage_updateCounts('passivetotal', 'ipaddress');
			$Model->Usage_updateCounts('ipaddress', 'passivetotal');
		}
		else
		{
			$Model->Usage_updateCounts('passivetotal', $type);
			$Model->Usage_updateCounts($type, 'passivetotal');
		}
		
		if($results = $Model->NS_getRemote($this->settings['uri'], $query, 'post', array('Accept' => 'application/json')))
		{
			$Model->Usage_updateCounts('lookup', 'passivetotal');
			if(isset($Model->getting_live))
			{
				if($Model->getting_live)
				{
					$Model->Usage_updateCounts('remote', 'passivetotal');
					$Model->Usage_updateCounts('passivetotal', 'remote');
				}
				else
				{
					$Model->Usage_updateCounts('cached', 'passivetotal');
					$Model->Usage_updateCounts('passivetotal', 'cached');
				}
			}
			$results = json_decode($results);
			$results = $Model->Common_objectToArray($results);
			$results = (isset($results['results']['resolutions'])?$results['results']['resolutions']:false);
		}
		
		if(!$results)
		{
			if(isset($Model->curlErrno) and $Model->curlErrno)
			{
				$this->fail_count++;
			}
			
			if($this->fail_count == 10 or $Model->curlErrno == 7)
			{
				$this->PT_disable($Model);
				$Model->PT_disabled = $this->PT_disabled;
			}
			return array();
		}
		
		$out = array();
		foreach($results as $result)
		{
			$value = $result['value'];
			$subsource = null;
			
			if(isset($result['source']))
			{
				$subsource = $result['source'];
				if(is_array($subsource))
				{
					sort($subsource);
					$subsource = implode(',', $subsource);
				}
			}
			
			$out[$value] = array(
				'ttl' => 600,
				'first_seen' => (isset($result['firstSeen'])?$result['firstSeen']:null),
				'last_seen' => (isset($result['lastSeen'])?$result['lastSeen']:null),
				'subsource' => $subsource,
			);
		}
		
		return $out;
	}
	
	public function PT_getLimit(Model $Model, $time_period = 'minute')
	{
		if(isset($this->settings['limits'][$time_period]))
			return $this->settings['limits'][$time_period];
		
		return false;
	}
	
	public function PT_disable(Model $Model, $disableLength = 'hour')
	{
		$Model->PT_disabled = $this->PT_disabled = true;
		
		$Model->Usage_updateCounts('passivetotal', 'disabled');
		
		// cache the disabled state for a set period of time
		if($disableLength and isset($this->disableLengths[$disableLength]))
		{
			$Model->Usage_updateCounts('passivetotal', 'disabled_'. $disableLength);
			
			$timestamp = $this->disableLengths[$disableLength];
			
			// if it doesn't previously exist, then send a notice
			$old_timestamp = Cache::read('disabled_passivetotal', 'nslookup_long');
			
			Cache::write('disabled_passivetotal', $timestamp, 'nslookup_long');
			if(!$old_timestamp)
			{
				$Model->shellOut(__('The Source: PassiveTotal is disabled for the rest of this %s.', $disableLength), 'nslookup', 'notice');
			}
		}
	}
	
	public function PT_isDisabled(Model $Model, $disableLength = 'hour', $bypass_session_disable = false)
	{
		if($this->PT_disabled)
		{
			return true;
		}
		
		$disableLength = 'hour';
		
		// cache the disabled state
		if($disableLength and isset($this->disableLengths[$disableLength]))
		{
			$timestamp = $this->disableLengths[$disableLength];
			$old_timestamp = Cache::read('disabled_passivetotal', 'nslookup_long');
			
			if($old_timestamp and $timestamp == $old_timestamp)
			{
				// refresh the cache
				Cache::write('disabled_passivetotal', $timestamp, 'nslookup_long');
				
				// set the session disable cache
				$Model->PT_disabled = $this->PT_disabled = true;
			
				return true;
			}
		}
		
		// check the config to see if it's disabled there
		if(isset($this->settings['disabled']) and $this->settings['disabled'] and !$bypass_session_disable)
		{
			// disable it for this session
			$this->PT_disable($Model, $disableLength);
			return true;
		}
		
		return false;
	} 
	
}