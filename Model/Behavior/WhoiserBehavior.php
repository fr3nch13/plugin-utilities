<?php

App::uses('AuthComponent', 'Controller/Component');

class WhoiserBehavior extends ModelBehavior 
{
	
	private $whoiser_settings = false;
	
	private $Curl = false;
	
	public $defaults = array(
		'sha1' => false,
		'source' => false,
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
		$this->whoiser_settings = Configure::read('Whoiser.settings');
	}
	
/////// These functions are used by
	public function Whoiser_submitSearch(Model $Model, $string = false)
	{
		if(!$string)
		{
			return array(
				'success' => false,
				'msg' => __('Unknown string to search.'),
			);
		}
		
		$query = array(
			'email' => $this->whoiser_settings['email'],
			'api_key' => $this->whoiser_settings['api_key'],
			'search_term' => $string,
			'notify_email' => (AuthComponent::user('email')?AuthComponent::user('email'):false),
		);
		
		$results = $this->Whoiser_getRemote($Model, $this->whoiser_settings['search_url'], $query, $method = 'post');
		
		$results = json_decode($results);
		$results = $this->object_to_array($Model, $results);
		
		if(isset($results['results'])) $results = $results['results'];
		
		return $results;
	}
	
//
	public function Whoiser_checkStatus(Model $Model, $search_id = false)
	{
		if(!$search_id)
		{
			return array(
				'success' => false,
				'msg' => __('Unknown Search to check.'),
			);
		}
		
		$query = array(
			'email' => $this->whoiser_settings['email'],
			'api_key' => $this->whoiser_settings['api_key'],
		);
		
		$url = __($this->whoiser_settings['status_url'], $search_id);
		
		$results = $this->Whoiser_getRemote($Model, $url, $query, $method = 'post');
		
		$results = json_decode($results);
		$results = $this->object_to_array($Model, $results);
		
		if(isset($results['results'])) $results = $results['results'];
		
		return $results;
	}
	
//
	public function Whoiser_getDetails(Model $Model, $search_id = false)
	{
		if(!$search_id)
		{
			return array(
				'success' => false,
				'msg' => __('Unknown Search to check.'),
			);
		}
		
		$query = array(
			'email' => $this->whoiser_settings['email'],
			'api_key' => $this->whoiser_settings['api_key'],
		);
		
		$url = __($this->whoiser_settings['details_url'], $search_id);
		
		$results = $this->Whoiser_getRemote($Model, $url, $query, $method = 'post');
		
		$results = json_decode($results);
		$results = $this->object_to_array($Model, $results);
		
		if(isset($results['results'])) $results = $results['results'];
		
		return $results;
	}
	
	public function Whoiser_mapSqlSource(Model $Model, $record = false)
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
			
			// source
			if(preg_match('/^source$/i', $key)) $this->storeRecordItem($value, 'sha1');
			
			// tld
			if(preg_match('/^domain$/i', $key)) $this->storeRecordItem($value, 'tld');
			if(preg_match('/^domain$/i', $key)) $this->storeRecordItem($value, 'domain');
			
			// registrar
			if(preg_match('/contact_email/i', $key)) $this->storeRecordItem($value, 'contactEmail');
			if(preg_match('/^RegistrantContact\.email$/i', $key)) $this->storeRecordItem($value, 'contactEmail');
			
			// dates
			if(preg_match('/create(.*)?date$/i', $key)) $this->storeRecordItem($value, 'createdDate');
			if(preg_match('/update(.*)?date$/i', $key)) $this->storeRecordItem($value, 'updatedDate');
			if(preg_match('/expire(.*)?date$/i', $key)) $this->storeRecordItem($value, 'expiresDate');
			
			// name_servers
			if(preg_match('/nameserver$/i', $key)) $this->storeRecordItem($value, 'nameServers');
			
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
		
		// fill out possible blank lines
		// add a hash
		$this->storeRecordItem(sha1(serialize($this->record)), 'sha1');
		// add a source 
		$this->storeRecordItem('whoiser', 'source');
		
		// mimic the fact that this is 1 of many results so it's compatible with the other sources
		// not needed here in Whoiser. this is copied and modified from the WhoisBehavior
		// $out = array($this->record);
		// return $out;
		return $this->record;
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
	public function Whoiser_getRemote(Model $Model, $url = null, $query = array(), $method = 'get', $headers = array(), $curl_options = array())
	{
		$data = false;
		$cacheKey = md5(serialize(array('url' => $url, 'query' => $query, 'method' => $method)));
		
		if(Cache::read('debug') < 2)
		{
//			$data = Cache::read($cacheKey, 'whoiser');
		}
		
		if ($data !== false)
		{
			return $data;
		}
		
		if($url)
		{
			if(!$this->Curl)
			{
				// load the curl object
				$Model->shellOut(__('Loading cUrl.'), 'whoiser', 'info');
				App::import('Vendor', 'Utilities.Curl');
				$this->Curl = new Curl();
			}
			
			$this->Curl->referer = $url;
			
			// set the curl options
			$this->Curl->followLocation = true;
			$this->Curl->maxRedirs = 5;
			$this->Curl->timeout = 10;
			$this->Curl->connectTimeout = 10;
			$this->Curl->sslVerifyHost = 0;
			$this->Curl->sslVerifyPeer = false;
			$this->Curl->sslVersion = 3;
			
			// cookies
			$this->Curl->cookieFile = CACHE. 'dt_cookieFile';
			$this->Curl->cookieJar = CACHE. 'dt_cookieJar';
			
			if(is_array($curl_options) and !empty($curl_options))
			{
				foreach($curl_options as $k => $v)
				{
					$this->Curl->{$k} = $v;
				}
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
				$Model->shellOut(__('URL: %s - POST Query: %s', $url, $query_url), 'whoiser', 'info');
				$this->Curl->url = $url;
			}
			else
			{
				$url .= $query_url;
				$Model->shellOut(__('URL: %s', $url), 'whoiser', 'info');
				$this->Curl->url = $url;
			}
			
			$data = $this->Curl->execute();
			
			if($this->Curl->error)
			{
				$Model->shellOut(__('Curl Error: %s', $this->Curl->error), 'whoiser', 'error');
			}
			
			// cache it
			Cache::write($cacheKey, $data, 'whoiser');
		}
		return $data;
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
}