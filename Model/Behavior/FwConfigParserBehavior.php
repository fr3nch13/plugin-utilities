<?php
// based on the original script from 
// Jon Spriggs - jon@spriggs.org.uk
// https://fwconfigparser.googlecode.com/svn/trunk/index.php

App::uses('Hash', 'Core');
App::uses('Inflector', 'Core');
class FwConfigParserBehavior extends ModelBehavior
{
	public $file_path = false;
	
	public $error = false;
	
	public $remark_group = false;
	
	public $remark = false;
	
	public function FCP_filePath(Model $Model, $path = false)
	{
		if($path)
		{
			// doesn't exist
			if(!is_file($path)) return false;
			if(!is_readable($path)) return false;
			$this->file_path = $path;
			return $this->file_path;;
		}
		
		return false;
	}
	
	public function FCP_parseFile(Model $Model, $file_path = '', $existing_fogs = array(), $existing_pogs = array())
	{
		if(!$file_path = $this->FCP_filePath($Model, $file_path))
		{
			$Model->modelError = __('(1) Unable to read the file.');
			return false;
		}
		
		if(!$content = file_get_contents($file_path))
		{
			$Model->modelError = __('(2) Unable to read the file.');
			return false;
		}
		
		return $this->FCP_parseString($Model, $content, $existing_fogs, $existing_pogs);
	}
	
