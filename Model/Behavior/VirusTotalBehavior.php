<?php

class VirusTotalBehavior extends ModelBehavior 
{
	private $Model = false;
	
	public $settings = array(
		'api_key' => false,
		'disabled' => false,
	);
	
	public $Curl = false;
	public $curlError = false;
	public $curlErrno = false;
	public $curlHeaders = false;
	public $modelID = false;
	public $rawPrefix = 'raw_data';
	
	public $getting_live = false;
	public $session_counts = array();
	public $actualModelUpdating = '';
	public $old_limits = array();
	
	public $parser_disabled = false;
	private $disableLengths = array();
	
	public $inDescruct = false;
	
	public function setup(Model $Model, $settings = array())
	{
		$this->Model = $Model;
		
		// load the settings for this behavior/vendor
		$this->settings = Configure::read('VirusTotal.settings');
		
		if (!isset($this->settings['apikey']))
		{
			throw new NotFoundException('No API key is set');
		}
		
		$this->disableLengths = array('minute' => date('YmdHi'), 'hour' => date('YmdH'), 'day' => date('Ymd'), 'week' => date('YW'), 'month' => date('Ym'), 'year' => date('Y'));
		
		register_shutdown_function(array($this, "shutdown"), $Model);
		
		if(!$Model->Behaviors->loaded('Usage.Usage'))
		{
			$Model->Behaviors->load('Usage.Usage');
		}
		
		if(!$Model->Behaviors->enabled('Usage.Usage'))
		{
			$Model->Behaviors->enable('Usage.Usage');
		}
	}
	
	public function cleanup(Model $Model)
	{
		if(!$this->inDescruct) $this->shutdown($Model);
	}
	
	public function __destruct()
	{
		if(!$this->inDescruct) $this->shutdown($this->Model);
	}
	
	public function shutdown(Model $Model) 
	{
		$this->inDescruct = true;
		$this->VT_saveCounts($this->Model);
	}
	
	public function VT_getStats(Model $Model)
	{
	// used to get stats from virus total
		
		$out = array();
		
return array('virustotal' => $out);
	
	}
	
	public function VT_getIps(Model $Model, $hostname, $automatic = false, $model_id = false)
	{
		$this->VT_setModelID($Model, $model_id);
		return $this->VT_getContent($Model, $hostname, 'hostname_dnslookup', $automatic, $model_id);
	}
	
	public function VT_getHostnames(Model $Model, $ip, $automatic = false, $model_id = false)
	{
		$this->VT_setModelID($Model, $model_id);
		return $this->VT_getContent($Model, $ip, 'ipaddress_dnslookup', $automatic, $model_id);
	}
	
	public function VT_getHostnameReport(Model $Model, $hostname, $automatic = false, $model_id = false)
	{
		$this->VT_setModelID($Model, $model_id);
		return $this->VT_getContent($Model, $hostname, 'hostname_report', $automatic, $model_id);
	}
	
	public function VT_getIpaddressReport(Model $Model, $ip, $automatic = false, $model_id = false)
	{
		$this->VT_setModelID($Model, $model_id);
		return $this->VT_getContent($Model, $ip, 'ipaddress_report', $automatic, $model_id);
	}
	
	public function VT_getFileBehavior(Model $Model, $query, $automatic = false, $model_id = false)
	{
		$this->VT_setModelID($Model, $model_id);
		return $this->VT_getContent($Model, $query, 'file_behaviour', $automatic, $model_id);
	}
	
	public function VT_getContent(Model $Model, $query = null, $lookup_type = null, $automatic = false, $model_id = false)
	{
		$out = array();
		
		if(!$query) return $out;
		
		if($this->VT_isDisabled($Model))
		{
			$Model->modelError = __('We hit their limit');
			$Model->shellOut($Model->modelError, 'virustotal', 'notice');
			return $out;
		}
		
		$Model->modelError = false;
		
		// weird situation where the debug setting is being blown out
		$debug = Configure::read('debug');
		
		$this->VT_setModelID($Model, $model_id);
		$out['virustotal'] = $this->VT_getResults($Model, $query, $lookup_type, $automatic, $model_id);
		
		$Model->Usage_updateCounts($lookup_type, 'virustotal');
		
		Configure::write('debug', $debug);
		
		return $out;
	}
	
