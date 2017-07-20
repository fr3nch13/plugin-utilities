<?php

class NslookupBehavior extends ModelBehavior 
{
	public $parser = null; // the one we're using
	public $content = null; // content returned from the remote web server
	public $url = null;
	public $Curl = null;
	public $curlError = null;
	public $curlErrno = null;
	public $curlHeaders = null;
	public $curlRequestHeaders = null;
	public $curlCookieFile = null;
	public $curlCookieJar = null;
	public $lastParser = '';
	
	public $defaultTTL = 600; // default ttl when none is reported
	
	// list of local ip addresses.
	// can be partial like '192.168.'
	// used to determine if we should do a local lookup, or an external one
	public $local_ips = array();
	
	// list of local hostnames.
	// can be partial like '.example.com'
	// used to determine if we should do a local lookup, or an external one
	public $local_hosts = array();
	
	// list of exempt ip addresses.
	// can be partial like '192.168.'
	// used to determine if we should skip this ip address
	public $exempt_ips = array();
	
	// list of exempt hostnames.
	// can be partial like '.example.com'
	// used to determine if we should skip this host
	public $exempt_hosts = array();
	
	
	private $parser_settings = false;
	
	private $account_settings = false;
	
	private $exemption_settings = false;
	
	private $parsers_disabled = array();
	private $disableLengths = array();
	
	// tracks if the getRemote function is returning cache (false), or actually reaching out (true)
	private $getting_live = false;
	
	
	public function setup(Model $Model, $settings = array())
	{
		$this->parser_settings = Configure::read('Nslookup.parser_settings');
		
		$Model->curlError = $this->curlError;
		$Model->curlErrno = $this->curlErrno;
		$Model->curlHeaders = $this->curlHeaders;
		$Model->curlRequestHeaders = $this->curlRequestHeaders;
		$Model->getting_live = $this->getting_live;
		
		$this->disableLengths = array('hour' => date('YmdH'), 'day' => date('Ymd'), 'week' => date('YW'), 'month' => date('Ym'));
		
		if(!$Model->Behaviors->loaded('Usage.Usage'))
		{
			$Model->Behaviors->load('Usage.Usage');
		}
		
		if(!$Model->Behaviors->enabled('Usage.Usage'))
		{
			$Model->Behaviors->enable('Usage.Usage');
		}
	}
	
/***** Parsers. One for each source ******/
	
//
	private function parser_domaintools_dns_history(Model $Model, $query = false, $type = false, $automatic = false)
	{
		$out = array();
		if($this->isDisabledParser($Model, 'domaintools_dns_history', 'month'))
		{
			return $out;
		}
		
		$Model->shellOut(__('Looking up: %s', $query), 'nslookup', 'info');
		if(!$query or !$type) return $out;
		
		if($type != 'hostname')
		{
			return $out;
		}
		
		$parser_settings = $this->parser_settings['domaintools_dns_history'];
		
		$uri = $parser_settings['url_base']. $query. $parser_settings['uri_postfix'];
		
		$content = $this->NS_getRemote($Model, $uri, array(
			'api_username' => $parser_settings['api_username'],
			'api_key' => $parser_settings['api_key'],
		));
		
		$content = json_decode($content);
		$content = $this->object_to_array($Model, $content);
		
		if(isset($content['error']))
		{
			$loglevel = 'error';
			$notify = true;
			$error_code = $content['error']['code'];
			$error_codes = array(
				'503' => array('disable' => 'month', 'loglevel' => 'notice'), // exceeded monthly limit
				'404' => array('disable' => false, 'loglevel' => 'notice', 'notify' => false), // No Hosting History data found
			);
			if(isset($error_codes[$error_code]))
			{
				$loglevel = (isset($error_codes[$error_code]['loglevel'])?$error_codes[$error_code]['loglevel']:$loglevel);
				$notify = (isset($error_codes[$error_code]['notify'])?$error_codes[$error_code]['notify']:$notify);
				if(isset($error_codes[$error_code]['disable']) and $error_codes[$error_code]['disable'])
				{
					if(is_bool($error_codes[$error_code]['disable']))
					{
						$error_codes[$error_code]['disable'] = 'month';
					}
					$this->disableParser($Model, 'domaintools_dns_history', $error_codes[$error_code]['disable']);
				}
			}
			$Model->shellOut(__('Error Occurred with url: %s - Error: (%s) %s', $uri, $content['error']['code'], $content['error']['message']), 'nslookup', $loglevel, $notify);
			return $out;
		}
		
		if(!isset($content['response']['ip_history']))
		{
			$Model->shellOut(__('No responses (1)'), 'nslookup', 'warning');
			return $out;
		}
		
		if(!$content['response']['ip_history'])
		{
			$Model->shellOut(__('No responses (2)'), 'nslookup', 'warning');
			return $out;
		}
		
		if(!is_array($content['response']['ip_history']))
		{
			$Model->shellOut(__('No responses (3)'), 'nslookup', 'warning');
			return $out;
		}
		
		$defaults = array('ttl' => $this->defaultTTL);
		
		$Model->Usage_updateCounts('all', 'nslookup_domaintools_dns_history');
		$Model->Usage_updateCounts('domaintools_dns_history', 'nslookup');
		
		if($this->getting_live)
		{
			$Model->Usage_updateCounts('remote', 'nslookup_domaintools_dns_history');
		}
		else
		{
			$Model->Usage_updateCounts('cached', 'nslookup_domaintools_dns_history');
		}
			
		if($type == 'hostname')
		{
			$Model->Usage_updateCounts('hostname', 'nslookup_domaintools_dns_history');
		}
		elseif($type == 'ip')
		{
			$Model->Usage_updateCounts('ipaddress', 'nslookup_domaintools_dns_history');
		}
		
		foreach($content['response']['ip_history'] as $ip_history)
		{
			$timestamp = strtotime($ip_history['actiondate']);
			
			if(trim($ip_history['post_ip']))
			{
				$ip = $ip_history['post_ip'];
				
				$record = array_merge($defaults, array('first_seen' => $timestamp));
				
				// update the dates
				if(isset($out[$ip]))
				{
					if(!isset($out[$ip]['first_seen']))
					{
						$out[$ip]['first_seen'] = $timestamp;
					}
					
					if($out[$ip]['first_seen'] > $timestamp)
					{
						$out[$ip]['first_seen'] = $timestamp;
					}
				}
				else
				{
					$out[$ip] = $record;
				}
			}
			
			if(trim($ip_history['pre_ip']))
			{
				$ip = $ip_history['pre_ip'];
				
				$record = array_merge($defaults, array('last_seen' => $timestamp));
				
				// update the dates
				if(isset($out[$ip]))
				{
					if(!isset($out[$ip]['last_seen']))
					{
						$out[$ip]['last_seen'] = $timestamp;
					}
					
					if($out[$ip]['last_seen'] < $timestamp)
					{
						$out[$ip]['last_seen'] = $timestamp;
					}
				}
				else
				{
					$out[$ip] = $record;
				}
			}
		}
		
		foreach($out as $ip => $details)
		{
			if(isset($out[$ip]['first_seen']))
			{
				$out[$ip]['first_seen'] = date('Y-m-d H:i:s', $out[$ip]['first_seen']);
			}
			else
			{
				$out[$ip]['first_seen'] = date('Y-m-d H:i:s');
			}
			if(isset($out[$ip]['last_seen']))
			{
				$out[$ip]['last_seen'] = date('Y-m-d H:i:s', $out[$ip]['last_seen']);
			}
			else
			{
				$out[$ip]['last_seen'] = date('Y-m-d H:i:s');
			}
		}
		return $out;
	}
	
