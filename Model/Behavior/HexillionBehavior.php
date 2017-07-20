<?php

class HexillionBehavior extends ModelBehavior 
{
	public $settings = array();
	
	private $hex_sessionKey = false;
	
	public $hex_balance = false; // track the balance 
	
	public $fail_count = 0;
	
	public $Hex_disabled = false;
	
	public $disableLengths = array();
	
	public $defaultTTL = 600; // default ttl when none is reported
	
	public function setup(Model $Model, $settings = array())
	{
		$Model->Hex_disabled = $this->Hex_disabled;
		
		// load the settings for this behavior/vendor
		$this->settings = Configure::read('Hexillion.settings');
		
		// make sure the Nslookup Behavior is loaded
		if (!$Model->Behaviors->loaded('Nslookup'))
		{
			throw new NotImplementedException('Nslookup Behavior isn\'t loaded');
		}
		
		$Model->Hex_disabled = $this->Hex_disabled = false;
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
	
	public function Hex_getStats(Model $Model)
	{
		$out = array();
		$Model->Usage_updateCounts('hexillion_called', 'source_stat');
		
		$query_options_auth = $this->settings['auth_query'];
		$query_options_auth = array_merge(array('username' => false, 'password' => false), $query_options_auth);
		
		// login to hexillion
		if(!$this->hex_sessionKey)
		{
			$content = $this->NS_getRemote(
				$Model, 
				$this->settings['url_base_auth'], 
				$query_options_auth,
				'post'
			);
			
			if($content)
			{
				if($xmlArray = Xml::toArray(Xml::build($content)))
				{
					if(isset($xmlArray['AuthResult']['SessionKey']) and trim($xmlArray['AuthResult']['SessionKey']))
					{
						$this->hex_sessionKey = $xmlArray['AuthResult']['SessionKey'];
					}
				}
			}
		}
		
		// failed login
		if(!$this->hex_sessionKey)
		{
			$Model->Usage_updateCounts('hexillion_login_failed', 'source_stat');
			$Model->shellOut(__('Unable to login and get the session key.'), 'source_stats', 'warning');
			return $out;
		}
		
		$query_options = $this->settings['account_query'];
		$query_options = array_merge($query_options, array('sessionkey' => $this->hex_sessionKey));
		
		$content = $this->NS_getRemote(
			$Model, 
			$this->settings['account_url'],
			$query_options,
			'post'
		);
	
		if(!$content)
		{
			$notify = true;
			// suppress messages with timeout issue
			if($this->curlErrno == 28)
			{
				$Model->Usage_updateCounts('hexillion_timeout', 'source_stat');
				$notify = false;
			}
			$Model->Usage_updateCounts('hexillion_no_content', 'source_stat');
			$Model->shellOut(__('No content returned.'), 'source_stats', 'warning', $notify);
			return $out;
		}
		
		if(!$xmlArray = Xml::toArray(Xml::build($content)))
		{
			return $out;
		}
		
		$xmlArray = Set::filter($xmlArray); // filters out empty items from the array
		$xmlArray = Set::flatten($xmlArray);
		
		$th = $td = array();
		$i = 0;
		foreach($xmlArray as $key => $value)
		{
			if(!preg_match('/\.table\.tr\./i', $key)) continue;
			
			$value = trim($value);
			
			$matches = array();
			if(preg_match('/\.table\.tr\.(\d+)\.th$/i', $key, $matches))
			{
				if(isset($matches[1]))
				{
					$i = (int)$matches[1];
					
					$final_index = $value;
					if(preg_match('/^user/i', $value))
						$final_index = 'user';
					elseif(preg_match('/^current.*balance$/i', $value))
						$final_index = 'balance';
					elseif(preg_match('/^last\s+refill/i', $value))
						$final_index = 'last_refill_date';
					elseif(preg_match('/^balance.*refill$/i', $value))
						$final_index = 'last_refill_balance';
					elseif(preg_match('/^Estimated\s+depletion\s+date$/i', $value))
						$final_index = 'est_depletion_date';
					elseif(preg_match('/^Estimated\s+time\s+till\s+depletion$/i', $value))
						$final_index = 'est_depletion_time_left';
					elseif(preg_match('/^Average\s+usage\s+rate/i', $value))
						$final_index = 'avg_usage_rate';
					else
						$final_index = Inflector::slug(strtolower($value));
					
					$th[$i] = $final_index;
				}
			}
			if(preg_match('/\.table\.tr\.\d+\.td\.\d+$/i', $key))
			{
				$td[$i] = $value;
			}
			elseif(preg_match('/\.table\.tr\.\d+\.td\.\d+\.[^a]/i', $key))
			{
				if(preg_match('/\.table\.tr\.\d+\.td\.\d+\..*\@$/i', $key))
				{
					if(!isset($td[$i])) $td[$i] = '';
					$td[$i] .= $value. ' ';
				}
			}
		}
		
		// clean up the values
		foreach($td as $i => $value)
		{
			$value = trim($value);
			if(preg_match('/unit(s)?/i', $value))
			{
				$parts = preg_split('/unit(s)?/', $value);
				foreach($parts as $j => $part)
				{
					$part = trim($part);
					if($part === '') unset($parts[$j]);
				}
				$parts[0] = preg_replace('/\s+/', '', $parts[0]);
				// fix encoding issue with odd spaces instead of commas in the numbers
				$fixed = '';
				for($x = 0; $x < strlen($parts[0]); $x++)
				{
					if(ctype_digit($parts[0][$x]))
						$fixed .= $parts[0][$x];
				}
				$parts[0] = $fixed;
				$value = implode(' units', $parts);
			}
			$td[$i] = $value;
		}
		
		$out = array();
		foreach($th as $i => $value)
		{
			$out[$value] = (isset($td[$i])?$td[$i]:false);
			if(preg_match('/date/i', $value))
			{
				$out[$value] = date('Y-m-d H:i:s', strtotime($out[$value]));
			}
		}
		
		$out['percent_available'] = 0;
		if(isset($out['balance']) and isset($out['last_refill_balance']))
		{
			$out['percent_available'] = ceil( (($out['balance'] / $out['last_refill_balance']) * 100) );
		}
		$Model->Usage_updateCounts('hexillion_percent_available', 'source_stat', (int)$out['percent_available']);
		
		if($out['percent_available'] < 20)
		{
			$out['mark_important'] = true;
		}
		
		$balance = 0;
		if(isset($out['balance'])) $balance = $out['balance'];
		$Model->Usage_updateCounts('hexillion_balance', 'source_stat', (int)$balance);
		
		if($balance < 1000)
		{
			$out['mark_important'] = true;
		}
		
		$average_usage = 0;
		if(isset($out['avg_usage_rate']))
		{
			$parts = preg_split('/\s+/', $out['avg_usage_rate']);
			$average_usage = (int)$parts[0];
		}
		$Model->Usage_updateCounts('hexillion_average_usage', 'source_stat', (int)$average_usage);
		
		return $out;
	}
	
	public function Hex_getIps(Model $Model, $hostname, $automatic = false)
	{
		return $this->Hex_getContent($Model, $hostname, 'hostname', $automatic);
	}
	
	public function Hex_getHostnames(Model $Model, $ip, $automatic = false)
	{
		return $this->Hex_getContent($Model, $ip, 'ip', $automatic);
	}
	
	public function Hex_getContent(Model $Model, $host = null, $type = null, $automatic = false)
	{
		$out = array();
		
		if(!$host) return $out;
		if($type != 'ip' and $type != 'hostname') return $out;
		
		$Model->modelError = false;
		
		$Model->Hex_disabled = $this->Hex_disabled = false;
		
		if($this->Hex_isDisabled($Model))
		{
			$Model->modelError = __('Hexillion is temporarly disabled.');
			$Model->shellOut($Model->modelError, 'hexillion', 'error');
			$Model->Hex_disabled = $this->Hex_disabled = true;
			return $out;
		}
		
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
		
		$out['hexillion'] = $this->Hex_getResults($Model, $host, $type, $automatic);
		
		Configure::write('debug', $debug);
		
		return $out;
	}
	
	public function Hex_getResults(Model $Model, $query = false, $type = false, $automatic = false)
	{
		$out = array();
		
		// check if hexillion is disabled before we even begin queries, as there is no point
	 	if($this->Hex_isDisabled($Model))
	 	{
	 		$this->modelError = __('Hexillion is disabled.');
	 		return false;
	 	}
		
		$query_options_auth = $this->settings['auth_query'];
		$query_options_auth = array_merge(array('username' => false, 'password' => false), $query_options_auth);
		
		// login to hexillion
		if(!$this->hex_sessionKey)
		{
			$Model->shellOut(__('Logging in.'), 'nslookup', 'info');
			$content = $Model->NS_getRemote(
				$this->settings['url_base_auth'], 
				$query_options_auth,
				'post'
			);
		
			$Model->Usage_updateCounts('login', 'nslookup_hexillion');
			
			if($content)
			{
				if($xmlArray = Xml::toArray(Xml::build($content)))
				{
					if(isset($xmlArray['AuthResult']['SessionKey']) and trim($xmlArray['AuthResult']['SessionKey']))
					{
						$this->hex_sessionKey = $xmlArray['AuthResult']['SessionKey'];
					}
				}
			}
		}
		
		// failed login
		if(!$this->hex_sessionKey)
		{
			$Model->shellOut(__('Unable to login and get the session key.'), 'nslookup', 'warning');
			return $out;
		}
		
		$query_options = $this->settings['host_query'];
		$query_options = array_merge($query_options, array('addr' => $query, 'sessionkey' => $this->hex_sessionKey));
		
		$content = $Model->NS_getRemote(
			$this->settings['url_base'],
			$query_options,
			'post'
		);
	
		if(!$content)
		{
			$notify = true;
			// suppress messages with timeout issue
			if($this->curlErrno == 28)
				$notify = false;
			$Model->shellOut(__('No content returned. -- Host: %s', $query), 'nslookup', 'warning', $notify);
			return $out;
		}
		
		$Model->Usage_updateCounts('all', 'nslookup_hexillion');
		$Model->Usage_updateCounts('hexillion', 'nslookup');
		
		if($Model->getting_live)
		{
			$Model->Usage_updateCounts('remote', 'nslookup_hexillion');
		}
		else
		{
			$Model->Usage_updateCounts('cached', 'nslookup_hexillion');
		}
		
		// clean up the content
		$content = str_ireplace('</td>', "\t", $content);
		$content = str_ireplace('<br>', "\n", $content);
		$content = str_ireplace('<br/>', "\n", $content);
		$content = str_ireplace('<br />', "\n", $content);
		$content = strip_tags($content);
		$content = explode("\n", $content);
		
		foreach($content as $i => $line)
		{
			$content[$i] = trim($content[$i]);
			if(!$content[$i])
			{
				unset($content[$i]);
				continue;
			}
			
			$content[$i] = strtolower($content[$i]);
		}
		// reset the index, but keep the proper order
		// garbage cleanup and reindexing
		$content = array_values($content);
		
		$ptr = 'false';
		if($type == 'hostname')
		{
			$Model->Usage_updateCounts('hostname', 'nslookup_hexillion');
		}
		elseif($type == 'ip')
		{
			$ptr = implode(".",array_reverse(explode(".",$query))).".in-addr.arpa";
			$Model->Usage_updateCounts('ipaddress', 'nslookup_hexillion');
		}
		
		//////// find the results
		
		// defaults
		$defaults = array('ttl' => $this->defaultTTL, 'first_seen' => date('Y-m-d H:i:s'), 'last_seen' => date('Y-m-d H:i:s'));
		// track the ttls
		$ttls = array();
		
		$next_line = false;
		foreach($content as $i => $line)
		{
			// Look for the balance
			if(preg_match('/^balance/i', $line))
			{
				$this->hex_balance = trim($content[$i+1]);
				// check if the balance is below balance threshold
				$parts = preg_split('/\s+/', $this->hex_balance);
				if(isset($parts[0]))
				{
					(int)$this->hex_balance = $parts[0];
					if($this->hex_balance == 0 )
					{
						$Model->shellOut(__('Hexillion Balance empty!: %s', $this->hex_balance), 'nslookup', 'error');
						
						$Model->Usage_updateCounts('balance_empty', 'nslookup_hexillion');
						// disable the parser for this session as well, if no units are available
						$this->Hex_disable($Model, 'day');
						continue;
					}
					elseif($this->hex_balance <= $this->settings['balance_threshold'])
					{
						$Model->Usage_updateCounts('balance_low', 'nslookup_hexillion');
						$Model->shellOut(__('Hexillion Balance: %s', $this->hex_balance), 'nslookup', 'warning');
						continue;
					}
				}
				$Model->shellOut(__('Hexillion Balance: %s', $this->hex_balance), 'nslookup', 'info');
				continue;
			}
			
			// for hostnames
			if($type == 'hostname')
			{
				// list of addresses at the top
				if(preg_match('/^addresses/i', $line))
				{
					$next_line = true;
					continue;
				}
				
				if($next_line)
				{
					// bypass the ipv6
					if(strpos($line, ':') !== false) continue;
					
					// get the result
					$matches = array();
					if(preg_match('/[\d\.]+/', $line, $matches))
					{
						$out[$matches[0]] = $defaults;
						$ttls[$matches[0]] = $this->defaultTTL;
						continue;
					}
					$next_line = false;
				}
				
				// find the actual A records
				$matches = array();
				if(preg_match('/a\s+([\d\.]+)\s+([\d]+)s/', $line, $matches))
				{
					// bypass other A records
					if(!preg_match('/^'. preg_quote($query). '/i', $line)) continue;
					if(!isset($matches[1]) or !$matches[1]) continue;
					if(!isset($matches[2]) or !$matches[2]) continue;
					
					$out[$matches[1]] = $defaults;
					$ttls[$matches[1]] = $matches[2];
				}
			}
			elseif($type == 'ip')
			{
				// list of hostnames at the top
				if(preg_match('/^canonical name/i', $line))
				{
					$next_line = true;
					continue;
				}
				
				if($next_line)
				{	
					$matches = array();
					if(preg_match('/^([\w\-\.]+)\./', $line, $matches))
					{
						$out[$matches[1]] = $defaults;
						$ttls[$matches[1]] = $this->defaultTTL;
						continue;
					}
					$next_line = false;
				}
				
				// find the actual A records
				$matches = array();
				
				if(preg_match('/^'.preg_quote($ptr).'\s+in\s+ptr\s+([\w\-\.]+)\s+([\d]+)s/', $line, $matches))
				{
					if(!isset($matches[1]) or !$matches[1]) continue;
					if(!isset($matches[2]) or !$matches[2]) continue;
					
					$out[$matches[1]] = $defaults;
					$ttls[$matches[1]] = $matches[2];
				}
			}
		}
		
		// update the result arrays
		foreach($out as $result => $values)
		{
			if(isset($ttls[$result])) $out[$result]['ttl'] = $ttls[$result];
		}
		
		if(count($out))
		{
			$Model->Usage_updateCounts('results_found', 'nslookup_hexillion');
			$Model->Usage_updateCounts('nslookup_hexillion', 'results_found');
		}
		else
		{
			$Model->Usage_updateCounts('results_empty', 'nslookup_hexillion');
			$Model->Usage_updateCounts('nslookup_hexillion', 'results_empty');
		}
		
		$Model->shellOut(__('Found %s results for: %s', count($out), $query), 'nslookup', 'info');
		return $out;
	}
	
	public function Hex_getLimit(Model $Model, $time_period = 'minute')
	{
		if(isset($this->settings['limits'][$time_period]))
			return $this->settings['limits'][$time_period];
		
		return false;
	}
	
	public function Hex_disable(Model $Model, $disableLength = 'hour')
	{
		$Model->Hex_disabled = $this->Hex_disabled = true;
		
		$Model->Usage_updateCounts('hexillion', 'disabled');
		
		// cache the disabled state for a set period of time
		if($disableLength and isset($this->disableLengths[$disableLength]))
		{
			$Model->Usage_updateCounts('hexillion', 'disabled_'. $disableLength);
			
			$timestamp = $this->disableLengths[$disableLength];
			
			// if it doesn't previously exist, then send a notice
			$old_timestamp = Cache::read('disabled_hexillion', 'nslookup_long');
			
			Cache::write('disabled_hexillion', $timestamp, 'nslookup_long');
			if(!$old_timestamp)
			{
				$Model->shellOut(__('The Source: Hexillion is disabled for the rest of this %s.', $disableLength), 'nslookup', 'notice');
			}
		}
	}
	
	public function Hex_isDisabled(Model $Model, $disableLength = false, $bypass_session_disable = false)
	{
		$Model->Hex_disabled = $this->Hex_disabled;
		
		if($this->Hex_disabled)
		{
			return true;
		}
		
		
		if($disableLength and isset($this->disableLengths[$disableLength]))
		{
			$timestamp = $this->disableLengths[$disableLength];
			$old_timestamp = Cache::read('disabled_hexillion', 'nslookup_long');
			
			if($old_timestamp and $timestamp == $old_timestamp)
			{
				// refresh the cache
				Cache::write('disabled_hexillion', $timestamp, 'nslookup_long');
				
				// set the session disable cache
				$Model->Hex_disabled = $this->Hex_disabled = true;
			
				return true;
			}
		}
		// check all of them to see if any of them are disabled
		else
		{
			$old_timestamp = Cache::read('disabled_hexillion', 'nslookup_long');
			foreach($this->disableLengths as $disableLength => $timestamp)
			{
				if($old_timestamp and $timestamp == $old_timestamp)
				{
					// refresh the cache
					Cache::write('disabled_hexillion', $timestamp, 'nslookup_long');
					
					// set the session disable cache
					$Model->Hex_disabled = $this->Hex_disabled = true;
				
					return true;
				}
			}
		}
		
		// remove the disabled cache, since we're not disabled that way
		Cache::delete('disabled_hexillion', 'nslookup_long');
		
		// check the config to see if it's disabled there
		if(isset($this->settings['disabled']) and $this->settings['disabled'] and !$bypass_session_disable)
		{
			// disable it for this session
			$this->Hex_disable($Model, $disableLength);
			return true;
		}
		
		return false;
	} 
	
}