	public function VT_getResults(Model $Model, $query_string = false, $lookup_type = false, $automatic = false, $model_id = false)
	{
		$out = array();
		
		$Model->shellOut(__('Looking up: %s', $query_string), 'virustotal', 'info');
		
		if(!$query_string or !$lookup_type)
		{
			$Model->modelError = __('Query String or Lookup Type isn\' set');
			$Model->shellOut($Model->modelError, 'virustotal', 'error');
			return $out;
		}
		
		if($this->VT_isDisabled($Model))
		{
			$Model->modelError = __('We hit their limit');
			$Model->shellOut($Model->modelError, 'virustotal', 'notice');
			return $out;
		}
		
		if(!$lookup_settings = $this->VT_getLookupSettings($Model, $lookup_type))
		{
			$Model->modelError = __('Unabled to find the lookup settings for the Type: %s', $lookup_type);
			$Model->shellOut($Model->modelError, 'virustotal', 'error');
			return $out;
		}
		
		if(isset($lookup_settings['limits']) and is_array($lookup_settings['limits']) and $lookup_settings['limits'])
		{
			$this->changeLimits($Model, $lookup_settings['limits']);
		}
		
		$results = false;
		$uri = false;
		$query = array();
		
		
		if(!isset($lookup_settings['uri']))
		{
			$Model->modelError = __('No uri setting for lookup type %s.', $lookup_type);
			$Model->shellOut($Model->modelError, 'virustotal', 'error');
			$this->resetLimits($Model);
			return $out;
		}
		if(!isset($lookup_settings['query']))
		{
			$Model->modelError = __('No query setting for lookup type %s.', $lookup_type);
			$Model->shellOut($Model->modelError, 'virustotal', 'error');
			$this->resetLimits($Model);
			return $out;
		}
		
		$uri = $lookup_settings['uri'];
		$query = array(
			$lookup_settings['query'] => $query_string,
			'apikey' => (isset($lookup_settings['apikey'])?$lookup_settings['apikey']:$this->settings['apikey']),
		);
		
		$this->VT_setModelID($Model, $model_id, (isset($lookup_settings['raw_prefix'])?$lookup_settings['raw_prefix']:false));
		
		$results = $this->VT_getRemote($Model, $uri, $query, 'get', array('Accept' => 'application/json'));
		if($results)
		{
			$results = json_decode($results);
			$results = $Model->Common_objectToArray($results);
		}
		
		if(!$results)
		{
			$this->resetLimits($Model);
			return array();
		}
		
		// the ones that need to be processed to fit a pre-existing return
		if(!in_array($lookup_type, array('ipaddress_dnslookup', 'hostname_dnslookup')))
		{
			return $results;
		}
		
		if(isset($results['response_code']) and $results['response_code'] == 0)
		{
			$Model->Usage_updateCounts('not_found', 'virustotal');
			if($lookup_type == 'ipaddress_dnslookup')
				$Model->Usage_updateCounts('not_found_ipaddress', 'virustotal');
			elseif($lookup_type == 'hostname_dnslookup')
				$Model->Usage_updateCounts('not_found_hostname', 'virustotal');
			
			$msg = __('No Results found');
			if(isset($results['verbose_msg']) and $results['verbose_msg'])
			{
				$msg = $results['verbose_msg'];
			}
			$Model->modelError = false;
			$Model->shellOut($msg, 'virustotal', 'info');
			return array();
		}
		
		if(!isset($results['resolutions']) or !$results['resolutions'])
		{
			$Model->Usage_updateCounts('no_resolutions', 'virustotal');
			if($lookup_type == 'ipaddress_dnslookup')
				$Model->Usage_updateCounts('no_resolutions_ipaddress', 'virustotal');
			elseif($lookup_type == 'hostname_dnslookup')
				$Model->Usage_updateCounts('no_resolutions_hostname', 'virustotal');
			
			$Model->modelError = false;
			$Model->shellOut(__('No resolutions found.'), 'virustotal', 'info');
			return array();
		}
		
		$out = array();
		foreach($results['resolutions'] as $result)
		{
			if(!is_array($result)) continue;
			
			if($lookup_type == 'ipaddress_dnslookup')
			{
				$results = (isset($results['resolutions'])?$results['resolutions']:false);
				$hostname = $result['hostname'];
				$out[$hostname] = array(
					'ttl' => 600,
					'first_seen' => null,
					'last_seen' => (isset($result['last_resolved'])?$result['last_resolved']:null),
				);
			}
			elseif($lookup_type == 'hostname_dnslookup')
			{
				$results = (isset($results['resolutions'])?$results['resolutions']:false);
				$ip_address = $result['ip_address'];
				$out[$ip_address] = array(
					'ttl' => 600,
					'first_seen' => null,
					'last_seen' => (isset($result['last_resolved'])?$result['last_resolved']:null),
				);
			}
		}
		
		$this->resetLimits($Model);
		return $out;
	}
	