	private function stat_domaintools_dns(Model $Model)
	{
		$out = array();
		
		$parser_settings = $this->parser_settings['domaintools_dns'];
		
		$cookieJar = CACHE. 'cookieJar-'. getmypid();
		
		$Model->Usage_updateCounts('domaintools_dns_called', 'source_stat');
		$Model->Usage_updateCounts('domaintools_dns_login', 'source_stat');
		
		// call the login page as a get to get cookies
		$login_content = $this->NS_getRemote(
			$Model, 
			$parser_settings['account_url_base']. $parser_settings['account_url_login'], 
			array(), 
			'get',
			array(),
			array(
				'checkCache' => false,
			)
		);
		
		if(!$this->curlCookieJar)
		{
			$Model->Usage_updateCounts('domaintools_dns_login_failed', 'source_stat');
			$Model->Usage_updateCounts('domaintools_dns_login_failed_1', 'source_stat');
			return $out;
		}
		
		$CSRFToken = false;
		$cookies = array();
		foreach($this->curlCookieJar as $i => $cookie)
		{
			if(!isset($cookie['name'])) continue;
			
			$cookies[] = $cookie['name'].'='.$cookie['value'];
			
			if($cookie['name'] == 'csrftoken')
			{
				$CSRFToken = $cookie['value'];
			}
		}

		$headers = array(
			'Accept' => 'application/json, text/javascript, */*; q=0.01',
			'Accept-Encoding' => 'gzip, deflate',
			'Accept-Language' => 'en-US,en;q=0.5',
			'Cache-Control' => 'no-cache',
			'Connection' => 'keep-alive',
			'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
			'Pragma' => 'no-cache',
			'Cookie' => implode('; ', $cookies),
			'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:35.0) Gecko/20100101 Firefox/35.0',
			'DNT' => 1,
			'X-CSRFToken' => $CSRFToken,
			'X-Requested-With' => 'XMLHttpRequest',
		);
		
		$login_content = $this->NS_getRemote(
			$Model, 
			$parser_settings['account_url_base'], 
			array('ajax' => 'mLogin', 'call' => 'ajax_authenticate', 'args[0]' => $parser_settings['username'], 'args[1]' => $parser_settings['password'], 'args[2]' => ''),
			'post',
			$headers,
			array(
				'encoding' => 'gzip',
				'checkCache' => false,
			)
		);
		
		if(!$login_content)
		{
			$Model->Usage_updateCounts('domaintools_dns_login_failed', 'source_stat');
			$Model->Usage_updateCounts('domaintools_dns_login_failed_2', 'source_stat');
			return $out;
		}
		
		$login_content = json_decode($login_content);
		$login_content = $this->object_to_array($Model, $login_content);
		if(!isset($login_content['type']))
		{
			return $out;
		}
		
		if($login_content['type'] != 'success')
		{
			$Model->Usage_updateCounts('domaintools_dns_login_failed', 'source_stat');
			$Model->Usage_updateCounts('domaintools_dns_login_failed_3', 'source_stat');
			return $out;
		}
		
		foreach($this->curlCookieJar as $i => $cookie)
		{
			if(!isset($cookie['name'])) continue;
			
			$cookies[] = $cookie['name'].'='.$cookie['value'];
			
			if($cookie['name'] == 'csrftoken')
			{
				$CSRFToken = $cookie['value'];
			}
		}
		foreach($this->curlCookieFile as $i => $cookie)
		{
			if(!isset($cookie['name'])) continue;
			
			$cookies[] = $cookie['name'].'='.$cookie['value'];
		}

		$headers = array(
			'Accept' => 'application/json, text/javascript, */*; q=0.01',
			'Accept-Encoding' => 'gzip, deflate',
			'Accept-Language' => 'en-US,en;q=0.5',
			'Cache-Control' => 'no-cache',
			'Connection' => 'keep-alive',
			'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
			'Pragma' => 'no-cache',
			'Cookie' => implode('; ', $cookies),
			'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:35.0) Gecko/20100101 Firefox/35.0',
			'DNT' => 1,
			'X-CSRFToken' => $CSRFToken,
			'X-Requested-With' => 'XMLHttpRequest',
		);
		
		$content = $this->NS_getRemote(
			$Model, 
			$parser_settings['account_url_base'], 
			array('ajax' => 'mAPI', 'call' => 'ajaxGetAPIData'),
			'post',
			$headers,
			array(
				'encoding' => 'gzip',
				'checkCache' => false,
			)
		);
		
		$content = json_decode($content);
		$content = $this->object_to_array($Model, $content);
		
		$out['total'] = 0;
		$products = array();
		foreach($content as $day => $items)
		{
			if(isset($items['usage']))
			{
				$out['total'] = ($out['total'] + $items['usage']);
				
				$out[$day] = $items['usage'];
			}
			
			if(isset($items['products']))
			{
				foreach($items['products'] as $product)
				{
					if(isset($product['product_name']) and isset($product['usage']))
					{
						$product_name = $product['product_name'];
						$product_usage = $product['usage'];
						if(!isset($out[$product_name])) $out[$product_name] = 0;
						$out[$product_name] = ($out[$product_name] + $product_usage);
						
						if(!isset($products[$product_name])) $products[$product_name] = 0;
						$products[$product_name] = ($products[$product_name] + $product_usage);
					}
				}
			}
		}
		
		arsort($out);
		
		$Model->Usage_updateCounts('domaintools_dns_total', 'source_stat', (int)$out['total']);
		
		foreach($products as $name => $count)
		{
			$Model->Usage_updateCounts('domaintools_dns_product_'. $name, 'source_stat', (int)$count);
		}
		
		return $out;
	}
	