	// 
	public function FCP_parseString(Model $Model, $content = '', $fogs = array(), $pogs = array())
	{
		// objects that can have their config across multiple lines
		$object_types = array('hostname', 'domain-name', 'name', 'interface', 'object-group', 'object', 'fog', 'pog', 'access-list');
		
		$rule_cnt=0;
		
		$firewall_hostname = $firewall_name = $firewall_hostname_slug = $firewall_domain_name = false;
		$object_type = $hostname = $domain_name = $if_name = $fog_name = $pog_name = false;
		$names = $interfaces = $protogs = $rules = array();
		
		$lines = explode("\n", $content);

		// find all of the other objects before processing possible rules
		foreach($lines as $line_num => $line) 
		{
			$line = trim($line);
			$arr = preg_split('/\s+/', $line);
			
			$first = strtolower(trim($arr[0]));
			
			if(in_array($first, $object_types))
			{
				$object_type = $first;
			
				//// process the single lines
				switch($object_type)
				{
					case 'hostname':
						$firewall_hostname = trim($arr[1]);
						$firewall_name = $this->FCP_humanize($Model, trim($arr[1]));
						$firewall_hostname_slug = $this->FCP_slugify($Model, $firewall_hostname);
					break;
					
					case 'domain-name':
						$firewall_domain_name = trim($arr[1]);
					break;
					
					// host aliases
					case 'name':
						$names[trim($arr[1])] = trim($arr[2]);
					break;
					
					case 'interface':
						$if_name = trim($arr[1]);
						$if_slug = $this->FCP_slugify($Model, $if_name);
						$interfaces[$if_slug] = array('name' => $if_name, 'slug' => $if_slug);
						continue 2;
					break;
					
					case 'object-group':
					case 'object':
						$orig_object_type = $object_type;
						$second = strtolower(trim($arr[1]));
						$og_name = (isset($arr[2])?trim($arr[2]):false);
						$og_slug = $this->FCP_slugify($Model, $og_name);
						$protocol = (isset($arr[3])?strtolower(trim($arr[3])):false);
						$proto_slug = $this->FCP_slugify($Model, $protocol);
						if($second == 'network') 
						{
							$object_type = 'fog';
							$fog_name = $og_name;
							$fog_slug = $og_slug;
							if(!isset($fogs[$fog_slug]))
							{
								$fogs[$fog_slug] = [];
							}
							$fogs[$fog_slug]['name'] = $fog_name;
							$fogs[$fog_slug]['slug'] = $fog_slug;
							$fogs[$fog_slug]['type'] = $orig_object_type;
						}
						elseif($second == 'service')
						{
							$object_type = 'pog';
							$pog_name = $og_name;
							$pog_slug = $og_slug;
							if($protocol)
							{
								if(!isset($pogs[$pog_slug]))
								{
									$pogs[$pog_slug] = [];
								}
								$pogs[$pog_slug]['name'] = $pog_name;
								$pogs[$pog_slug]['slug'] = $pog_slug;
								$pogs[$pog_slug]['type'] = $orig_object_type;
								$pogs[$pog_slug]['protocol'] = $protocol;
								$pogs[$pog_slug]['protocol_slug'] = $proto_slug;
							}
							else
							{
								if(!isset($protogs[$pog_slug]))
								{
									$protogs[$pog_slug] = [];
								}
								$protogs[$pog_slug]['name'] = $pog_name;
								$protogs[$pog_slug]['slug'] = $pog_slug;
								$protogs[$pog_slug]['type'] = $orig_object_type;
							}
							
							// track the protocol as a protocol object group as well
							if($proto_slug)
							{
								if(!isset($protogs[$pog_slug]))
								{
									$protogs[$pog_slug] = [];
								}
								$protogs[$pog_slug]['name'] = $protocol;
								$protogs[$pog_slug]['slug'] = $proto_slug;
								$protogs[$pog_slug]['type'] = $orig_object_type;
								
								if(!isset($protogs[$pog_slug]['protocol']))
									$protogs[$pog_slug]['protocol'] = [];
								$protogs[$pog_slug]['protocol'][$protocol] = $protocol;
							}
						}
						continue 2;
					break;
				}
			}

			//// process the multiple line configurations
			$continue = false;
			switch($object_type)
			{
				// Interfaces
				case 'interface':
					switch($first)
					{
						case 'speed':
						case 'duplex':
						case 'nameif':
						case 'security-level':
							$interfaces[$if_slug][$first] = trim($arr[1]);
						break;
						
						case 'ip':
							array_shift($arr);
							array_shift($arr);
							$interfaces[$if_slug]['ip_address'] = implode(' ', $arr);
						break;
						
						case 'shutdown':
							$interfaces[$if_slug]['shutdown'] = true;
						break;
						
						case 'no':
							$k = trim($arr[1]);
							if($k == 'ip') $k = 'ip_address';
							$interfaces[$if_slug][$k] = false;
						break;
						
						case 'description':
							array_shift($arr);
							$interfaces[$if_slug]['description'] = implode(' ', $arr);
						break;
					}
				break;
				
				// Firewall Object Groups
				case 'fog':
					$second = (isset($arr[1])?$this->FCP_slugify($Model, $arr[1]):false);
					switch($first)
					{
						case 'network-object':
						case 'host':
						case 'range':
							array_shift($arr);
							if(in_array($second, ['host', 'range', 'object'])) array_shift($arr);
							$v = implode(' ', $arr);
							$v_slug = $this->FCP_slugify($Model, $v);
							
							if($second == 'object')
							{
								$fogs[$fog_slug]['groups'][$v_slug] = $v;
							}
							else
							{
								sort($arr);
								$k = strtolower(trim(implode('_', $arr)));
								$fogs[$fog_slug]['hosts'][$k] = $v;
							}
						break;
						
						case 'group-object':
						case 'object':
							array_shift($arr);
							$v = implode(' ', $arr);
							$v_slug = $this->FCP_slugify($Model, $v);
							$fogs[$fog_slug]['groups'][$v_slug] = $v;
						break;
						
						case 'description':
							array_shift($arr);
							$fogs[$fog_slug]['description'] = implode(' ', $arr);
						break;
					}
				break;
				
				// Port Object Groups
				case 'pog':
					$second = (isset($arr[1])?$this->FCP_slugify($Model, $arr[1]):false);
					switch($first)
					{
						case 'port-object':
							$port = trim($arr[2]);
							if($second == 'range')
							{
								$tmparr = $arr;
								array_shift($tmparr);
								array_shift($tmparr);
								$port = implode(' ', $tmparr);
							}
							$pogs[$pog_slug]['ports'][$port] = $port;
						break;
						
						case 'service-object':
							$tmparr = $arr;
							array_shift($tmparr);
							$port = implode(' ', $tmparr);
							$protogs[$pog_slug]['protocols'][$port] = $port;
						break;
						
						case 'group-object':
							$pogs[$pog_slug]['groups'][$second] = $arr[1];
						break;
						
						case 'description':
							array_shift($arr);
							$pogs[$pog_slug]['description'] = implode(' ', $arr);
						break;
					}
				break;
			}
		}
		
		ksort($names);
		ksort($interfaces);
		ksort($fogs);
		ksort($pogs);
		ksort($protogs);
		
		$out = array(
			'name' => $firewall_name,
			'hostname' => $firewall_hostname,
			'hostname_slug' => $firewall_hostname_slug,
			'domain_name' => $firewall_domain_name,
			'names' => $names,
			'interfaces' => $interfaces,
			'fogs' => $fogs,
			'pogs' => $pogs,
			'protogs' => $protogs,
		);
		
		// Process the possible rules once we have found the other objects
		foreach($lines as $line_num => $line) 
		{
			$line = trim($line);
			$arr = preg_split('/\s+/', $line);
			
			$first = strtolower(trim($arr[0]));
			
			if(in_array($first, $object_types))
			{
				$object_type = $first;
			
				//// process the single lines
				switch($object_type)
				{
					// the rules
					case 'access-list':
						if($rule  = $this->FCP_processRuleLine($Model, $line, $out))
						{
							$rule_key = sha1(trim($rule['line']));
							$rules[$rule_key] = $rule;
							$rule_cnt++;
						}
					break;
				}
			}
		}
		
		$out['rules'] = $rules;
		$rules = [];
		
		return $out;
	}
	