	private function changeLimits(Model $Model, $new_limits = array())
	{
		if($new_limits)
		{
			$this->old_limits = $this->settings['limits'];
			$this->settings['limits'] = $new_limits;
		}
	}
	
	private function resetLimits(Model $Model)
	{
		if($this->old_limits)
		{
			$this->settings['limits'] = $this->old_limits;
			$this->old_limits = array();
		}
	}
	
	public function VT_getRemote(Model $Model, $url = null, $query = array(), $method = 'get', $headers = array(), $curl_options = array())
	{
		$data = false;
		$cacheKey = md5(serialize(array('url' => $url, 'query' => $query, 'method' => $method)));
		
		if(Cache::read('debug') < 2)
		{
			$data = Cache::read($cacheKey, 'virustotal');
			
			// if we need to save the raw copy of the results, do so now.
			if($this->settings['save_raw'] and $model_id = $this->VT_getModelID($Model))
			{
				$filename = $this->VT_getRawFilename($Model, $model_id);
				$paths = $this->VT_rawPaths($Model, $model_id, true);
				if($paths['sys'] and is_readable($paths['sys']) and !file_exists($paths['sys']. $filename))
				{
					file_put_contents($paths['sys']. $filename, $data);
				}
			}
		}
		
		if ($data !== false)
		{
			$Model->shellOut(__('Loaded from cache with key: %s', $cacheKey), 'virustotal', 'info');
			$Model->Usage_updateCounts('virustotal', 'cached');
			$Model->Usage_updateCounts('cached', 'virustotal');
			return $data;
		}
		
		if($this->VT_isDisabled($Model))
		{
			$Model->modelError = __('We hit their limit');
			$Model->shellOut($Model->modelError, 'virustotal', 'notice');
			return $out;
		}
		
		$curl_options_default = array(
			'followLocation' => true,
			'maxRedirs' => 5,
			'timeout' => 20,
			'connectTimeout' => 20,
			'cookieFile' => CACHE. 'dt_cookieFile',
			'cookieJar' => CACHE. 'dt_cookieJar',
			'header' => true,
		);
		$curl_options = array_merge($curl_options_default, $curl_options);
		
		if($url)
		{
			if(!$this->Curl)
			{
				// load the curl object
				$Model->shellOut(__('Loading cUrl.'), 'virustotal', 'info');
				App::import('Vendor', 'Utilities.Curl');
				$this->Curl = new Curl();
			}
			
			$this->Curl->referer = $url;
			
			foreach($curl_options as $k => $v)
			{
				$this->Curl->{$k} = $v;
			}
			
			$query_url = '';
			if(is_array($query) and !empty($query))
			{
				$query_url = array();
				foreach($query as $k => $v)
				{
					$query_url[] = $k. '='. $v;
				}
				$query_url = '?'. implode('&', $query_url);
			}
			
			if(is_array($headers) and !empty($headers))
			{
				$this->Curl->httpHeader = $headers;
			}
			
			if($method == 'post')
			{
				$this->Curl->post = true;
				$this->Curl->postFieldsArray = $query;
				$Model->shellOut(__('URL: %s - POST Query: %s', $url, $query_url), 'virustotal', 'info');
				$this->Curl->url = $url;
			}
			else
			{
				$url .= $query_url;
				$Model->shellOut(__('URL: %s', $url), 'virustotal', 'info');
				$this->Curl->url = $url;
			}
			
			// going for a live connection
			$this->getting_live = true;
			
			if(!$this->VT_checkLimits($Model))
			{
				return false;
			}
			
			$data = $this->Curl->execute();
			
			$Model->Usage_updateCounts('virustotal', 'remote');
			$Model->Usage_updateCounts('remote', 'virustotal');
			
			if($this->Curl->response_headers)
			{
				$Model->curlHeaders = $this->curlHeaders = $this->Curl->response_headers;
				
				// check if we got a 204, and disable ourselves
				if(isset($this->curlHeaders['status']) and $this->curlHeaders['status'] == '204')
				{
					$Model->Usage_updateCounts('virustotal', 'limit_hit');
					$count_type = $this->VT_guessLimitHitType($Model);
					$this->disableParser($Model, $count_type);
					$Model->shellOut(__('We have hit VirusTotal\'s internal limit, disabling for the %s. Current possible counts: %s', $count_type, $this->VT_getCountsNice($Model)), 'virustotal', 'notice');
					return $data;
				}
			}
				
			if($this->Curl->error)
			{
				$Model->Usage_updateCounts('virustotal', 'remote_error');
				$Model->Usage_updateCounts('remote_error', 'virustotal');
				$Model->curlError = $this->curlError = $this->Curl->error;
				$Model->curlErrno = $this->curlErrno = $this->Curl->errno;
				$logtype = 'error';
				$Model->shellOut(__('Curl Error: (%s) %s -- Url: %s', $this->curlErrno, $this->curlError, $url), 'virustotal', $logtype);
			}
			else
			{
				$Model->Usage_updateCounts('virustotal', 'remote_success');
				$Model->Usage_updateCounts('remote_success', 'virustotal');
				$this->VT_updateCounts($Model, 1);
				
				// cache it
				Cache::write($cacheKey, $data, 'virustotal');
				
				// if we need to save the raw copy of the results, do so now.
				if($this->settings['save_raw'] and $model_id = $this->VT_getModelID($Model))
				{
					$filename = $this->VT_getRawFilename($Model, $model_id);
					$paths = $this->VT_rawPaths($Model, $model_id, true, $filename);
					if($paths['sys'])
					{
						file_put_contents($paths['sys'], $data);
					}
				}
			}
		}
		return $data;
	}
	
