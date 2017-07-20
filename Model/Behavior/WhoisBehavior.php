<?php

class WhoisBehavior extends ModelBehavior 
{
	public $parser = null; // the one we're using
	public $content = null; // content returned from the remote web server
	public $url = null;
	public $Curl = null;
	public $curlError = null;
	public $curlErrno = null;
	public $curlHeaders = null;
	public $lastParser = '';
	
	private $parser_settings = false;
	private $account_settings = false;
	
	private $getting_live = false;
	
	public $defaults = array(
		'sha1' => false,
		'tld' => false, // used to track the tld from domain tools
		'domain' => false, 
		'recordDate' => false, // mainly used for historical info
		'createdDate' => false,
		'updatedDate' => false,
		'expiresDate' => false,
		'contactEmail' => false,
		'registrarName' => false,
		'registrarStatus' => false,
		'registrantName' => false,
		'registrantOrg' => false,
		'registrantAddress' => false,
		'registrantCity' => false,
		'registrantState' => false,
		'registrantPostalCode' => false,
		'registrantCountry' => false,
		'registrantEmail' => false,
		'registrantPhone' => false,
		'registrantFax' => false,
		'billingName' => false,
		'billingOrg' => false,
		'billingAddress' => false,
		'billingCity' => false,
		'billingState' => false,
		'billingPostalCode' => false,
		'billingCountry' => false,
		'billingEmail' => false,
		'billingPhone' => false,
		'billingFax' => false,
		'adminName' => false,
		'adminOrg' => false,
		'adminAddress' => false,
		'adminCity' => false,
		'adminState' => false,
		'adminPostalCode' => false,
		'adminCountry' => false,
		'adminEmail' => false,
		'adminPhone' => false,
		'adminFax' => false,
		'techName' => false,
		'techOrg' => false,
		'techAddress' => false,
		'techCity' => false,
		'techState' => false,
		'techPostalCode' => false,
		'techCountry' => false, 
		'techEmail' => false,
		'techPhone' => false,
		'techFax' => false,
		'zoneName' => false,
		'zoneOrg' => false,
		'zoneAddress' => false,
		'zoneCity' => false,
		'zoneState' => false,
		'zonePostalCode' => false,
		'zoneCountry' => false,
		'zoneEmail' => false,
		'zonePhone' => false,
		'zoneFax' => false,
		'nameServers' => array(),
	);
	
	public $record = array();
	
	public function setup(Model $Model, $settings = array())
	{
		$this->parser_settings = Configure::read('Whois.parser_settings');
		$this->account_settings = Configure::read('Whois.account_settings');	
		
		$Model->curlError = $this->curlError;
		$Model->curlErrno = $this->curlErrno;
		$Model->curlHeaders = $this->curlHeaders;
		$Model->getting_live = $this->getting_live;
		
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
	private function parser_domaintools_whois(Model $Model, $query = false, $automatic = false)
	{
		$out = array();
		if($this->isDisabledParser($Model, 'domaintools_whois', 'month'))
		{
			return $out;
		}
		
		$Model->shellOut(__('Looking up: %s', $query), 'whois', 'info');
		
		if(!$query)
		{
			$Model->shellOut(__('Unknown Host'), 'whois', 'error');
			return $out;
		}
		
		$parser_settings = $this->parser_settings['domaintools_whois'];
		
		$query = urlencode($query);
		
		$uri = $parser_settings['url_base']. $query. $parser_settings['uri_whois'];
		
		$content = $this->Whois_getRemote($Model, $uri, array(
			'api_username' => $parser_settings['api_username'],
			'api_key' => $parser_settings['api_key'],
		));
		
		if(!$content)
		{
			$Model->shellOut(__('No Content'), 'whois', 'info');
			return $out;
		}
		
		$raw_content = $content;
		
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
				// no results
				'400' => array('disable' => false, 'loglevel' => 'info'),
				// no results
				'404' => array('disable' => false, 'loglevel' => 'info'),
			);
			if(isset($error_codes[$error_code]))
			{
				$loglevel = (isset($error_codes[$error_code]['loglevel'])?$error_codes[$error_code]['loglevel']:$loglevel);
				if(isset($error_codes[$error_code]['disable']) and $error_codes[$error_code]['disable'])
				{
					$disable_length = (isset($error_codes[$error_code]['disable_length'])?$error_codes[$error_code]['disable_length']:false);
					
					$this->disableParser($Model, 'domaintools_whois', $disable_length);
				}
			}
			$Model->shellOut(__('Error Occurred with url: %s - Error: (%s) %s', $uri, $content['error']['code'], $content['error']['message']), 'whois', $loglevel);
			return $out;
		}
		
		// the default map, like the names in the database
		// reset the record array
		$this->record = $this->defaults;
		
		$results = Set::flatten($content);
		
		// format the raw text
		$raw = false;
		foreach($results as $key => $value)
		{
			if(preg_match('/record$/i', $key)) $raw .= "\n". $value;
		}
		
		// add the key/value pair from the raw test to the results array
		$raw = explode("\n", $raw);
		foreach($raw as $line)
		{
			$line = trim($line);
			if(!$line) continue;
			if(stripos($line, ':') === false) continue;
			$parts = explode(':', $line);
			if(count($parts) > 1)
			{
				$result_key = 'raw_'. trim(array_shift($parts));
				
				$results[$result_key] = trim(implode(':', $parts));
			}
		}
		