	public function FCP_processRuleLine(Model $Model, $line = false, $total_out = array())
	{
		$line = trim($line);
		if(!$line) return false;
		
		// default
		$out = array(
			'line' => $line,
			'interface' => false,
			'permit' => false,
			'protocol' => false,
			'src_ip' => false,
			'src_port' => false,
			'src_fog' => false,
			'src_fog_slug' => false,
			'src_pog' => false,
			'src_pog_slug' => false,
			'dst_ip' => false,
			'dst_port' => false,
			'dst_fog' => false,
			'dst_fog_slug' => false,
			'dst_pog' => false,
			'dst_pog_slug' => false,
			'logging' => false,
			'remark' => false,
		);
		
		$arr = preg_split('/\s+/', $line);
		
		$interface = trim($arr[1]);
		
		$remark = $permit = $protocol = false;
		$src_fog = $src_pog = $src_ip = $src_port = false;
		$dst_fog = $dst_pog = $dst_ip = $dst_port = false;
		
		switch(strtolower(trim($arr[2])))
		{
			// a comment
			case 'remark':
				array_shift($arr);
				array_shift($arr);
				array_shift($arr);
				$remark = $this->FCP_setRemark($Model, implode(' ', $arr));
				return array('line' => $line, 'remark' => $remark);
			break;
			
			// an actual rule
			case 'extended':
				if(!in_array($arr[3], array('permit', 'deny')))
				{
					return false;
				}
			break;
			
			default:
				return false;
		}
		
		$not_hosts = array('access-list', 'extended');
		
		
		// collect just the host/pog/fog stuff
		$host_stuff = $non_host_stuff = array();
		foreach($arr as $i => $str)
		{
			$lowstr = trim(strtolower($str));
			if(in_array($lowstr, $not_hosts)) 
			{
				$non_host_stuff[] = $str;
				continue;
			}
			$host_stuff[] = $str;
		}
		if(!count($host_stuff)) return false;
		
		$out['interface'] = strtolower(trim($host_stuff[0]));
		array_shift($host_stuff);
		
		$out['permit'] = strtolower(trim($host_stuff[0]));
		array_shift($host_stuff);
		
		$protocol = strtolower(trim($host_stuff[0]));
		if($protocol == 'object-group')
		{
			$out['protocol'] = trim($host_stuff[1]);
			array_shift($host_stuff);
			array_shift($host_stuff);
		}
		else
		{
			$out['protocol'] = strtolower(trim($host_stuff[0]));
			array_shift($host_stuff);
		}
		
		while(count($host_stuff))
		{
			$drop = 0;
			if(isset($host_stuff[0]))
			{
				$slug = false;
				if(isset($host_stuff[1]))
				{
					$slug = $this->FCP_slugify($Model, $host_stuff[1]);
				}
				switch(strtolower(trim($host_stuff[0])))
				{
					
					// object group
					case 'object-group':
					case 'object':
						// fog
						if(isset($total_out['fogs'][$slug]))
						{
							if(!$out['src_fog'] and !$out['src_ip'])
							{
								$out['src_fog'] = trim($host_stuff[1]);
								$out['src_fog_slug'] = $this->FCP_slugify($Model, $host_stuff[1]);
							}
							else
							{
								$out['dst_fog'] = trim($host_stuff[1]);
								$out['dst_fog_slug'] = $this->FCP_slugify($Model, $host_stuff[1]);
							}
							$drop = 2;
						}
						// pog
						elseif(isset($total_out['pogs'][$slug]))
						{
							if($out['dst_fog'] or $out['dst_ip'])
							{
								$out['dst_pog'] = trim($host_stuff[1]);
								$out['dst_pog_slug'] = $this->FCP_slugify($Model, $host_stuff[1]);
							}
							else
							{
								$out['src_pog'] = trim($host_stuff[1]);
								$out['src_pog_slug'] = $this->FCP_slugify($Model, $host_stuff[1]);
							}
							$drop = 2;
						}
						else
						{
							if(!$out['src_fog'] and !$out['src_ip'])
							{
								$out['src_fog'] = trim($host_stuff[1]);
								$out['src_fog_slug'] = $this->FCP_slugify($Model, $host_stuff[1]);
							}
							elseif(!$out['src_pog'] and !$out['src_port'])
							{
								$out['src_pog'] = trim($host_stuff[1]);
								$out['src_pog_slug'] = $this->FCP_slugify($Model, $host_stuff[1]);
							}
							elseif(!$out['dst_fog'] and !$out['dst_ip'])
							{
								$out['dst_fog'] = trim($host_stuff[1]);
								$out['dst_fog_slug'] = $this->FCP_slugify($Model, $host_stuff[1]);
							}
							elseif(!$out['dst_pog'] and !$out['dst_port'])
							{
								$out['dst_pog'] = trim($host_stuff[1]);
								$out['dst_pog_slug'] = $this->FCP_slugify($Model, $host_stuff[1]);
							}
							$drop = 2;
						}
					break;
					
					// host
					case 'host':
						if(!$out['src_ip'] and !$out['src_fog'])
						{
							$out['src_ip'] = trim($host_stuff[1]);
						}
						else
						{
							$out['dst_ip'] = trim($host_stuff[1]);
						}
						$drop = 2;
					break;
					
					//  port
					case 'eq':
						if(!$out['src_ip'] and !$out['src_fog'])
						{
							$out['src_port'] = trim($host_stuff[1]);
						}
						else
						{
							$out['dst_port'] = trim($host_stuff[1]);
						}
						$drop = 2;
					break;
					
					// hosts
					case 'any':
						if(!$out['src_ip'] and !$out['src_fog'])
						{
							$out['src_ip'] = trim($host_stuff[0]);
						}
						else
						{
							$out['dst_ip'] = trim($host_stuff[0]);
						}
						$drop = 1;
					break;
					
					// logging stuff
					case 'log':
						$drop = count($host_stuff);
						$out['logging'] = implode(' ', $host_stuff);
					break;
					
					// look for the masks
					default:
						if(preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/i', $host_stuff[0]))
						{
							if(!$out['src_ip'] and !$out['src_fog'])
							{
								$out['src_ip'] = trim($host_stuff[0]);
								$drop = 1;
								if(isset($host_stuff[1])) 
								{
									$host_stuff[1] = trim($host_stuff[1]);
									if(preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/i', $host_stuff[1]))
									{
										$out['src_ip'] .= ' '. trim($host_stuff[1]);
										$drop = 2;
									}
								}
							}
							else
							{
								$out['dst_ip'] = trim($host_stuff[0]);
								if(isset($host_stuff[1])) 
								{
									$host_stuff[1] = trim($host_stuff[1]);
								$drop = 1;
									if(preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/i', $host_stuff[1]))
									{
										$out['dst_ip'] .= ' '. trim($host_stuff[1]);
										$drop = 2;
									}
								}
							}
							$drop = 2;
						}
						elseif(preg_match('/^any/i', $host_stuff[0]))
						{
							if(!$out['src_ip'] and !$out['src_fog'])
							{
								$out['src_ip'] = trim($host_stuff[0]);
							}
							else
							{
								$dst_ip = [$host_stuff[0]];
								if(isset($host_stuff[1]) and trim($host_stuff[1]) and !in_array(trim($host_stuff[1]), ['log', 'eq']))
									$dst_ip[] = trim($host_stuff[1]);
								$out['dst_ip'] = implode(' ', $dst_ip);
							}
							$drop = 1;
						}
					break;
				}
			}
			else
			{
				// jump out of the loop
				break;
			}
			if($drop)
			{
				for ($i = 1; $i <= $drop; $i++)
				{
					if(count($host_stuff)) array_shift($host_stuff);
				}
			}
			else
			{
				if(count($host_stuff)) array_shift($host_stuff);
			}
		}
		
		$out['remark'] = $this->FCP_setRemark($Model);
		
		// if the src pog is set, but not the dst fog or dst ip, then the src pog is actually the dst fog
		if($out['src_pog'] and !$out['dst_ip'] and !$out['dst_fog'])
		{
			$out['dst_fog'] = $out['src_pog'];
			$out['src_pog'] = false;
			$out['dst_fog_slug'] = $out['src_pog_slug'];
			$out['src_pog_slug'] = false;
		}
		return $out;
	}
	
	public function FCP_getHostRange(Model $Model, $ipaddress = false, $netmask = false)
	{
		$ipadd=explode('.', trim($ipaddress));
		
		switch($netmask)
		{
			case '255.0.0.0':
				$scope[1]=255;
				$scope[2]=255;
				$scope[3]=255;
			case '255.128.0.0':
				$scope[1]=127;
				$scope[2]=255;
				$scope[3]=255;
			case '255.192.0.0':
				$scope[1]=63;
				$scope[2]=255;
				$scope[3]=255;
			case '255.224.0.0':
				$scope[1]=31;
				$scope[2]=255;
				$scope[3]=255;
			case '255.240.0.0':
				$scope[1]=15;
				$scope[2]=255;
				$scope[3]=255;
			case '255.248.0.0':
				$scope[1]=7;
				$scope[2]=255;
				$scope[3]=255;
			case '255.252.0.0':
				$scope[1]=3;
				$scope[2]=255;
				$scope[3]=255;
			case '255.254.0.0':
				$scope[1]=1;
				$scope[2]=255;
				$scope[3]=255;
			case '255.255.0.0':
				$scope[1]=0;
				$scope[2]=255;
				$scope[3]=255;
			case '255.255.128.0':
				$scope[1]=0;
				$scope[2]=127;
				$scope[3]=255;
			case '255.255.192.0':
				$scope[1]=0;
				$scope[2]=63;
				$scope[3]=255;
			case '255.255.224.0':
				$scope[1]=0;
				$scope[2]=31;
				$scope[3]=255;
			case '255.255.240.0':
				$scope[1]=0;
				$scope[2]=15;
				$scope[3]=255;
			case '255.255.248.0':
				$scope[1]=0;
				$scope[2]=7;
				$scope[3]=255;
			case '255.255.252.0':
				$scope[1]=0;
				$scope[2]=3;
				$scope[3]=255;
			case '255.255.254.0':
				$scope[1]=0;
				$scope[2]=1;
				$scope[3]=255;
			case '255.255.255.0':
				$scope[1]=0;
				$scope[2]=0;
				$scope[3]=255;
			break;
			
			case '255.255.255.128':
				$scope[1]=0;
				$scope[2]=0;
				$scope[3]=127;
			break;
			
			case '255.255.255.192':
				$scope[1]=0;
				$scope[2]=0;
				$scope[3]=63;
			break;
			
			case '255.255.255.224':
				$scope[1]=0;
				$scope[2]=0;
				$scope[3]=31;
			break;
			
			case '255.255.255.240':
				$scope[1]=0;
				$scope[2]=0;
				$scope[3]=15;
			break;
			
			case '255.255.255.248':
				$scope[1]=0;
				$scope[2]=0;
				$scope[3]=7;
			break;
			
			case '255.255.255.252':
				$scope[1]=0;
				$scope[2]=0;
				$scope[3]=3;
			break;
			
			default:
				$scope[1]=0;
				$scope[2]=0;
				$scope[3]=0;
  		}
  		
  		if($scope[1] != 0 AND $scope[2] != 0 AND $scope[3] != 0)
  		{
  			$ip0 = $ipadd[0];
  			for($ip1 = $ipadd[1]; $ip1 <= $ipadd[1] + $scope[1]; $ip1++)
  			{
  				for($ip2 = $ipadd[2]; $ip2 <= $ipadd[2] + $scope[2]; $ip2++)
  				{
  					for($ip3=$ipadd[3]; $ip3<=$ipadd[3]+$scope[3]; $ip3++)
  					{
  						$return[] = $ip0. '.'. $ip1. '.'. $ip2. '.'. $ip3;
  					}
  				}
  			}
  		}
  		else
  		{
  			$return = trim($ipaddress). ' / '. trim($netmask);
  		}
  		
  		return($return);
  	}
  	
  	public function FCP_slugify(Model $Model, $string = false)
  	{
  		return strtolower(Inflector::slug($string));
  	}
  	
  	public function FCP_humanize(Model $Model, $string = false)
  	{
  		$string = str_replace(array('-', '_'), ' ', $string);
  		return Inflector::humanize($string);
  	}
  	
  	public function FCP_setRemark(Model $Model, $remark = false)
  	{
  		$out = false;
  		// set the remark
  		if($remark)
  		{
  			$remark = trim($remark);
  			
  			// start of a group
  			if(preg_match('/^\*/', $remark))
  			{
  				$remark = trim($remark, ' *');
  			}
  			
  			// start the group prefix
  			if(preg_match('/^start/i', $remark))
  			{
  				$remark = preg_replace('/^start(\s+of)?\s+/i', '', $remark);
  				$this->remark_group = $remark;
  			}
  			// clear the group prefix
  			elseif(preg_match('/^(end|Closure)/i', $remark))
  			{
  				$this->remark_group = false;
  			}
  			// set the one-off comment
  			else
  			{
  				$out = $this->remark = $remark;
  			}
  			return $remark;
  		}
  		
  		// get the remark
  		elseif($this->remark or $this->remark_group)
  		{
  			$out = array();
  			// set from previous line
  			if($this->remark_group)
  			{
  				$out[] = $this->remark_group;
  			}
  			if($this->remark) 
  			{
  				$out[] = $this->remark;
  				$this->remark = false;
  			}
  			
  			$out = implode(' -- ', $out);
  		}
  		return $out;
  	}
}