	public function VT_setModelID(Model $Model, $id = false, $raw_prefix = false)
	{
		if($id) $this->modelID = $id;
		if($raw_prefix) $this->rawPrefix = $raw_prefix;
	}
	
	public function VT_getModelID(Model $Model)
	{
		if($this->modelID)
		{
			return $this->modelID;
		}
		return false;
	}
	
	public function VT_getRawFilename(Model $Model, $model_id = false)
	{
		if($this->rawPrefix)
		{
			return 'virustotal_'. $this->rawPrefix. '_'. $model_id. '.txt';
		}
		return false;
	}
	
	public function VT_getLookupSettings(Model $Model, $lookup_key = false)
	{
		if(!isset($this->settings['lookup_types']))
		{
			return false;
		}
		if(!$lookup_key)
		{
			return $this->settings['lookup_types'];
		}
		if(!isset($this->settings['lookup_types'][$lookup_key]))
		{
			return false;
		}
		return $this->settings['lookup_types'][$lookup_key];
	}
	
	public function VT_rawPaths(Model $Model, $id = false, $create = false, $filename = false)
	{
		$paths = array('web' => false, 'sys' => false);
		
		if(!$id) return false;
		
		$paths['web'] = DS. 'files'. DS. 'virustotal'. DS. strtolower(Inflector::pluralize($Model->name)). DS;
		$paths['sys'] = WWW_ROOT. ltrim($paths['web'], DS);
		
		foreach(str_split($id) as $num)
		{
			$paths['sys'] .= $num. DS;
			$paths['web'] .= $num. DS;
		}
		
		if($create)
		{
			umask(0);
			if(!is_dir($paths['sys'])) mkdir($paths['sys'], 0777, true);
		}
		
		if($filename)
		{
			$paths['sys'] .= $filename;
			$paths['web'] .= $filename;
		}
		
		return $paths;
	}
	