	private function parser_domaintools_dns(Model $Model, $query = false, $type = false, $automatic = false)
	{
		$out = array();
		if($this->isDisabledParser($Model, 'domaintools_dns'))
		{
			return $out;
		}
		
		$Model->shellOut(__('Looking up: %s', $query), 'nslookup', 'info');
		if(!$query or !$type) return $out;
		
		$parser_settings = $this->parser_settings['domaintools_dns'];
		
		$uri = $parser_settings['url_base']. $query;
		if($type == 'hostname')
		{
			$uri .= $parser_settings['uri_postfix_hostname'];
		}
		elseif($type == 'ip')
		{
			$uri .= $parser_settings['uri_postfix_ipaddress'];
		}
		
		$content = $this->NS_getRemote($Model, $uri, array(
			'api_username' => $parser_settings['api_username'],
			'api_key' => $parser_settings['api_key'],
		));
	
		if(!$content)
		{
			$notify = true;
			// suppress messages with timeout issue
			if($this->curlErrno == 28)
				$notify = false;
			$Model->shellOut(__('No content returned. -- Host: %s', $query), 'nslookup', 'warning', $notify);
			return $out;
		}
		
		$content = json_decode($content);
		$content = $this->object_to_array($Model, $content);
		
		if(isset($content['error']))
		{
			$loglevel = 'error';
			$error_code = $content['error']['code'];
			$error_codes = array(
				// rate limit hit
				'503' => array('disable' => true, 'disable_length' => 'month', 'loglevel' => 'notice'),
				// api account doesn't have access
				// i have it set as info, so this doesn't get emailed
				'403' => array('disable' => true, 'disable_length' => 'day', 'loglevel' => 'info'),
				// not results
				'400' => array('disable' => false, 'loglevel' => 'info'),
			);
			if(isset($error_codes[$error_code]))
			{
				$loglevel = (isset($error_codes[$error_code]['loglevel'])?$error_codes[$error_code]['loglevel']:$loglevel);
				if(isset($error_codes[$error_code]['disable']) and $error_codes[$error_code]['disable'])
				{
					$disable_length = (isset($error_codes[$error_code]['disable_length'])?$error_codes[$error_code]['disable_length']:false);
					
					$this->disableParser($Model, 'domaintools_dns', $disable_length);
				}
			}
			$Model->shellOut(__('Error Occurred with url: %s - Error: (%s) %s', $uri, $content['error']['code'], $content['error']['message']), 'nslookup', $loglevel);
			return $out;
		}
		
		if(!isset($content['response']['ip_history']))
		{
			$Model->shellOut(__('No responses (1)'), 'nslookup', 'warning');
			return $out;
		}
		
		if(!$content['response']['ip_history'])
		{
			$Model->shellOut(__('No responses (2)'), 'nslookup', 'warning');
			return $out;
		}
		
		if(!is_array($content['response']['ip_history']))
		{
			$Model->shellOut(__('No responses (3)'), 'nslookup', 'warning');
			return $out;
		}
		
		$Model->Usage_updateCounts('all', 'nslookup_domaintools_dns');
		$Model->Usage_updateCounts('domaintools_dns', 'nslookup');
		
		if($this->getting_live)
		{
			$Model->Usage_updateCounts('remote', 'nslookup_domaintools_dns');
		}
		else
		{
			$Model->Usage_updateCounts('cached', 'nslookup_domaintools_dns');
		}
			
		if($type == 'hostname')
		{
			$Model->Usage_updateCounts('hostname', 'nslookup_domaintools_dns');
		}
		elseif($type == 'ip')
		{
			$Model->Usage_updateCounts('ipaddress', 'nslookup_domaintools_dns');
		}
		
		$defaults = array('ttl' => $this->defaultTTL);
		
//		$content = $this->NS_getRemote($Model, $url. $query. '/ANY', array(), 'post', $parser_settings['loginHeaders']);
		
	}
	