		// extract and map the results to our default fields
		foreach($results as $key => $value)
		{
			$key = trim($key);
			
			// tld
			if(preg_match('/Domain(\s+)?Name$/i', $key)) $this->storeRecordItem($value, 'tld');
			
			// registrar
			if(preg_match('/contactemail/i', $key)) $this->storeRecordItem($value, 'contactEmail');
			
			// dates
			if(preg_match('/(date$|registration)/i', $key))
			{
				if(preg_match('/creat/i', $key)) $this->storeRecordItem($value, 'createdDate');
				if(preg_match('/update/i', $key)) $this->storeRecordItem($value, 'updatedDate');
				if(preg_match('/expire/i', $key)) $this->storeRecordItem($value, 'expiresDate');
			}
			// dates
			if(preg_match('/(On|Date)$/i', $key))
			{
				if(preg_match('/creat/i', $key)) $this->storeRecordItem($value, 'createdDate');
				if(preg_match('/update/i', $key)) $this->storeRecordItem($value, 'updatedDate');
				if(preg_match('/expir/i', $key)) $this->storeRecordItem($value, 'expiresDate');
			}
			
			// name servers
			if(preg_match('/nameserver/i', $key)) $this->storeRecordItem($value, 'nameServers');
			
			// registrar
			if(preg_match('/(.*)?registrar$/i', $key)) $this->storeRecordItem($value, 'registrarName');
			if(preg_match('/(.*)?status$/i', $key)) $this->storeRecordItem($value, 'registrarStatus');
			
			// registrant
			if(preg_match('/registrant/i', $key))
			{
				$key_prefix = 'registrant';
				if(preg_match('/(.*)?Name$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Name');
				if(preg_match('/(.*)?(Organization|Org)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Org');
				if(preg_match('/(.*)?Street(.)?$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Address');
				if(preg_match('/(.*)?City$/i', $key)) $this->storeRecordItem($value, $key_prefix.'City');
				if(preg_match('/(.*)?(State|Province)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'State');
				if(preg_match('/Postal/i', $key)) $this->storeRecordItem($value, $key_prefix.'PostalCode');
				if(preg_match('/(.*)?Country$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Country');
				if(preg_match('/(.*)?Email$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Email');
				if(preg_match('/(.*)?Phone$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Phone');
				if(preg_match('/(.*)?Fax$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Fax');
			}
			// billing
			if(preg_match('/billing/i', $key))
			{
				$key_prefix = 'billing';
				if(preg_match('/(.*)?Name$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Name');
				if(preg_match('/(.*)?(Organization|Org)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Org');
				if(preg_match('/(.*)?Street(.)?$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Address');
				if(preg_match('/(.*)?City$/i', $key)) $this->storeRecordItem($value, $key_prefix.'City');
				if(preg_match('/(.*)?(State|Province)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'State');
				if(preg_match('/Postal/i', $key)) $this->storeRecordItem($value, $key_prefix.'PostalCode');
				if(preg_match('/(.*)?Country$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Country');
				if(preg_match('/(.*)?Email$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Email');
				if(preg_match('/(.*)?Phone$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Phone');
				if(preg_match('/(.*)?Fax$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Fax');
			}
			// admin
			if(preg_match('/admin/i', $key))
			{
				$key_prefix = 'admin';
				if(preg_match('/(.*)?Name$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Name');
				if(preg_match('/(.*)?(Organization|Org)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Org');
				if(preg_match('/(.*)?Street(.)?$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Address');
				if(preg_match('/(.*)?City$/i', $key)) $this->storeRecordItem($value, $key_prefix.'City');
				if(preg_match('/(.*)?(State|Province)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'State');
				if(preg_match('/Postal/i', $key)) $this->storeRecordItem($value, $key_prefix.'PostalCode');
				if(preg_match('/(.*)?Country$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Country');
				if(preg_match('/(.*)?Email$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Email');
				if(preg_match('/(.*)?Phone$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Phone');
				if(preg_match('/(.*)?Fax$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Fax');
			}
			// tech
			if(preg_match('/tech/i', $key))
			{
				$key_prefix = 'tech';
				if(preg_match('/(.*)?Name$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Name');
				if(preg_match('/(.*)?(Organization|Org)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Org');
				if(preg_match('/(.*)?Street(.)?$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Address');
				if(preg_match('/(.*)?City$/i', $key)) $this->storeRecordItem($value, $key_prefix.'City');
				if(preg_match('/(.*)?(State|Province)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'State');
				if(preg_match('/Postal/i', $key)) $this->storeRecordItem($value, $key_prefix.'PostalCode');
				if(preg_match('/(.*)?Country$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Country');
				if(preg_match('/(.*)?Email$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Email');
				if(preg_match('/(.*)?Phone$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Phone');
				if(preg_match('/(.*)?Fax$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Fax');
			}
		}
		
		// fix the compiled data
		if(is_array($this->record['registrantAddress'])) $this->record['registrantAddress'] = implode("\n", $this->record['registrantAddress']);
		if(is_array($this->record['billingAddress'])) $this->record['billingAddress'] = implode("\n", $this->record['billingAddress']);
		if(is_array($this->record['adminAddress'])) $this->record['adminAddress'] = implode("\n", $this->record['adminAddress']);
		if(is_array($this->record['techAddress'])) $this->record['techAddress'] = implode("\n", $this->record['techAddress']);
		
		// add a hash
		$this->record['sha1'] = sha1(serialize($this->record));
		
		// mimic the fact that this is 1 of many results so it's compatible with the other sources
		$out = array($this->record);
		
		$this->saveRaw($Model, ($this->record['tld']?$this->record['tld']:$query), $raw_content);
		
		$Model->shellOut(__('Results found and returned.'), 'whois', 'info');
		
		return $out;
	}
	
	private function parser_domaintools_whois_history(Model $Model, $query = false, $automatic = false)
	{
		$out = array();
		if($this->isDisabledParser($Model, 'domaintools_whois_history', 'month'))
		{
			return $out;
		}
		
		$Model->shellOut(__('Looking up: %s', $query), 'whois', 'info');
		
		if(!$query)
		{
			$Model->shellOut(__('Unknown Host'), 'whois', 'error');
			return $out;
		}
		
		$parser_settings = $this->parser_settings['domaintools_whois_history'];
		
		$uri = $parser_settings['url_base']. $query. $parser_settings['uri_whois'];
		
		$timestamp = gmdate("Y-m-d\TH:i:s\Z");
		$signature = hash_hmac('md5', $parser_settings['api_username'] . $timestamp . $uri, $parser_settings['api_key']);
		
		$content = $this->Whois_getRemote($Model, $uri, array(
			'api_username' => $parser_settings['api_username'],
			'api_key' => $parser_settings['api_key'],
//			'signature' => $signature,
//			'timestamp' => $timestamp,
		));
		
		if(!$content)
		{
			$Model->shellOut(__('No Content - 1'), 'whois', 'info');
			return $out;
		}
		
		$raw_content = $content;
		
		$content = json_decode($content);
		$content = $this->object_to_array($Model, $content);
		
		if(isset($content['error']))
		{
			$Model->shellOut(__('Error Occurred with url: %s - Error: (%s) %s', $uri, $content['error']['code'], $content['error']['message']), 'whois', 'error');
//			return $out;
		}
		
		if(!isset($content['response']))
		{
			$Model->shellOut(__('No Content - 2'), 'whois', 'info');
			return $out;
		}
		
		if(!isset($content['response']['record_count']))
		{
			$Model->shellOut(__('No Records - 1'), 'whois', 'info');
			return $out;
		}
		
		if(!isset($content['response']['history']))
		{
			$Model->shellOut(__('No Records - 2'), 'whois', 'info');
			return $out;
		}
		
		$Model->shellOut(__('%s Historical Records found', $content['response']['record_count']), 'whois', 'info');
		
		$content = $content['response']['history'];
		
		$out = array();
		
		$i=0;
		foreach($content as $record)
		{
			$results = Set::flatten($record);
		
			// the default map, like the names in the database
			// reset the record array
			$this->record = $this->defaults;
			
			// format the raw text
			$raw = false;
			foreach($results as $key => $value)
			{
				if(preg_match('/record$/i', $key)) $raw .= "\n". $value;
			}
		
			// add the key/value pair from the raw test to the results array
			$raw = explode("\n", $raw);
			foreach($raw as $line)
			{
				$line = trim($line);
				if(!$line) continue;
				if(stripos($line, ':') === false) continue;
				$parts = explode(':', $line);
				if(count($parts) > 1)
				{
					$result_key = 'raw_'. trim(array_shift($parts));
					$results[$result_key] = trim(implode(':', $parts));
				}
			}
		
			// extract and map the results to our default fields
			foreach($results as $key => $value)
			{
				$key = trim($key);
				
				// historical record date
				if(preg_match('/^date$/i', $key)) $this->storeRecordItem($value, 'recordDate');
				
				// registrar
				if(preg_match('/contactemail/i', $key)) $this->storeRecordItem($value, 'contactEmail');
				
				// dates
				if(preg_match('/(On|Date)$/i', $key))
				{
					if(preg_match('/creat/i', $key)) $this->storeRecordItem($value, 'createdDate');
					if(preg_match('/update/i', $key)) $this->storeRecordItem($value, 'updatedDate');
					if(preg_match('/expir/i', $key)) $this->storeRecordItem($value, 'expiresDate');
				}
				
				// name servers
				if(preg_match('/name(\s+)?server/i', $key)) $this->storeRecordItem($value, 'nameServers');
				
				// registrar
				if(preg_match('/(.*)?registrar$/i', $key)) $this->storeRecordItem($value, 'registrarName');
				if(preg_match('/(.*)?status$/i', $key)) $this->storeRecordItem($value, 'registrarStatus');
				
				// registrant
				if(preg_match('/registrant/i', $key))
				{
					$key_prefix = 'registrant';
					if(preg_match('/(.*)?Name$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Name');
					if(preg_match('/(.*)?(Organization|Org)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Org');
					if(preg_match('/(.*)?Street(.)?$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Address');
					if(preg_match('/(.*)?City$/i', $key)) $this->storeRecordItem($value, $key_prefix.'City');
					if(preg_match('/(.*)?(State|Province)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'State');
					if(preg_match('/Postal/i', $key)) $this->storeRecordItem($value, $key_prefix.'PostalCode');
					if(preg_match('/(.*)?Country$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Country');
					if(preg_match('/(.*)?Email$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Email');
					if(preg_match('/(.*)?Phone$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Phone');
					if(preg_match('/(.*)?Fax$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Fax');
				}
				// billing
				if(preg_match('/billing/i', $key))
				{
					$key_prefix = 'billing';
					if(preg_match('/(.*)?Name$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Name');
					if(preg_match('/(.*)?(Organization|Org)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Org');
					if(preg_match('/(.*)?Street(.)?$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Address');
					if(preg_match('/(.*)?City$/i', $key)) $this->storeRecordItem($value, $key_prefix.'City');
					if(preg_match('/(.*)?(State|Province)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'State');
					if(preg_match('/Postal/i', $key)) $this->storeRecordItem($value, $key_prefix.'PostalCode');
					if(preg_match('/(.*)?Country$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Country');
					if(preg_match('/(.*)?Email$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Email');
					if(preg_match('/(.*)?Phone$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Phone');
					if(preg_match('/(.*)?Fax$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Fax');
				}
				// admin
				if(preg_match('/admin/i', $key))
				{
					$key_prefix = 'admin';
					if(preg_match('/(.*)?Name$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Name');
					if(preg_match('/(.*)?(Organization|Org)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Org');
					if(preg_match('/(.*)?Street(.)?$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Address');
					if(preg_match('/(.*)?City$/i', $key)) $this->storeRecordItem($value, $key_prefix.'City');
					if(preg_match('/(.*)?(State|Province)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'State');
					if(preg_match('/Postal/i', $key)) $this->storeRecordItem($value, $key_prefix.'PostalCode');
					if(preg_match('/(.*)?Country$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Country');
					if(preg_match('/(.*)?Email$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Email');
					if(preg_match('/(.*)?Phone$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Phone');
					if(preg_match('/(.*)?Fax$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Fax');
				}
				// tech
				if(preg_match('/tech/i', $key))
				{
					$key_prefix = 'tech';
					if(preg_match('/(.*)?Name$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Name');
					if(preg_match('/(.*)?(Organization|Org)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Org');
					if(preg_match('/(.*)?Street(.)?$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Address');
					if(preg_match('/(.*)?City$/i', $key)) $this->storeRecordItem($value, $key_prefix.'City');
					if(preg_match('/(.*)?(State|Province)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'State');
					if(preg_match('/Postal/i', $key)) $this->storeRecordItem($value, $key_prefix.'PostalCode');
					if(preg_match('/(.*)?Country$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Country');
					if(preg_match('/(.*)?Email$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Email');
					if(preg_match('/(.*)?Phone$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Phone');
					if(preg_match('/(.*)?Fax$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Fax');
				}
			}
			
			// fix the compiled data
			if(is_array($this->record['registrantAddress'])) $this->record['registrantAddress'] = implode("\n", $this->record['registrantAddress']);
			if(is_array($this->record['billingAddress'])) $this->record['billingAddress'] = implode("\n", $this->record['billingAddress']);
			if(is_array($this->record['adminAddress'])) $this->record['adminAddress'] = implode("\n", $this->record['adminAddress']);
			if(is_array($this->record['techAddress'])) $this->record['techAddress'] = implode("\n", $this->record['techAddress']);
			
			// add a hash
			$this->record['sha1'] = sha1(serialize($this->record));
			
			// mimic the fact that this is 1 of many results so it's compatible with the other sources
			$out[$i] = $this->record;
			$i++;
		}
		
		$this->saveRaw($Model, ($this->record['tld']?$this->record['tld']:$query), $raw_content);
		
		$Model->shellOut(__('Results found and returned: %s.', count($out)), 'whois', 'info');
		
		return $out;
	}
	
	private function stat_whoisxmlapi(Model $Model)
	{
		$out = array();
		$account_settings = $this->account_settings['whoisxmlapi'];
		
		if(!$content = $this->Whois_getRemote($Model, $account_settings['url_base'], $account_settings['query'], 'post'))
		{
			$Model->modelError = __('Unable to get account balance from whoisxmlapi');
			$Model->shellOut($Model->modelError, 'source_stats', 'error');
			return $out;
		}
			
		if(!$xmlArray = Xml::toArray(Xml::build($content)))
		{
			return $out;
		}
		
		if(!isset($xmlArray['Account']))
		{
			return $out;
		}
		$out = $xmlArray['Account'];
		
		
		foreach($out as $k => $v)
		{
			$v = trim($v);
			if(!$v) unset($out[$k]);
		}
		
		$out['percent_available'] = 0;
		if(isset($out['balance']) and isset($out['reserve']))
		{
			$out['percent_available'] = ceil( (($out['balance'] / $out['reserve']) * 100) );
		}
		
		if($out['percent_available'] < 20)
		{
			$out['mark_important'] = true;
		}
		
		return $out;
	}
	
	private function parser_whoisxmlapi(Model $Model, $query = false, $automatic = false)
	{
		$out = array();
		if($this->isDisabledParser($Model, 'whoisxmlapi', 'month'))
		{
			return $out;
		}
		
		$Model->shellOut(__('Looking up: %s', $query), 'whois', 'info');
		
		if(!$query)
		{
			$Model->shellOut(__('Unknown Host'), 'whois', 'error');
			return $out;
		}
		
		$parser_settings = $this->parser_settings['whoisxmlapi'];
		
		$query = array(
			'domainName' => $query,
			'username' => $parser_settings['username'],
			'password' => $parser_settings['password'],
			'outputFormat' => 'JSON',
		);
		
		if(!$content = $this->Whois_getRemote($Model, $parser_settings['url_base'], $query, 'post'))
		{
			$Model->modelError = __('Unable to lookup the domain from our source.');
			$Model->shellOut($Model->modelError, 'whois', 'error');
			return $out;
		}
		
		$raw_content = $content;
		
		$results = json_decode($content);
		
		if(isset($results->ErrorMessage->msg) and $results->ErrorMessage->msg)
		{
			$Model->modelError = $results->ErrorMessage->msg;
			$Model->shellOut($Model->modelError, 'whois', 'error');
			return $out;
		}
		
		// the default map, like the names in the database
		// reset the record array
		$this->record = $this->defaults;
		
		$results = $this->object_to_array($Model, $results->WhoisRecord);
		$results = Set::flatten($results);
		
		// format the raw text
		$raw = false;
		foreach($results as $key => $value)
		{
			if(preg_match('/raw/i', $key)) $raw .= "\n". $value;
			if(preg_match('/strippedText/i', $key)) $raw .= "\n". $value;
		}
		
		// add the key/value pair from the raw test to the results array
		$raw = explode("\n", $raw);
		foreach($raw as $line)
		{
			$line = trim($line);
			if(!$line) continue;
			if(stripos($line, ':') === false) continue;
			$parts = explode(':', $line);
			if(count($parts) > 1)
			{
				$result_key = 'raw_'. trim(array_shift($parts));
				
				$results[$result_key] = trim(implode(':', $parts));
			}
		}
		
		// extract and map the results to our default fields
		foreach($results as $key => $value)
		{
			$key = trim($key);
			
			// tld
			if(preg_match('/Domain(\s+)?Name$/i', $key)) $this->storeRecordItem($value, 'tld');
			
			// registrar
			if(preg_match('/contactemail/i', $key)) $this->storeRecordItem($value, 'contactEmail');
			
			// dates
			if(preg_match('/create(.*)?date$/i', $key)) $this->storeRecordItem($value, 'createdDate');
			if(preg_match('/update(.*)?date$/i', $key)) $this->storeRecordItem($value, 'updatedDate');
			if(preg_match('/expire(.*)?date$/i', $key)) $this->storeRecordItem($value, 'expiresDate');
			
			// name servers
			if(preg_match('/nameserver/i', $key)) $this->storeRecordItem($value, 'nameServers');
			
			// registrar
			if(preg_match('/(.*)?registrar$/i', $key)) $this->storeRecordItem($value, 'registrarName');
			if(preg_match('/(.*)?status$/i', $key)) $this->storeRecordItem($value, 'registrarStatus');
			
			// registrant
			if(preg_match('/registrant/i', $key))
			{
				$key_prefix = 'registrant';
				if(preg_match('/(.*)?Name$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Name');
				if(preg_match('/(.*)?(Organization|Org)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Org');
				if(preg_match('/(.*)?Street(.)?$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Address');
				if(preg_match('/(.*)?City$/i', $key)) $this->storeRecordItem($value, $key_prefix.'City');
				if(preg_match('/(.*)?(State|Province)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'State');
				if(preg_match('/Postal/i', $key)) $this->storeRecordItem($value, $key_prefix.'PostalCode');
				if(preg_match('/(.*)?Country$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Country');
				if(preg_match('/(.*)?Phone$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Phone');
				if(preg_match('/(.*)?Fax$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Fax');
				if(preg_match('/(.*)?Email$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Email');
			}
			// billing
			if(preg_match('/billing/i', $key))
			{
				$key_prefix = 'billing';
				if(preg_match('/(.*)?Name$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Name');
				if(preg_match('/(.*)?(Organization|Org)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Org');
				if(preg_match('/(.*)?Street(.)?$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Address');
				if(preg_match('/(.*)?City$/i', $key)) $this->storeRecordItem($value, $key_prefix.'City');
				if(preg_match('/(.*)?(State|Province)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'State');
				if(preg_match('/Postal/i', $key)) $this->storeRecordItem($value, $key_prefix.'PostalCode');
				if(preg_match('/(.*)?Country$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Country');
				if(preg_match('/(.*)?Phone$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Phone');
				if(preg_match('/(.*)?Fax$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Fax');
				if(preg_match('/(.*)?Email$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Email');
			}
			// admin
			if(preg_match('/admin/i', $key))
			{
				$key_prefix = 'admin';
				if(preg_match('/(.*)?Name$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Name');
				if(preg_match('/(.*)?(Organization|Org)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Org');
				if(preg_match('/(.*)?Street(.)?$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Address');
				if(preg_match('/(.*)?City$/i', $key)) $this->storeRecordItem($value, $key_prefix.'City');
				if(preg_match('/(.*)?(State|Province)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'State');
				if(preg_match('/Postal/i', $key)) $this->storeRecordItem($value, $key_prefix.'PostalCode');
				if(preg_match('/(.*)?Country$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Country');
				if(preg_match('/(.*)?Phone$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Phone');
				if(preg_match('/(.*)?Fax$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Fax');
				if(preg_match('/(.*)?Email$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Email');
			}
			// tech
			if(preg_match('/tech/i', $key))
			{
				$key_prefix = 'tech';
				if(preg_match('/(.*)?Name$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Name');
				if(preg_match('/(.*)?(Organization|Org)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Org');
				if(preg_match('/(.*)?Street(.)?$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Address');
				if(preg_match('/(.*)?City$/i', $key)) $this->storeRecordItem($value, $key_prefix.'City');
				if(preg_match('/(.*)?(State|Province)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'State');
				if(preg_match('/Postal/i', $key)) $this->storeRecordItem($value, $key_prefix.'PostalCode');
				if(preg_match('/(.*)?Country$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Country');
				if(preg_match('/(.*)?Phone$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Phone');
				if(preg_match('/(.*)?Fax$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Fax');
				if(preg_match('/(.*)?Email$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Email');
			}
		}
		
		// fix the compiled data
		if(is_array($this->record['registrantAddress'])) $this->record['registrantAddress'] = implode("\n", $this->record['registrantAddress']);
		if(is_array($this->record['billingAddress'])) $this->record['billingAddress'] = implode("\n", $this->record['billingAddress']);
		if(is_array($this->record['adminAddress'])) $this->record['adminAddress'] = implode("\n", $this->record['adminAddress']);
		if(is_array($this->record['techAddress'])) $this->record['techAddress'] = implode("\n", $this->record['techAddress']);
		
		// add a hash
		$this->record['sha1'] = sha1(serialize($this->record));
		
		// mimic the fact that this is 1 of many results so it's compatible with the other sources
		$out = array($this->record);
		
		$this->saveRaw($Model, ($this->record['tld']?$this->record['tld']:$query), $raw_content);
		
		$Model->shellOut(__('Results found and returned.'), 'whois', 'info');
		
		return $out;
	}
	
	private function parser_whoiser(Model $Model, $query = false, $automatic = false)
	{
		$out = array();
		if($this->isDisabledParser($Model, 'whoiser', 'month'))
		{
			return $out;
		}
		
		$Model->shellOut(__('Looking up: %s', $query), 'whois', 'info');
		
		if(!$query)
		{
			$Model->shellOut(__('Unknown Host'), 'whois', 'error');
			return $out;
		}
		
		$parser_settings = $this->parser_settings['whoisxmlapi'];
		
		$query = array(
			'domainName' => $query,
			'username' => $parser_settings['username'],
			'password' => $parser_settings['password'],
			'outputFormat' => 'JSON',
		);
		
		if(!$content = $this->Whois_getRemote($Model, $parser_settings['url_base'], $query, 'post'))
		{
			$Model->modelError = __('Unable to lookup the domain from our source.');
			$Model->shellOut($Model->modelError, 'whois', 'error');
			return $out;
		}
		
		$raw_content = $content;
		
		$results = json_decode($content);
		
		if(isset($results->ErrorMessage->msg) and $results->ErrorMessage->msg)
		{
			$Model->modelError = $results->ErrorMessage->msg;
			$Model->shellOut($Model->modelError, 'whois', 'error');
			return $out;
		}
		
		// the default map, like the names in the database
		// reset the record array
		$this->record = $this->defaults;
		
		$results = $this->object_to_array($Model, $results->WhoisRecord);
		$results = Set::flatten($results);
		
		// format the raw text
		$raw = false;
		foreach($results as $key => $value)
		{
			if(preg_match('/raw/i', $key)) $raw .= "\n". $value;
			if(preg_match('/strippedText/i', $key)) $raw .= "\n". $value;
		}
		
		// add the key/value pair from the raw test to the results array
		$raw = explode("\n", $raw);
		foreach($raw as $line)
		{
			$line = trim($line);
			if(!$line) continue;
			if(stripos($line, ':') === false) continue;
			$parts = explode(':', $line);
			if(count($parts) > 1)
			{
				$result_key = 'raw_'. trim(array_shift($parts));
				
				$results[$result_key] = trim(implode(':', $parts));
			}
		}
		
		// extract and map the results to our default fields
		foreach($results as $key => $value)
		{
			$key = trim($key);
			
			// tld
			if(preg_match('/Domain(\s+)?Name$/i', $key)) $this->storeRecordItem($value, 'tld');
			
			// registrar
			if(preg_match('/contactemail/i', $key)) $this->storeRecordItem($value, 'contactEmail');
			
			// dates
			if(preg_match('/create(.*)?date$/i', $key)) $this->storeRecordItem($value, 'createdDate');
			if(preg_match('/update(.*)?date$/i', $key)) $this->storeRecordItem($value, 'updatedDate');
			if(preg_match('/expire(.*)?date$/i', $key)) $this->storeRecordItem($value, 'expiresDate');
			
			// name servers
			if(preg_match('/nameserver/i', $key)) $this->storeRecordItem($value, 'nameServers');
			
			// registrar
			if(preg_match('/(.*)?registrar$/i', $key)) $this->storeRecordItem($value, 'registrarName');
			if(preg_match('/(.*)?status$/i', $key)) $this->storeRecordItem($value, 'registrarStatus');
			
			// registrant
			if(preg_match('/registrant/i', $key))
			{
				$key_prefix = 'registrant';
				if(preg_match('/(.*)?Name$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Name');
				if(preg_match('/(.*)?(Organization|Org)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Org');
				if(preg_match('/(.*)?Street(.)?$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Address');
				if(preg_match('/(.*)?City$/i', $key)) $this->storeRecordItem($value, $key_prefix.'City');
				if(preg_match('/(.*)?(State|Province)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'State');
				if(preg_match('/Postal/i', $key)) $this->storeRecordItem($value, $key_prefix.'PostalCode');
				if(preg_match('/(.*)?Country$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Country');
				if(preg_match('/(.*)?Phone$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Phone');
				if(preg_match('/(.*)?Fax$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Fax');
				if(preg_match('/(.*)?Email$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Email');
			}
			// billing
			if(preg_match('/billing/i', $key))
			{
				$key_prefix = 'billing';
				if(preg_match('/(.*)?Name$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Name');
				if(preg_match('/(.*)?(Organization|Org)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Org');
				if(preg_match('/(.*)?Street(.)?$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Address');
				if(preg_match('/(.*)?City$/i', $key)) $this->storeRecordItem($value, $key_prefix.'City');
				if(preg_match('/(.*)?(State|Province)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'State');
				if(preg_match('/Postal/i', $key)) $this->storeRecordItem($value, $key_prefix.'PostalCode');
				if(preg_match('/(.*)?Country$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Country');
				if(preg_match('/(.*)?Phone$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Phone');
				if(preg_match('/(.*)?Fax$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Fax');
				if(preg_match('/(.*)?Email$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Email');
			}
			// admin
			if(preg_match('/admin/i', $key))
			{
				$key_prefix = 'admin';
				if(preg_match('/(.*)?Name$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Name');
				if(preg_match('/(.*)?(Organization|Org)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Org');
				if(preg_match('/(.*)?Street(.)?$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Address');
				if(preg_match('/(.*)?City$/i', $key)) $this->storeRecordItem($value, $key_prefix.'City');
				if(preg_match('/(.*)?(State|Province)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'State');
				if(preg_match('/Postal/i', $key)) $this->storeRecordItem($value, $key_prefix.'PostalCode');
				if(preg_match('/(.*)?Country$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Country');
				if(preg_match('/(.*)?Phone$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Phone');
				if(preg_match('/(.*)?Fax$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Fax');
				if(preg_match('/(.*)?Email$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Email');
			}
			// tech
			if(preg_match('/tech/i', $key))
			{
				$key_prefix = 'tech';
				if(preg_match('/(.*)?Name$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Name');
				if(preg_match('/(.*)?(Organization|Org)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Org');
				if(preg_match('/(.*)?Street(.)?$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Address');
				if(preg_match('/(.*)?City$/i', $key)) $this->storeRecordItem($value, $key_prefix.'City');
				if(preg_match('/(.*)?(State|Province)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'State');
				if(preg_match('/Postal/i', $key)) $this->storeRecordItem($value, $key_prefix.'PostalCode');
				if(preg_match('/(.*)?Country$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Country');
				if(preg_match('/(.*)?Phone$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Phone');
				if(preg_match('/(.*)?Fax$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Fax');
				if(preg_match('/(.*)?Email$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Email');
			}
		}
		
		// fix the compiled data
		if(is_array($this->record['registrantAddress'])) $this->record['registrantAddress'] = implode("\n", $this->record['registrantAddress']);
		if(is_array($this->record['billingAddress'])) $this->record['billingAddress'] = implode("\n", $this->record['billingAddress']);
		if(is_array($this->record['adminAddress'])) $this->record['adminAddress'] = implode("\n", $this->record['adminAddress']);
		if(is_array($this->record['techAddress'])) $this->record['techAddress'] = implode("\n", $this->record['techAddress']);
		
		// add a hash
		$this->record['sha1'] = sha1(serialize($this->record));
		
		// mimic the fact that this is 1 of many results so it's compatible with the other sources
		$out = array($this->record);
		
		$this->saveRaw($Model, ($this->record['tld']?$this->record['tld']:$query), $raw_content);
		
		$Model->shellOut(__('Results found and returned.'), 'whois', 'info');
		
		return $out;
	}
	
	public function Whois_mapSqlSource(Model $Model, $record = false, $split = false)
	{
		$out = array();
		
		// the default map, like the names in the database
		// reset the record array
		$this->record = $this->defaults;
		
		$results = Set::flatten($record);
		
		// format the raw text
		$raw = false;
		foreach($results as $key => $value)
		{
			if(preg_match('/raw/i', $key)) $raw .= "\n". $value;
			if(preg_match('/strippedText/i', $key)) $raw .= "\n". $value;
		}
		
		// add the key/value pair from the raw test to the results array
		$raw = explode("\n", $raw);
		foreach($raw as $line)
		{
			$line = trim($line);
			if(!$line) continue;
			if(stripos($line, ':') === false) continue;
			$parts = explode(':', $line);
			if(count($parts) > 1)
			{
				$result_key = 'raw_'. trim(array_shift($parts));
				
				$results[$result_key] = trim(implode(':', $parts));
			}
		}
		
		// extract and map the results to our default fields
		foreach($results as $key => $value)
		{
			$key = trim($key);
			
			// SHA1
			if(preg_match('/^sha1$/i', $key)) $this->storeRecordItem($value, 'sha1');
			
			// tld
			if(preg_match('/Domain(\s+)?Name$/i', $key)) $this->storeRecordItem($value, 'tld');
			if(preg_match('/Domain(\s+)?Name$/i', $key)) $this->storeRecordItem($value, 'domain');
			if(preg_match('/^domain$/i', $key)) $this->storeRecordItem($value, 'tld');
			if(preg_match('/^domain$/i', $key)) $this->storeRecordItem($value, 'domain');
			
			// registrar
			if(preg_match('/contactemail/i', $key)) $this->storeRecordItem($value, 'contactEmail');
			if(preg_match('/contact_email/i', $key)) $this->storeRecordItem($value, 'contactEmail');
			if(preg_match('/^RegistrantContact\.email$/i', $key)) $this->storeRecordItem($value, 'contactEmail');
			
			// dates
			if(preg_match('/create(.*)?date$/i', $key)) $this->storeRecordItem($value, 'createdDate');
			if(preg_match('/update(.*)?date$/i', $key)) $this->storeRecordItem($value, 'updatedDate');
			if(preg_match('/expire(.*)?date$/i', $key)) $this->storeRecordItem($value, 'expiresDate');
			
			// name_servers
			if(preg_match('/nameserver/i', $key)) $this->storeRecordItem($value, 'nameServers');
			if(preg_match('/name_servers/i', $key)) $this->storeRecordItem($value, 'nameServers');
			
			// registrar
			if(preg_match('/(.*)?registrar$/i', $key)) $this->storeRecordItem($value, 'registrarName');
			if(preg_match('/registrar_name$/i', $key)) $this->storeRecordItem($value, 'registrarName');
			
			if(preg_match('/(.*)?status$/i', $key)) $this->storeRecordItem($value, 'registrarStatus');
			
			// registrant
			if(preg_match('/registrant/i', $key))
			{
				$key_prefix = 'registrant';
				if(preg_match('/(.*)?Name$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Name');
				if(preg_match('/(.*)?(Organization|Org)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Org');
				if(preg_match('/(.*)?Street(.)?$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Address');
				if(preg_match('/(.*)?address$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Address');
				if(preg_match('/(.*)?City$/i', $key)) $this->storeRecordItem($value, $key_prefix.'City');
				if(preg_match('/(.*)?(State|Province)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'State');
				if(preg_match('/Postal/i', $key)) $this->storeRecordItem($value, $key_prefix.'PostalCode');
				if(preg_match('/(.*)?Country$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Country');
				if(preg_match('/(.*)?Phone$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Phone');
				if(preg_match('/(.*)?Fax$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Fax');
				if(preg_match('/(.*)?Email$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Email');
			}
			// billing
			if(preg_match('/billing/i', $key))
			{
				$key_prefix = 'billing';
				if(preg_match('/(.*)?Name$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Name');
				if(preg_match('/(.*)?(Organization|Org)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Org');
				if(preg_match('/(.*)?Street(.)?$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Address');
				if(preg_match('/(.*)?address$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Address');
				if(preg_match('/(.*)?City$/i', $key)) $this->storeRecordItem($value, $key_prefix.'City');
				if(preg_match('/(.*)?(State|Province)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'State');
				if(preg_match('/Postal/i', $key)) $this->storeRecordItem($value, $key_prefix.'PostalCode');
				if(preg_match('/(.*)?Country$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Country');
				if(preg_match('/(.*)?Phone$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Phone');
				if(preg_match('/(.*)?Fax$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Fax');
				if(preg_match('/(.*)?Email$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Email');
			}
			// admin
			if(preg_match('/admin/i', $key))
			{
				$key_prefix = 'admin';
				if(preg_match('/(.*)?Name$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Name');
				if(preg_match('/(.*)?(Organization|Org)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Org');
				if(preg_match('/(.*)?Street(.)?$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Address');
				if(preg_match('/(.*)?address$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Address');
				if(preg_match('/(.*)?City$/i', $key)) $this->storeRecordItem($value, $key_prefix.'City');
				if(preg_match('/(.*)?(State|Province)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'State');
				if(preg_match('/Postal/i', $key)) $this->storeRecordItem($value, $key_prefix.'PostalCode');
				if(preg_match('/(.*)?Country$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Country');
				if(preg_match('/(.*)?Phone$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Phone');
				if(preg_match('/(.*)?Fax$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Fax');
				if(preg_match('/(.*)?Email$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Email');
			}
			// tech
			if(preg_match('/tech/i', $key))
			{
				$key_prefix = 'tech';
				if(preg_match('/(.*)?Name$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Name');
				if(preg_match('/(.*)?(Organization|Org)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Org');
				if(preg_match('/(.*)?Street(.)?$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Address');
				if(preg_match('/(.*)?address$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Address');
				if(preg_match('/(.*)?City$/i', $key)) $this->storeRecordItem($value, $key_prefix.'City');
				if(preg_match('/(.*)?(State|Province)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'State');
				if(preg_match('/Postal/i', $key)) $this->storeRecordItem($value, $key_prefix.'PostalCode');
				if(preg_match('/(.*)?Country$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Country');
				if(preg_match('/(.*)?Phone$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Phone');
				if(preg_match('/(.*)?Fax$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Fax');
				if(preg_match('/(.*)?Email$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Email');
			}
			// tech
			if(preg_match('/zone/i', $key))
			{
				$key_prefix = 'zone';
				if(preg_match('/(.*)?Name$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Name');
				if(preg_match('/(.*)?(Organization|Org)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Org');
				if(preg_match('/(.*)?Street(.)?$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Address');
				if(preg_match('/(.*)?address$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Address');
				if(preg_match('/(.*)?City$/i', $key)) $this->storeRecordItem($value, $key_prefix.'City');
				if(preg_match('/(.*)?(State|Province)$/i', $key)) $this->storeRecordItem($value, $key_prefix.'State');
				if(preg_match('/Postal/i', $key)) $this->storeRecordItem($value, $key_prefix.'PostalCode');
				if(preg_match('/(.*)?Country$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Country');
				if(preg_match('/(.*)?Phone$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Phone');
				if(preg_match('/(.*)?Fax$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Fax');
				if(preg_match('/(.*)?Email$/i', $key)) $this->storeRecordItem($value, $key_prefix.'Email');
			}
		}
		
		// fix the compiled data
		if(is_array($this->record['registrantAddress'])) $this->record['registrantAddress'] = implode("\n", $this->record['registrantAddress']);
		if(is_array($this->record['billingAddress'])) $this->record['billingAddress'] = implode("\n", $this->record['billingAddress']);
		if(is_array($this->record['adminAddress'])) $this->record['adminAddress'] = implode("\n", $this->record['adminAddress']);
		if(is_array($this->record['techAddress'])) $this->record['techAddress'] = implode("\n", $this->record['techAddress']);
		if(is_array($this->record['zoneAddress'])) $this->record['zoneAddress'] = implode("\n", $this->record['zoneAddress']);
		
		// add a hash
		$this->storeRecordItem(sha1(serialize($this->record)), 'sha1');
		
		// mimic the fact that this is 1 of many results so it's compatible with the other sources
		$out = array($this->record);
		
		return $out;
	}
	
	private function storeRecordItem($value = false, $recordName = false)
	{
		if(!$value) return false;
		
		$value = trim($value);
		
		if(preg_match('/Date/i', $recordName))
		{
			$value = strtotime($value);
			if(date('Y', $value) == 1969) return;
			$value = date('Y-m-d H:i:s', $value);
		}
		
		if(preg_match('/(phone|fax)/i', $recordName))
		{
			$value = preg_replace("/\D/", "", $value);
		}
		
		if($recordName == 'nameServers')
		{
			$value = strtolower($value);
			if(preg_match('/\s+/i', $value))
			{
				$values = preg_split('/\s+/i', $value);
				foreach($values as $value)
				{
					$this->record[$recordName][$value] = $value;
				}
			}
			else
			{
				$this->record[$recordName][$value] = $value;
			}
			return;
		}
		
		if($recordName == 'registrantAddress')
		if(preg_match('/Address/i', $recordName))
		{
			if($value) $this->record[$recordName][$value] = $value;
			return;
		}
		
		if($recordName == 'tld')
		{
			$this->record['tld'] = trim(strtolower($value));
			return;
		}
		
		if($recordName == 'domain')
		{
			$this->record['domain'] = trim(strtolower($value));
			return;
		}
		
		if($this->record[$recordName] == false) $this->record[$recordName] = $value;
	}
	
//
	public function Whois_records(Model $Model, $host)
	{
		return $this->Whois_getContent($Model, $host);
	}
	
//
	public function Whois_getStats(Model $Model)
	{
		$out = array();
		
		$debug = Configure::read('debug');
		
		$parser_keys = array_keys($this->account_settings);
		
		// go through the parsers
		shuffle($parser_keys); // randomize the array to choose a random one to go first
		foreach($parser_keys as $parser_key) 
		{
			$method = 'stat_'. $parser_key;
//			if($this->isDisabledParser($Model, $parser_key)) continue;
			if(!method_exists($this, $method)) continue;
			$out[$parser_key] = $this->{$method}($Model);
			Configure::write('debug', $debug);
		}
		
		return $out;
	}
	
//
	public function Whois_getContent(Model $Model, $host = null, $automatic = false)
	{
		$out = array();
		
		if(!$host) return $out;
		
		$debug = Configure::read('debug');
		
		$parser_keys = array_keys($this->parser_settings);
		
		// go through the parsers
		shuffle($parser_keys); // randomize the array to choose a random one to go first
		foreach($parser_keys as $parser_key) 
		{
			$method = 'parser_'. $parser_key;
			if($this->isDisabledParser($Model, $parser_key)) continue;
			if(!method_exists($this, $method)) continue;
			$out[$parser_key] = $this->{$method}($Model, $host, $automatic);
			Configure::write('debug', $debug);
		}
		
		return $out;
	}
	
//
	public function Whois_getRemote(Model $Model, $url = null, $query = array(), $method = 'get', $headers = array(), $curl_options = array())
	{
		$this->getting_live = $Model->getting_live = false;
		$data = false;
		$cacheKey = md5(serialize(array('url' => $url, 'query' => $query, 'method' => $method)));
		
		if(Cache::read('debug') < 2)
		{
			$data = Cache::read($cacheKey, 'whois');
		}
		
		if ($data !== false)
		{
			$Model->Usage_updateCounts('whois', 'cached');
			$Model->Usage_updateCounts('cached', 'whois');
			
			$Model->shellOut(__('Loaded from cache with key: %s', $cacheKey), 'whois', 'info');
			return $data;
		}
		
		if($url)
		{
			if(!$this->Curl)
			{
				// load the curl object
				$Model->shellOut(__('Loading cUrl.'), 'whois', 'info');
				App::import('Vendor', 'Utilities.Curl');
				$this->Curl = new Curl();
			}
			
			$this->Curl->referer = $url;
				
			// set the curl options
			$this->Curl->followLocation = true;
			$this->Curl->maxRedirs = 5;
			$this->Curl->timeout = 10;
			$this->Curl->connectTimeout = 10;
			
			// cookies
			$this->Curl->cookieFile = CACHE. 'dt_cookieFile';
			$this->Curl->cookieJar = CACHE. 'dt_cookieJar';
			
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
				$Model->shellOut(__('URL: %s - POST Query: %s', $url, $query_url), 'whois', 'info');
				$this->Curl->url = $url;
			}
			else
			{
				$url .= $query_url;
				$Model->shellOut(__('URL: %s', $url), 'whois', 'info');
				$this->Curl->url = $url;
			}
			
			// going for a live connection
			$this->getting_live = $Model->getting_live = true;
			
			$Model->Usage_updateCounts('whois', 'remote');
			$Model->Usage_updateCounts('remote', 'whois');
			
			$data = $this->Curl->execute();
			
			if($this->Curl->error)
			{
				$Model->Usage_updateCounts('whois', 'remote_error');
				$Model->Usage_updateCounts('remote_error', 'whois');
				
				$Model->curlError = $this->curlError = $this->Curl->error;
				$Model->curlErrno = $this->curlErrno = $this->Curl->errno;
				
				$logtype = 'error';
					
				$Model->shellOut(__('Curl Error: (%s) %s -- Url: %s', $this->curlErrno, $this->curlError, $url), 'whois', $logtype);
			}
			else
			{
				if($this->Curl->response_headers)
				{
					$Model->curlHeaders = $this->curlHeaders = $this->Curl->response_headers;
				}
				// cache it
				Cache::write($cacheKey, $data, 'whois');
				$Model->Usage_updateCounts('whois', 'remote_success');
				$Model->Usage_updateCounts('remote_success', 'whois');
			}
			
			// cache it
			Cache::write($cacheKey, $data, 'whois');
		}
		return $data;
	}
	
	private function disableParser(Model $Model, $parser_key = false, $disableLength = false)
	{
		if(!isset($this->parsers_disabled[$parser_key]))
		{
			$this->parsers_disabled[$parser_key] = $parser_key;
			$Model->shellOut(__('The Source: %s is disabled for this session.', $parser_key), 'whois', 'info');
		}
		
		// cache the disabled state for a set period of time
		if($disableLength and isset($this->disableLengths[$disableLength]))
		{
			$timestamp = $this->disableLengths[$disableLength];
			
			// if it doesn't previously exist, then send a notice
			$old_timestamp = Cache::read('disabled_'. $parser_key, 'whois_long');
			
			Cache::write('disabled_'. $parser_key, $timestamp, 'whois_long');
			if(!$old_timestamp)
			{
				$Model->shellOut(__('The Source: %s is disabled for the rest of this %s.', $parser_key, $disableLength), 'whois', 'notice');
			}
		}
	}
	
	private function isDisabledParser(Model $Model, $parser_key = false, $disableLength = false)
	{
		if(isset($this->parsers_disabled[$parser_key]))
		{
			return true;
		}
		
		// cache the disabled state
		elseif($disableLength and isset($this->disableLengths[$disableLength]))
		{
			$timestamp = $this->disableLengths[$disableLength];
			$old_timestamp = Cache::read('disabled_'. $parser_key, 'whois_long');
			
			if($old_timestamp and $timestamp == $old_timestamp)
			{
				// refresh the cache
				Cache::write('disabled_'. $parser_key, $timestamp, 'whois_long');
				
				// set the session disable cache
				$this->parsers_disabled[$parser_key] = $parser_key;
			
				return true;
			}
		}
		
		// check the config to see if it's disabled there
		$parser_settings = (isset($this->parser_settings[$parser_key])? $this->parser_settings[$parser_key]: false);
		if($parser_settings and isset($parser_settings['disabled']) and $parser_settings['disabled'])
		{
			// disable it for this session
			$this->disableParser($Model, $parser_key);
			return true;
		}
		
		return false;
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
	
	public function saveRaw(Model $Model, $query = false, $content = false)
	{
		
		$e = new Exception();
		$trace = $e->getTrace();
		$last_call = $trace[1];
		
		$query_parts = explode('.', $query);
		$query_parts_temp = array_reverse($query_parts);
		$query_parts_first = (int) $query_parts_temp[0];
		if(!$query_parts_first)
		{
			$query_parts = $query_parts_temp;
		}
		
		$filepath = TMP. 'raw'. DS. $last_call['class']. DS. $last_call['function']. DS. implode(DS, $query_parts). DS;
		
		if(!is_dir($filepath))
		{
			umask(0);
			if(!mkdir($filepath, 0777, true))
			{
				return false;
			}
		}
		
		$filepath .= $query;
		return file_put_contents($filepath, $content);
	}
}
?>