	public function VT_setCounts(Model $Model)
	{
		if(isset($this->session_counts[$Model->name]))
		{
			return;
		}
		
		foreach($this->disableLengths as $time_type => $now)
		{
			$this->session_counts[$Model->name][$time_type]['count'] = 0;
			$this->session_counts[$Model->name][$time_type]['timestamp'] = $now;
		}
		// track just this session
		$this->session_counts[$Model->name]['session']['count'] = 0;
	}
	
	public function VT_saveCounts(Model $Model, $limit_type = false)
	{
		if(!isset($this->session_counts[$Model->name]['session']['count'])) return;
		if(!$this->session_counts[$Model->name]['session']['count']) return;
		
		$stored_counts = Cache::read('limit_count', 'virustotal_long');
		
		// only store once per session
		$this_pid = getmypid();
		if(isset($stored_counts['pid']) and $stored_counts['pid'] == $this_pid) return;
		$this->session_counts['pid'] = $this_pid;
		
		// tracked time types
		$time_types = array_keys($this->disableLengths);
		
		foreach($this->session_counts[$Model->name] as $time_type => $data)
		{
			// only track the ones we have timestamped as an index (e.g. don't refresh the session count)
			if(!in_array($time_type, $time_types)) continue;
			// no need to update something that didn't previously exist
			if(!isset($stored_counts[$Model->name][$time_type])) continue;
			
			$new_count = $data['count'];
			$new_timestamp = $data['timestamp'];
			
			$old_count = $stored_counts[$Model->name][$time_type]['count'];
			$old_timestamp = $stored_counts[$Model->name][$time_type]['timestamp'];
			
			// still in the same timeframe for this time type
			if($new_timestamp == $old_timestamp)
			{
				$new_count = ($old_count + $new_count);
				$this->session_counts[$Model->name][$time_type]['count'] = $new_count;
			}
		}
		
		$this->Model->shellOut(__('Final Usage Count: %s with %s', $this->VT_getCountsNice($Model), $Model->name), 'virustotal', 'info');
		Cache::write('limit_count', $this->session_counts, 'virustotal_long');
	}
	
	public function VT_checkLimits(Model $Model)
	{
		if(!$Model->name) return true;
		foreach($this->settings['limits'] as $type => $limit)
		{
			if(isset($this->session_counts[$Model->name][$type]['count']) and $this->session_counts[$Model->name][$type]['count'] >= $limit)
			{
				$this->disableParser($Model, $limit);
				return false;
			}
		}
		
		return true;
	}
	