	private function stat_dnsdbapi(Model $Model)
	{
		$out = array();
		
		$Model->Usage_updateCounts('dnsdbapi_called', 'source_stat');
		$parser_settings = $this->parser_settings['dnsdbapi'];
		
		return $out;
	}
	
//
	private function parser_dnsdbapi(Model $Model, $query = false, $type = false, $automatic = false, $bypass_session_disable = false)
	{
		$out = array();
		if($this->isDisabledParser($Model, 'dnsdbapi', 'day', $bypass_session_disable))
		{
			return $out;
		}
		
		$Model->shellOut(__('Looking up: %s', $query), 'dnsdbapi', 'info');
		if(!$query or !$type) return $out;
		
		$urls = array();
		$json_key = false;
		$parser_settings = $this->parser_settings['dnsdbapi'];
		
		if($type == 'ip')
		{
			if(isset($parser_settings['path_rdata_ip'])) $urls['rdata'] = $parser_settings['host']. $parser_settings['path_rdata_ip'];
			if(isset($parser_settings['path_rrset_ip'])) $urls['rrset'] = $parser_settings['host']. $parser_settings['path_rrset_ip'];
		}
		elseif($type == 'hostname')
		{
			if(isset($parser_settings['path_rdata_host'])) $urls['rdata'] = $parser_settings['host']. $parser_settings['path_rdata_host'];
			if(isset($parser_settings['path_rrset_host'])) $urls['rrset'] = $parser_settings['host']. $parser_settings['path_rrset_host'];
		}
		
		if(!$urls)
		{
			$Model->shellOut(__('Unknown type.'), 'dnsdbapi', 'error');
			return $out;
		}
		
		$Model->dnsdbapi_none = false;
		
		// figure out which key we want to use
		if(isset($parser_settings['keys']) and !empty($parser_settings['keys']))
		{
			$parser_settings['key'] = $this->dnsdbapi_getKey($Model, $parser_settings['keys'], $automatic);
		}
		
		if(!$parser_settings['key'])
		{
			$Model->dnsdbapi_none = true;
			$this->disableParser($Model, 'dnsdbapi', 'day');
			$Model->shellOut(__('None of the keys are available.'), 'dnsdbapi', 'info');
			return $out;
		}
		
		$Model->shellOut(__('Using the key: %s', $parser_settings['key']), 'dnsdbapi', 'info');
		
		$defaults = array('ttl' => $this->defaultTTL, 'first_seen' => date('Y-m-d H:i:s'), 'last_seen' => date('Y-m-d H:i:s'));
		
		$all_content = false;
		foreach($urls as $k => $url)
		{
			$content = $this->NS_getRemote($Model, $url. $query. '/ANY', array(), 'post', array('X-API-Key' => $parser_settings['key'], 'Accept' => 'application/json'));
			
			$matches = array();
			$limit_error = false;
			if(preg_match('/Error\:(.*)/i', $content, $matches))
			{
				$Model->shellOut(__('Error Occurred with %s url: %s', $k, $url. $query), 'dnsdbapi', 'error');
				if(isset($matches[1]))
				{
					$Model->shellOut(__('Error: %s - key: %s', $matches[1], $parser_settings['key']), 'dnsdbapi', 'error');
					
					// rate limit exceeded
					if(preg_match('/Rate\s+limit\s+exceeded/i', $matches[1]))
					{
						// mark this key as hitting it's limit
						$this->dnsdbapi_updateKey($Model, $parser_settings['key'], array('limit_hit' => 1));
						
						// disable this source
						$this->disableParser($Model, 'dnsdbapi', 'day');
						
						// try this with another key?
						return $this->parser_dnsdbapi($Model, $query, $type);
					}
				}
				continue;
			}
			$all_content = $content. "\n";
		}
		
		if(!$all_content)
		{
			$Model->shellOut(__('No Content'), 'dnsdbapi', 'warning');
			return $out;
		}
		
		// update the key count
		if($this->getting_live)
		{
			$this->dnsdbapi_updateKey($Model, $parser_settings['key'], array('count' => 1));
		}
		
		$Model->Usage_updateCounts('all', 'nslookup_dnsdbapi');
		$Model->Usage_updateCounts('dnsdbapi', 'nslookup');
		
		if($this->getting_live)
		{
			$Model->Usage_updateCounts('remote', 'nslookup_dnsdbapi');
		}
		else
		{
			$Model->Usage_updateCounts('cached', 'nslookup_dnsdbapi');
		}
			
		if($type == 'hostname')
		{
			$Model->Usage_updateCounts('hostname', 'nslookup_dnsdbapi');
		}
		elseif($type == 'ip')
		{
			$Model->Usage_updateCounts('ipaddress', 'nslookup_dnsdbapi');
		}
		
		//// get the results
		$content = explode("\n", $all_content);
		
		foreach($content as $i => $line)
		{
			$line = trim($line);
			$line = json_decode($line);
			$line = $this->object_to_array($Model, $line);
			if(!isset($line['rrtype'])) continue;
			if(strtoupper($line['rrtype']) != 'A') continue;
			
			if($type == 'ip')
			{
				$results_key = $parser_settings['json_key_ip'];
			}
			elseif($type == 'hostname')
			{
				$results_key = $parser_settings['json_key_host'];
			}
			
			if(!isset($line[$results_key])) continue;
			if(!$line[$results_key]) continue;
			
			$results = $line[$results_key];
			if(is_string($results))
			{
				$results = array($results);
			}
			
			foreach($results as $result)
			{
				$result = trim($result, '.');
				$out[$result] = $defaults;
				if(isset($line['time_first'])) $out[$result]['first_seen'] = date('Y-m-d H:i:s', $line['time_first']);
				if(isset($line['time_first'])) $out[$result]['last_seen'] = date('Y-m-d H:i:s', $line['time_last']);
			}
		}
		
		// update the result arrays
		foreach($out as $result => $values)
		{
			if(isset($ttls[$result])) $out[$result]['ttl'] = $ttls[$result];
		}
		
		if(count($out))
		{
			$Model->Usage_updateCounts('results_found', 'nslookup_dnsdbapi');
			$Model->Usage_updateCounts('nslookup_dnsdbapi', 'results_found');
		}
		else
		{
			$Model->Usage_updateCounts('results_empty', 'nslookup_dnsdbapi');
			$Model->Usage_updateCounts('nslookup_dnsdbapi', 'results_empty');
		}
		
		$Model->shellOut(__('Found %s results for: %s', count($out), $query), 'dnsdbapi', 'info');
		return $out;
	}
	
//
	private function parser_networktools(Model $Model, $query = false, $type = false, $automatic = false)
	{	
		$out = array();
		if($this->isDisabledParser($Model, 'networktools'))
		{
			return $out;
		}
		
		$Model->shellOut(__('Looking up: %s', $query), 'nslookup', 'info');
		if(!$query or !$type) return $out;
		
		if($type == 'hostname') $prog = 'dnsrec';
		elseif($type == 'ip') $prog = 'lookup';
		
		$parser_settings = $this->parser_settings['networktools'];
		
		$query_options = $this->parser_settings['networktools']['host_query'];
		$query_options = array_merge($query_options, array('host' => $query, 'prog' => $prog));
		
		$content = $this->NS_getRemote(
			$Model, 
			$parser_settings['url_base'], 
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
		
		$Model->Usage_updateCounts('all', 'nslookup_networktools');
		$Model->Usage_updateCounts('networktools', 'nslookup');
		
		if($this->getting_live)
		{
			$Model->Usage_updateCounts('remote', 'nslookup_networktools');
		}
		else
		{
			$Model->Usage_updateCounts('cached', 'nslookup_networktools');
		}
		
		$content = str_replace('<tr>', "\n", $content);			
		$content = str_replace('</td>', ' ', $content);
		$content = str_replace('<br/>', "\n", $content);	
		$content = strip_tags($content);
		
		$lines = explode("\n", $content);
		
		foreach($lines as $line)
		{
			$line = trim($line);
			if(!$line) continue;
			$matches = array();
		
			// get ip addresses for a hostname
			if($type == 'hostname')
			{
				// google.com  A 74.125.227.99 300s
				if(preg_match('/A\s+([\d\.]+)\s+([\d]+)s/', $line, $matches))
				{
					// bypass other A records
					if(!preg_match('/^'. preg_quote($query). '/i', $line)) continue;
					if(!isset($matches[1]) or !$matches[1]) continue;
					if(!isset($matches[2]) or !$matches[2]) continue;
					
					$out[$matches[1]] = $matches[2];
				}
				$Model->Usage_updateCounts('hostname', 'nslookup_networktools');
			}
			elseif($type == 'ip')
			{
				// google.com  A 74.125.227.99 300s
				// Host name:
				if(preg_match('/^(Host name|canonical name|Alias):\s+([\w\-\.]+)/', $line, $matches))
				{
					if(!isset($matches[2]) or !$matches[2]) continue;
					// there is no reported ttl, so we'll use 300
					$out[$matches[2]] = $this->defaultTTL;
				}
				$Model->Usage_updateCounts('ipaddress', 'nslookup_networktools');
			}
		}
		
		if(count($out))
		{
			$Model->Usage_updateCounts('results_found', 'nslookup_networktools');
			$Model->Usage_updateCounts('nslookup_networktools', 'results_found');
		}
		else
		{
			$Model->Usage_updateCounts('results_empty', 'nslookup_networktools');
			$Model->Usage_updateCounts('nslookup_networktools', 'results_empty');
		}
		
		$Model->shellOut(__('Found %s results for: %s', count($out), $query), 'nslookup', 'info');
		return $out;
	}
	
//
	private function parser_zoneedit(Model $Model, $query = false, $type = false, $automatic = false)
	{
		$out = array();
		if($this->isDisabledParser($Model, 'zoneedit'))
		{
			return $out;
		}
		
		$Model->shellOut(__('Looking up: %s', $query), 'nslookup', 'info');
		if(!$query or !$type) return $out;

		$matches = array();
		
		$defaults = array('ttl' => $this->defaultTTL, 'first_seen' => date('Y-m-d H:i:s'), 'last_seen' => date('Y-m-d H:i:s'));
		
		$parser_settings = $this->parser_settings['zoneedit'];
		
		// get ip addresses for a hostname
		if($type == 'hostname')
		{
			$query_options = $this->parser_settings['zoneedit']['hostname_query'];
			$query_options = array_merge($query_options, array('host' => $query));
			
			$content = $this->NS_getRemote(
				$Model, 
				$parser_settings['url_base'], 
				$query_options,
				'post',
				false,
				$parser_settings['curl_options']
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
			
			$json = json_decode($content);
			
			if(!isset($json->map->result))  
			{
				// try to pull the message
				if(isset($json->message))
				{
					$Model->shellOut(__('Message: %s -- Host: %s', $json->message, $query), 'nslookup', 'warning', false);
				}
				return $out;
			}
			
			$results = $json->map->result;
			
			if(!$results = explode('<br>', $results))  
			{
				return $out;
			}
		
			$Model->Usage_updateCounts('all', 'nslookup_zoneedit');
			$Model->Usage_updateCounts('zoneedit', 'nslookup');
			
			if($this->getting_live)
			{
				$Model->Usage_updateCounts('remote', 'nslookup_zoneedit');
			}
			else
			{
				$Model->Usage_updateCounts('cached', 'nslookup_zoneedit');
			}
			
			$Model->Usage_updateCounts('hostname', 'nslookup_zoneedit');
			
			foreach($results as $result)
			{
				if(!trim($result)) continue;
				$items = preg_split('/\s+/', $result);
				
				if(!isset($items[1]) or !$items[1]) continue;
				if(!isset($items[4]) or !$items[4]) continue;
				
				$out[$items[4]] = $defaults;
				$out[$items[4]]['ttl'] = $items[1];
			}
		}
		elseif($type == 'ip')
		{
			$query_options = $this->parser_settings['zoneedit']['ipaddress_query'];
			$query_options = array_merge($query_options, array('ipaddress' => $query));
			
			$content = $this->NS_getRemote(
				$Model, 
				$parser_settings['url_base'], 
				$query_options,
				'post',
				false,
				$parser_settings['curl_options']
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
			
			$json = json_decode($content);
			
			if(!isset($json->map->result))  
			{
				// try to pull the message
				if(isset($json->message))
				{
					$Model->shellOut(__('Message: %s -- Host: %s', $json->message, $query), 'nslookup', 'warning', false);
				}
				return $out;
			}
			
			$results = $json->map->result;
			
			if($results and $results != $query)
			{
				$results = trim($results, '.');
				// there is no reported ttl, so we'll use 300
				$out[$results] = $defaults;
			}
		
			$Model->Usage_updateCounts('all', 'nslookup_zoneedit');
			$Model->Usage_updateCounts('zoneedit', 'nslookup');
			
			if($this->getting_live)
			{
				$Model->Usage_updateCounts('remote', 'nslookup_zoneedit');
			}
			else
			{
				$Model->Usage_updateCounts('cached', 'nslookup_zoneedit');
			}
			
			$Model->Usage_updateCounts('ipaddress', 'nslookup_zoneedit');
		}
		
		if(count($out))
		{
			$Model->Usage_updateCounts('results_found', 'nslookup_zoneedit');
			$Model->Usage_updateCounts('nslookup_zoneedit', 'results_found');
		}
		else
		{
			$Model->Usage_updateCounts('results_empty', 'nslookup_zoneedit');
			$Model->Usage_updateCounts('nslookup_zoneedit', 'results_empty');
		}
		
		$Model->shellOut(__('Found %s results for: %s', count($out), $query), 'nslookup', 'info');
		return $out;
	}
	
//
	private function parser_namespace(Model $Model, $query = false, $type = false, $automatic = false)
	{
		$out = array();
		if($this->isDisabledParser($Model, 'namespace'))
		{
			return $out;
		}
		
		$Model->shellOut(__('Looking up: %s', $query), 'nslookup', 'info');
		if(!$query or !$type) return $out;
		
		$parser_settings = $this->parser_settings['namespace'];
		
		$query_options = $this->parser_settings['namespace']['host_query'];
		$query_options = array_merge($query_options, array('nsinput' => $query));
		
		$content = $this->NS_getRemote(
			$Model, 
			$parser_settings['url_base'], 
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
		
		$Model->Usage_updateCounts('all', 'nslookup_namespace');
		$Model->Usage_updateCounts('namespace', 'nslookup');
		
		if($this->getting_live)
		{
			$Model->Usage_updateCounts('remote', 'nslookup_namespace');
		}
		else
		{
			$Model->Usage_updateCounts('cached', 'nslookup_namespace');
		}

		$matches = array();
		
		// get ip addresses for a hostname
		if($type == 'hostname')
		{
			$Model->Usage_updateCounts('hostname', 'nslookup_namespace');
			if(preg_match('/(Address|Addresses):\s+([\d\.\s,]+)\s+<\/PRE\>/', $content, $matches))
			{
				if(isset($matches[2]))
				{
					$items = preg_split('/[,\s]+/', $matches[2]);
					foreach($items as $item)
					{
						// there is no reported ttl, so we'll use 300
						if(trim($item)) $out[$item] = $this->defaultTTL;
					}
					ksort($out);
				}
			}
		}
		elseif($type == 'ip')
		{
			$Model->Usage_updateCounts('ipaddress', 'nslookup_namespace');
		}
		
		if(count($out))
		{
			$Model->Usage_updateCounts('results_found', 'nslookup_namespace');
			$Model->Usage_updateCounts('nslookup_namespace', 'results_found');
		}
		else
		{
			$Model->Usage_updateCounts('results_empty', 'nslookup_namespace');
			$Model->Usage_updateCounts('nslookup_namespace', 'results_empty');
		}
		
		$Model->shellOut(__('Found %s results for: %s', count($out), $query), 'nslookup', 'info');
		return $out;
	}
	
//
	private function parser_webmaster_toolkit(Model $Model, $query = false, $type = false, $automatic = false)
	{
		$out = array();
		if($this->isDisabledParser($Model, 'webmaster_toolkit'))
		{
			return $out;
		}
		
		$Model->shellOut(__('Looking up: %s', $query), 'nslookup', 'info');
		if(!$query or !$type) return $out;
		
		$parser_settings = $this->parser_settings['webmaster_toolkit'];
		
		$query_options = $this->parser_settings['webmaster_toolkit']['host_query'];
		$query_options = array_merge($query_options, array('address' => $query));
		
		$content = $this->NS_getRemote(
			$Model, 
			$parser_settings['url_base'], 
			$query_options,
			'post'
		);
		
		$content = str_replace('<b>', '', $content);
		$content = str_replace('</b>', '', $content);
		
		if(!$content)
		{
			$notify = true;
			// suppress messages with timeout issue
			if($this->curlErrno == 28)
				$notify = false;
			$Model->shellOut(__('No content returned. -- Host: %s', $query), 'nslookup', 'warning', $notify);
			return $out;
		}
		
		$Model->Usage_updateCounts('all', 'nslookup_webmaster_toolkit');
		$Model->Usage_updateCounts('webmaster_toolkit', 'nslookup');
		
		if($this->getting_live)
		{
			$Model->Usage_updateCounts('remote', 'nslookup_webmaster_toolkit');
		}
		else
		{
			$Model->Usage_updateCounts('cached', 'nslookup_webmaster_toolkit');
		}

		$answer_content = array();
		
		// get ip addresses for a hostname
		if($type == 'hostname')
		{
			$Model->Usage_updateCounts('hostname', 'nslookup_webmaster_toolkit');
			if(preg_match('/ANSWER\s+SECTION:\s+[\_\-\.a-zINA0-9\s]+/', $content, $answer_content))
			{
				$content = array_pop($answer_content);
				$matches = array();
				preg_match_all('/([\d\.]+)\s+IN\s+A\s+([\d\.]+)/', $content, $matches);
				if(isset($matches[2]))
				{
					foreach($matches[2] as $i => $match)
					{
						$out[$match] = $matches[1][$i];
					}
					ksort($out);
				}
			}
		}
		elseif($type == 'ip')
		{
			$Model->Usage_updateCounts('ipaddress', 'nslookup_webmaster_toolkit');
			if(preg_match('/ANSWER\s+SECTION:\s+[\_\-\.a-zINPTR0-9\s]+/', $content, $answer_content))
			{
				$content = array_pop($answer_content);
				$matches = array();
				preg_match_all('/([\d\.]+)\s+IN\s+PTR\s+([\w\-\.]+)\.?/', $content, $matches);
				if(isset($matches[2]))
				{
					foreach($matches[2] as $i => $match)
					{
						// trim off the ending period if it's there
						$match = trim($match, '.');
						$out[$match] = $matches[1][$i];
					}
					ksort($out);
				}
			}
		}
		
		if(count($out))
		{
			$Model->Usage_updateCounts('results_found', 'nslookup_webmaster_toolkit');
			$Model->Usage_updateCounts('nslookup_webmaster_toolkit', 'results_found');
		}
		else
		{
			$Model->Usage_updateCounts('results_empty', 'nslookup_webmaster_toolkit');
			$Model->Usage_updateCounts('nslookup_webmaster_toolkit', 'results_empty');
		}
		
		$Model->shellOut(__('Found %s results for: %s', count($out), $query), 'nslookup', 'info');
		return $out;
	}
	
//
	public function NS_getIps(Model $Model, $hostname, $dnsdbapi = false, $automatic = false)
	{
		return $this->NS_getContent($Model, $hostname, 'hostname', $dnsdbapi, $automatic);
	}
	
//
	public function NS_getHostnames(Model $Model, $ip, $dnsdbapi = false, $automatic = false)
	{
		return $this->NS_getContent($Model, $ip, 'ip', $dnsdbapi, $automatic);
	}
	
//
	public function NS_setLocalsIps(Model $Model, $list = array())
	{
		if(!$list) return false;
		$out = array();
		foreach($list as $i => $item)
		{
			$item = trim($item);
			if(!$item) continue;
			$out[] = '^'. preg_quote($item);
		}
		$this->local_ips = $out;
		return true;
	}
	
//
	public function NS_setLocalsHosts(Model $Model, $list = array())
	{
		if(!$list) return false;
		$out = array();
		foreach($list as $i => $item)
		{
			$item = trim($item);
			if(!$item) continue;
			$out[] = preg_quote($item). '$';
		}
		$this->local_hosts = $out;
		return true;
	}
	
//
	public function NS_localCheck(Model $Model, $host = null, $type = null)
	{
		if(!$host) return false;
		if($type != 'ip' and $type != 'hostname') return false;
		
		if($type == 'ip')
		{
			if($this->local_ips) foreach($this->local_ips as $ipcheck)
			{
			
				if(preg_match('/'.$ipcheck.'/i', $host)) { return true; }
			}
		}
		elseif($type == 'hostname')
		{
			if($this->local_hosts) foreach($this->local_hosts as $hostcheck)
			{
				if(preg_match('/'.$hostcheck.'/i', $host)) { return true; }
			}
		}
		return false;
	}
	
//
	public function NS_setExemptIps(Model $Model, $list = array())
	{
		if(!$list) return false;
		$out = array();
		foreach($list as $i => $item)
		{
			$item = trim($item);
			if(!$item) continue;
			$out[] = '^'. preg_quote($item);
		}
		$this->exempt_ips = $out;
		return true;
	}
	
//
	public function NS_setExemptHosts(Model $Model, $list = array())
	{
		if(!$list) return false;
		$out = array();
		foreach($list as $i => $item)
		{
			$item = trim($item);
			if(!$item) continue;
			$out[] = preg_quote($item). '$';
		}
		$this->exempt_hosts = $out;
		return true;
	}
	
//
	public function NS_exemptCheck(Model $Model, $host = null, $type = null)
	{
		if(!$host) return false;
		if($type != 'ip' and $type != 'hostname') return false;
		
		if($type == 'ip')
		{
			if($this->exempt_ips) foreach($this->exempt_ips as $ipcheck)
			{
				if(preg_match('/'.$ipcheck.'/', $host)) 
				{
					$Model->shellOut(__('The host: " %s " is exempted from lookups by the admin settings.', $host), 'nslookup', 'notice');
					return true; 
				}
			}
		}
		elseif($type == 'hostname')
		{
			if($this->exempt_hosts) foreach($this->exempt_hosts as $hostcheck)
			{
				if(preg_match('/'.$hostcheck.'/', $host))
				{
					$Model->shellOut(__('The host: " %s " is exempted from lookups by the admin settings.', $host), 'nslookup', 'notice');
					return true; 
				}
			}
		}
		return false;
	}
	
//
	public function NS_localLookup(Model $Model, $host = null, $type = null)
	{
		$out = array();
		$Model->shellOut(__('Looking up: %s', $host), 'nslookup', 'info');
		
		if(!$host) return $out;
		if($type != 'ip' and $type != 'hostname') return $out;
		
		$defaults = array('ttl' => $this->defaultTTL, 'first_seen' => date('Y-m-d H:i:s'), 'last_seen' => date('Y-m-d H:i:s'));
		
		if($type == 'ip' or $type == 'ipaddress')
		{
			$ptr= implode(".",array_reverse(explode(".",$host))).".in-addr.arpa";
			if($records = dns_get_record($ptr,DNS_PTR))
			{
				foreach($records as $record)
				{
					$out[$record['target']] = array_merge($defaults, array('ttl' =>  $record['ttl']));
				}
			}
		
			$Model->Usage_updateCounts('ipaddress', 'nslookup_local');
		}
		elseif($type == 'hostname')
		{
			if($records = dns_get_record($host,DNS_A))
			{
				foreach($records as $record)
				{
					$out[$record['ip']] = array_merge($defaults, array('ttl' =>  $record['ttl']));
				}
			}
		
			$Model->Usage_updateCounts('hostname', 'nslookup_local');
		}
		
		$Model->Usage_updateCounts('all', 'nslookup_local');
			
		return $out;
	}
	
	public function NS_getStats(Model $Model)
	{
	// used to get stats from the various sources mentioned above
		
		// weird situation where the debug setting is being blown out
		$debug = Configure::read('debug');
		
		$Model->modelError = false;
		$parser_keys = array_keys($this->parser_settings);
		
		$out = array();
		
		// go through the parsers
		shuffle($parser_keys); // randomize the array to choose a random one to go first
		foreach($parser_keys as $parser_key) 
		{
			$method = 'stat_'. $parser_key;
			if(!method_exists($this, $method)) continue;
			
			$Model->shellOut(__('Retrieving Stats for: %s', $parser_key), 'source_stats');
			$out[$parser_key] = $this->{$method}($Model);
			Configure::write('debug', $debug);
		}
		
		return $out;
	}
	
//
	public function NS_getContent(Model $Model, $host = null, $type = null, $dnsdbapi = false, $automatic = false)
	{
		$out = array();
		
		if(!$host) return $out;
		if($type != 'ip' and $type != 'hostname') return $out;
		
		$Model->modelError = false;
		
		// see if it's exempt, if so, ignore this host
		if($this->NS_exemptCheck($Model, $host, $type))
		{
			return $out;
		}
		
		// see if it's a local, if so, do a local lookup
		if($this->NS_localCheck($Model, $host, $type))
		{
			$out['local'] = $this->NS_localLookup($Model, $host, $type);
			return $out;
		}
		
		// weird situation where the debug setting is being blown out
		$debug = Configure::read('debug');
		
		if($dnsdbapi)
		{
			$out['dnsdbapi'] = $this->parser_dnsdbapi($Model, $host, $type, $automatic, true);
			Configure::write('debug', $debug);
			return $out;
		}
		
		$parser_keys = array_keys($this->parser_settings);
		
		// go through the parsers
		shuffle($parser_keys); // randomize the array to choose a random one to go first
		foreach($parser_keys as $parser_key) 
		{
			$method = 'parser_'. $parser_key;
			if($this->isDisabledParser($Model, $parser_key)) continue;
			if(!method_exists($this, $method)) continue;
			$out[$parser_key] = $this->{$method}($Model, $host, $type, $automatic);
			Configure::write('debug', $debug);
		}
		
		return $out;
	}
	
//
	public function NS_getQuery(Model $Model, $host = null, $type = null, $parser = array())
	{
		if($host and $type and !empty($parser))
		{
			$k = null;
			$out = false;
			if($type == 'ip' or $type == 'hostname')
			{
				if(isset($parser['query'][$type]) and count($parser['query'][$type]))
				{
					foreach($parser['query'][$type] as $k => $v)
					{
						$out[$k] = str_replace('***', $this->_strToHex($Model, $host), $v);
					}
				}
				return $out;
			}
		}
		return false;
	}
	
//
	public function NS_getUrl(Model $Model, $host = null, $type = null, $parser = array())
	{
		if($host and $type and !empty($parser))
		{
			$k = null;
			if($type == 'ip') $k = 'url_ip';
			elseif($type == 'hostname') $k = 'url_hostname';
			if($k)
			{
				return str_replace('***', $this->_strToHex($Model, $host), $parser[$k]);
			}
		}
		return false;
	}
	
//
	public function NS_getRegex(Model $Model, $type = null, $parser = array())
	{
		if($type != null and !empty($parser))
		{
			if($type == 'ip' and isset($parser['regex_ip'])) return $parser['regex_ip'];
			elseif($type == 'hostname' and isset($parser['regex_hostname'])) return $parser['regex_hostname'];
			elseif($type == 'overlimit' and isset($parser['regex_overlimit'])) return $parser['regex_overlimit'];
		}
		return false;
	}
	
	public function NS_GetHexBalance(Model $Model, $arg = null)
	{
		return 10000;
	}
//
	public function NS_getRemote(Model $Model, $url = null, $query = array(), $method = 'get', $headers = array(), $curl_options = array())
	{
		$this->getting_live = $Model->getting_live = false;
		$data = false;
		$cacheKey = md5(serialize(array('url' => $url, 'query' => $query, 'method' => $method)));
		
		$readCache = true;
		
		if(Cache::read('debug') > 1)
		{
			$readCache = false;
		}
		
		if(isset($curl_options['checkCache']))
		{
			if($curl_options['checkCache'] === false)
			{
				$readCache = false;
			}
			unset($curl_options['checkCache']);
		}
		
		if($readCache)
		{
			$data = Cache::read($cacheKey, 'nslookup');
		}
		
		if ($data !== false)
		{
			$Model->Usage_updateCounts('nslookup', 'cached');
			$Model->Usage_updateCounts('cached', 'nslookup');
			
			$Model->shellOut(__('Loaded from cache with key: %s', $cacheKey), 'nslookup', 'info');
			return $data;
		}
		
		$curl_options_default = array(
			'followLocation' => true,
			'maxRedirs' => 5,
			'timeout' => 20,
			'connectTimeout' => 20,
			'cookieFile' => CACHE. 'dt_cookieFile_'. getmypid(),
			'cookieJar' => CACHE. 'dt_cookieJar_'. getmypid(),
			'header' => true,
			'headerOut' => true,
		);
		$curl_options = array_merge($curl_options_default, $curl_options);
		
		if($url)
		{
			if(!$this->Curl)
			{
				// load the curl object
				$Model->shellOut(__('Loading cUrl.'), 'nslookup', 'info');
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
				$Model->shellOut(__('POST URL: %s - POST Query: %s', $url, $query_url), 'nslookup', 'info');
				$this->Curl->url = $url;
			}
			else
			{
				$this->Curl->post = false;
				$url .= $query_url;
				$Model->shellOut(__('GET URL: %s', $url), 'nslookup', 'info');
				$this->Curl->url = $url;
			}
			
			// going for a live connection
			$this->getting_live = $Model->getting_live = true;
			
			$Model->Usage_updateCounts('nslookup', 'remote');
			$Model->Usage_updateCounts('remote', 'nslookup');
			
			$data = $this->Curl->execute();
			
			$this->curlInfo = $this->Curl->getInfo();
			
			if($this->Curl->error)
			{
				$Model->Usage_updateCounts('nslookup', 'remote_error');
				$Model->Usage_updateCounts('remote_error', 'nslookup');
				
				$Model->curlError = $this->curlError = $this->Curl->error;
				$Model->curlErrno = $this->curlErrno = $this->Curl->errno;
				
				$logtype = 'error';
				if($this->curlErrno == 28)
				{
					if(stripos($url, 'zoneedit') !== false)
						$logtype = 'info';
					if(stripos($url, 'hexillion') !== false)
						$logtype = 'info';
					$Model->Usage_updateCounts('remote_error_timeout', 'nslookup');
				}
				
					
				$Model->shellOut(__('Curl Error: (%s) %s -- Url: %s', $this->curlErrno, $this->curlError, $url), 'nslookup', $logtype);
			}
			else
			{
				if($this->Curl->response_headers)
				{
					$Model->curlHeaders = $this->curlHeaders = $this->Curl->response_headers;
				}
				if($this->Curl->request_headers)
				{
					$Model->curlRequestHeaders = $this->curlRequestHeaders = $this->Curl->request_headers;
				}
				
				// cache it
				Cache::write($cacheKey, $data, 'nslookup');
				$Model->Usage_updateCounts('nslookup', 'remote_success');
				$Model->Usage_updateCounts('remote_success', 'nslookup');
			}
			
			$this->Curl->close();
			$this->Curl = false;
			if($curl_options['cookieJar'])
			{
				$this->curlCookieJar = $this->parseCookieJar($Model, $curl_options['cookieJar']);
				if(is_readable($curl_options['cookieJar'])) unlink($curl_options['cookieJar']);
			}
			if($curl_options['cookieFile'])
			{
				$this->curlCookieFile = $this->parseCookieJar($Model, $curl_options['cookieFile']);
				if(is_readable($curl_options['cookieFile'])) unlink($curl_options['cookieFile']);
			}
		}
		return $data;
	}
	
	protected function parseCookieJar(Model $Model, $cookieJarPath = false)
	{
		$out = array();
		if(!is_readable($cookieJarPath)) 
		{
			return $out;
		}
		
		if(!$lines = file($cookieJarPath))
		{
			return $out;
		}
		
		foreach($lines as $line) 
		{
			$line = trim($line);
			if(!$line) continue;
			if($line[0] == '#') continue;
			
			if(substr_count($line, "\t") !== 6) continue;
			
			$tokens = explode("\t", $line);
			$tokens = array_map('trim', $tokens);
			
			$out[] = array(
				'domain' => $tokens[0],
				'flag' => (strtoupper($tokens[1]) === 'TRUE'?true:false),
				'path' => $tokens[2],
				'secure' => (strtoupper($tokens[3]) === 'TRUE'?true:false),
				'expiration' => date('Y-m-d H:i:s', strtotime($tokens[4])),
				'name' => $tokens[5],
				'value' => $tokens[6],
			);
		}
		return $out;
	}
	
//
	public function NS_searchContent(Model $Model, $regex = null, $content = null)
	{
		if($regex and $content)
		{
			$matches = array();
			preg_match_all( $regex, $content, $matches);
			if(!empty($matches))
			{
				return array_pop($matches);
			}
		}
		return false;
	}
	
//
	public function NS_getLocalHosts(Model $Model, $host = null)
	{
		if($host)
		{
			foreach($this->localhosts as $localhost)
			{
				$hlen = '-'. strlen($localhost);
				if(substr($host, $hlen) == $localhost) return true;
			}
			return false;
		}
		return $this->localhosts;
	}
	
	
	///////// Support functions
	
	
	public function dnsdbapi_getKey(Model $Model, $keys = array(), $automatic = false)
	{
		$this_key = false;
		
		$timestamp = gmdate('Ymd');
		
		$key_defaults = array(
			'timestamp' => gmdate('Ymd'),
			'count' => 0,
			'limit_hit' => 0,
			'false_limit_hit' => 0,
		);
		
		$parser_settings = $this->parser_settings['dnsdbapi'];
	
		if(!$dnsdbapi_key_stats = Cache::read('dnsdbapi_key_stats', 'dnsdbapi_long'))
		{
			$dnsdbapi_key_stats = array();
		}
		
		foreach($keys as $key)
		{
			if(!isset($dnsdbapi_key_stats[$key]))
			{
				$dnsdbapi_key_stats[$key] = $key_defaults;
			}
			
			$dnsdbapi_key_stats[$key] = array_merge($key_defaults, $dnsdbapi_key_stats[$key]);
			
			// make sure it's within the same date
			// otherwise reset the stats
			if($dnsdbapi_key_stats[$key]['timestamp'] != $timestamp)
			{
				$dnsdbapi_key_stats[$key] = $key_defaults;
			}
			
			
			// the false limit set by us
			// only applies if the call is from a cron job
			if($automatic)
			{
				if(!$dnsdbapi_key_stats[$key]['limit_hit'] and !$this_key) 
				{
					$this_key = $key;
				}
				
				if(isset($parser_settings['false_limit']) and $parser_settings['false_limit'])
				{
					if($dnsdbapi_key_stats[$key]['count'] >= $parser_settings['false_limit'])
					{
						$dnsdbapi_key_stats[$key]['limit_hit'] = 1;
						$dnsdbapi_key_stats[$key]['false_limit_hit'] = 1;
						$this_key = false;
					}
				}
			}
			else
			{
				$this_key = $key;
			}
			
			$Model->shellOut(__('Found stats for - key: %s - %s - auto: %s', $key, json_encode($dnsdbapi_key_stats[$key]), ($automatic?'yes':'no')), 'dnsdbapi', 'info');
		}
		
		$Model->dnsdbapi_stats = $dnsdbapi_key_stats;
		
		Cache::write('dnsdbapi_key_stats', $dnsdbapi_key_stats, 'dnsdbapi_long');
		
		return $this_key;
	}
	
	public function dnsdbapi_updateKey(Model $Model, $key = false, $stats = array())
	{
		if(!$key) return false;
		
		$key_defaults = array(
			'timestamp' => gmdate('Ymd'),
			'count' => 0,
			'limit_hit' => 0,
			'false_limit_hit' => 0,
		);
		
		$stats = array_merge($key_defaults, $stats);
		
		if(!$dnsdbapi_key_stats = Cache::read('dnsdbapi_key_stats', 'dnsdbapi_long'))
		{
			$dnsdbapi_key_stats = array();
		}
		
		foreach($dnsdbapi_key_stats as $this_key => $this_stats)
		{
			if(!isset($dnsdbapi_key_stats[$this_key]))
			{
				$dnsdbapi_key_stats[$this_key] = $key_defaults;
			}
			
			if($key == $this_key)
			{
				$dnsdbapi_key_stats[$this_key] = $stats;
				if($stats['count']) 
				{
					$dnsdbapi_key_stats[$this_key]['count'] = $this_stats['count'] + $stats['count'];
				}
			}
		}
		
		$log_type = 'info';
		if($dnsdbapi_key_stats[$key]['limit_hit'])
		{
			$log_type = 'warning';
		}
			
		// document the current state
		$Model->shellOut(__('Current stats for key: %s - %s', $key, json_encode($dnsdbapi_key_stats[$key])), 'dnsdbapi', $log_type);
		
		Cache::write('dnsdbapi_key_stats', $dnsdbapi_key_stats, 'dnsdbapi_long');
	}
	
	public function dnsdbapi_isDisabled(Model $Model)
	{
	/*
	 * ability to check if dnsdbapi is disabled before even looking for hosts
	 */
	
		return $this->isDisabledParser($Model, 'dnsdbapi', 'day', true);
	}
	
	private function disableParser(Model $Model, $parser_key = false, $disableLength = false)
	{
			
		$Model->Usage_updateCounts('disabled', 'nslookup_'. $parser_key);
		$Model->Usage_updateCounts('nslookup_'. $parser_key, 'disabled');
			
		if(!isset($this->parsers_disabled[$parser_key]))
		{
			$this->parsers_disabled[$parser_key] = $parser_key;
			$Model->shellOut(__('The Source: %s is disabled for this session.', $parser_key), 'nslookup', 'info');
			
			$Model->Usage_updateCounts('disabled_session', 'nslookup_'. $parser_key);
			$Model->Usage_updateCounts('nslookup_'. $parser_key, 'disabled_session');
		}
		
		// cache the disabled state for a set period of time
		if($disableLength and isset($this->disableLengths[$disableLength]))
		{
			$timestamp = $this->disableLengths[$disableLength];
			
			// if it doesn't previously exist, then send a notice
			$old_timestamp = Cache::read('disabled_'. $parser_key, 'nslookup_long');
			
			Cache::write('disabled_'. $parser_key, $timestamp, 'nslookup_long');
			if(!$old_timestamp)
			{
				$Model->Usage_updateCounts('disabled_'. $disableLength, 'nslookup_'. $parser_key);
				$Model->Usage_updateCounts('nslookup_'. $parser_key, 'disabled_'. $disableLength);
				
				$Model->shellOut(__('The Source: %s is disabled for the rest of this %s.', $parser_key, $disableLength), 'nslookup', 'notice');
			}
		}
	}
	
	public function isDisabledParser(Model $Model, $parser_key = false, $disableLength = false, $bypass_session_disable = false)
	{
		if(isset($this->parsers_disabled[$parser_key]))
		{
			return true;
		}
		
		// cache the disabled state
		elseif($disableLength and isset($this->disableLengths[$disableLength]))
		{
			$timestamp = $this->disableLengths[$disableLength];
			$old_timestamp = Cache::read('disabled_'. $parser_key, 'nslookup_long');
			
			if($old_timestamp and $timestamp == $old_timestamp)
			{
				// refresh the cache
				Cache::write('disabled_'. $parser_key, $timestamp, 'nslookup_long');
				
				// set the session disable cache
				$this->parsers_disabled[$parser_key] = $parser_key;
			
				return true;
			}
		}
		
		// check the config to see if it's disabled there
		$parser_settings = (isset($this->parser_settings[$parser_key])? $this->parser_settings[$parser_key]: false);
		if($parser_settings and isset($parser_settings['disabled']) and $parser_settings['disabled'] and !$bypass_session_disable)
		{
			// disable it for this session
			$this->disableParser($Model, $parser_key);
			return true;
		}
		
		return false;
	}
	
//
	public function _strToHex(Model $Model, $string)
	{
		return $string;
		$hex='';
		for ($i=0; $i < strlen($string); $i++)
		{
			$hex .= '%'. dechex(ord($string[$i]));
		}
		return $hex;
	}
	
	protected function object_to_array(Model $Model, $obj) 
	{
		$arrObj = is_object($obj) ? get_object_vars($obj) : $obj;
		$arr = '';
		if($arrObj)
		{
			foreach ($arrObj as $key => $val) 
			{
				$val = (is_array($val) || is_object($val)) ? $this->object_to_array($Model, $val) : $val;
				$arr[$key] = $val;
			}
		}
		return $arr;
	}
	
	public function saveRaw($filepath = false, $content = false)
	{
		
	}
	
}