	public function VT_updateCounts(Model $Model, $count = 1)
	{
		$this->VT_setCounts($Model); // make sure they are loaded from the cache
		if($count)
		{
			foreach($this->disableLengths as $time_type => $now)
			{
				$current = $this->session_counts[$Model->name][$time_type]['count'];
				
				$this->session_counts[$Model->name][$time_type]['count'] = ($current + $count);
			}
			// track the session as well
			$current = $this->session_counts[$Model->name]['session']['count'];
			$this->session_counts[$Model->name]['session']['count'] = ($current + $count);
		}
	}
	
	public function VT_getLimit(Model $Model, $time_period = 'minute')
	{
		if(isset($this->settings['limits'][$time_period]))
			return $this->settings['limits'][$time_period];
		
		return false;
	}
	
	public function VT_getCounts(Model $Model, $type = false)
	{
		$this->VT_setCounts($Model); // make sure they are loaded from the cache
		if($type)
		{
			if(isset($this->session_counts[$Model->name][$type]['count'])) return $this->session_counts[$Model->name][$type]['count'];
			else 0;
		}
		
		return $this->session_counts[$Model->name];
	}
	
	public function VT_getCountsNice(Model $Model)
	{
		$session_counts = $this->VT_getCounts($Model);
		$count_text = array();
		foreach($session_counts as $type => $datum)
		{
			$count_text[] = __('%s: %s', $type, $datum['count']);
		}
		return implode(' - ', $count_text);
	}
	
	public function VT_guessLimitHitType($Model, $upcount = 1)
	{
		$current_counts = $this->VT_getCounts($Model);
		
		foreach($current_counts as $current_type => $current_data)
		{
			if(!isset($this->settings['limits'][$current_type])) continue;
			
			if($current_data['count'] >= $this->settings['limits'][$current_type]) return $current_type;
		}
		return 'session';
	}
	
	public function VT_isDisabled(Model $Model, $type = false)
	{
	/*
	 * ability to check if virustotal is disabled before even looking for hosts
	 */
		
		foreach($this->settings['limits'] as $type => $limit)
		{
			if($this->isDisabledParser($Model, $type, true))
			{
				return $type;
			}
		}
		return false;
	}
	
	private function disableParser(Model $Model, $disableLength = false)
	{
		if(!$this->parser_disabled)
		{
			$this->parser_disabled = true;
			$Model->shellOut(__('The Source: %s is disabled for this session.', __('VirusTotal')), 'virustotal', 'info');
		}
		
		// cache the disabled state for a set period of time
		if($disableLength and isset($this->disableLengths[$disableLength]))
		{
			$timestamp = $this->disableLengths[$disableLength];
			
			// if it doesn't previously exist, then send a notice
			$old_timestamp = Cache::read('disabled', 'virustotal_long');
			
			Cache::write('disabled', $timestamp, 'virustotal_long');
			if(!$old_timestamp)
			{
				$Model->shellOut(__('The Source: %s is disabled for the rest of this %s.', __('virustotal'), $disableLength), 'virustotal', 'notice');
			}
		}
	}
	
	protected function isDisabledParser(Model $Model, $disableLength = false, $bypass_session_disable = false)
	{
		if($this->parser_disabled)
		{
			return true;
		}
		
		// check the config to see if it's disabled there
		if(isset($this->settings['disabled']) and $this->settings['disabled'] and !$bypass_session_disable)
		{
			// disable it for this session
			$this->disableParser($Model);
			return true;
		}
		
		// cache the disabled state
		if($disableLength and isset($this->disableLengths[$disableLength]))
		{
			$timestamp = $this->disableLengths[$disableLength];
			$old_timestamp = Cache::read('disabled', 'virustotal_long');
			if($old_timestamp and $timestamp == $old_timestamp)
			{
				// refresh the cache
				Cache::write('disabled', $timestamp, 'virustotal_long');
				
				// set the session disable cache
				$this->parser_disabled= true;
			
				return true;
			}
		}
		
		return false;
	}
	
}