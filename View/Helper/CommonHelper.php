<?php

App::uses('Hash', 'Core');
App::uses('UtilitiesAppHelper', 'Utilities.View/Helper');

/*
 * Used as a helper with common functionality for generic functions
 */

class CommonHelper extends UtilitiesAppHelper 
{

	public $Curl = false;
	
	public $curlHeaders = [];
	
	public function yesNo($toggle = 0, $raw = false)
	{
		$out = false;
		if(!$toggle) $out = __('No');
		else $out = __('Yes');
		
		return $out;
	}
	
	public function yesNoUnknown($toggle = 0)
	{
		if($toggle == 0) return __('Unknown');
		if($toggle == 1) return __('No');
		if($toggle == 2) return __('Yes');
		return '';
	}
	
	public function check($toggle = 0)
	{
		if(!$toggle) return __(' ');
		return __('X');
	}
	
	public function range($start = 0, $end = 0)
	{
		$out = array();
		if($range = range($start, $end))
		{
			foreach($range as $ittr)
			{
				$out[$ittr] = $ittr;
			}
		}
		return $out;
	}

	public function arrayDepth($array = array())
	{
		if (!is_array($array))
		{
			return false;
		}
		
		if(empty($array))
		{
			return 0;
		}
		
		$array = Hash::flatten($array);
		
		$len = $count = 0;
		foreach($array as $k => $v)
		{
			$count = count(explode('.', $k));
			if($count > $len) $len = $count;
		}
		return $len;
	}
	
	public function loadPluginMenuItems($role = false, $plugin = false)
	{
		if($plugin)
		{
			$plugins = array($plugin); 
		}
		else
		{
			$plugins = CakePlugin::loaded();
		}
		
		$out = array();
		foreach($plugins as $plugin)
		{
			$path = CakePlugin::path($plugin);
			
			$menu_filename = 'include_menu_main'. ($role?'_'.$role:''). '.ctp';
			$menu_path = $path. 'View'. DS. 'Elements'. DS. $menu_filename;
			if(is_readable($menu_path))
			{
				include($menu_path);
			}
		}
	}
	
	/* a wrapper around the view's element, to allow them to work in the included menus above */
	public function element($name, $data = array(), $options = array())
	{
		return $this->_View->element($name, $data, $options);
	}
	
	/* A copy of this is in the WrapHelper. Use this instead. */
	public function userRoles($nice = true)
	{
		$roles = Configure::read('Routing.prefixes');
		
		$out = array();
		foreach($roles as $role)
		{
			$role_k = $role;
			$role_v = $role;
			if($nice)
			{
				$role_v = Inflector::humanize($role);
			}
			$out[$role_k] = $role_v;
		}
		ksort($out);
		
		return $out;
	}
		
	/* A copy of this is in the WrapHelper. Use this instead. */
	public function userRole($user_role = false)
	{
		if(!$user_role)
		{
			$user_role = AuthComponent::user('role');
			if(!$user_role) return false;
		}
		return Inflector::humanize($user_role);
	}
	
	public function userGreeting()
	{
		$user = false;
		if(AuthComponent::user('name'))
			$user = AuthComponent::user('name');
		elseif(AuthComponent::user('adaccount'))
			$user = AuthComponent::user('adaccount');
		elseif(AuthComponent::user('email'))
			$user = AuthComponent::user('email');
		
		return __('%s %s', __('Welcome:'), $user);
	}
	
	/* A copy of this is in the WrapHelper. Use this instead. */
	public function roleCheck($roles = false, $user_role = false)
	{
		if(!$roles) return false;
		if(!$user_role)
		{
			$user_role = AuthComponent::user('role');
			if(!$user_role) return false;
		}
		
		if(!is_array($roles))
		{
			$roles = array($roles);
		}
		
		if(in_array($user_role, $roles)) 
		{
			return true;
		}
		return false;
	}
	
	public function dashboardUserRole()
	{
		$userRole = AuthComponent::user('role');
		
		$matches = array();
		if(!preg_match('/^db_(.*)/', $userRole, $matches))
		{
			return false;
		}
		$userRole = $matches[1];
		
		return $userRole;
	}
	
	public function isAdmin()
	{
		if(isset($this->request->params['admin']) and $this->request->params['admin'])
		{
			return true;
		}
		
		return false;
	}
	
	public function slugify($string = false)
	{
		return strtolower(Inflector::slug(trim($string)));
	}
	
	public function niceNumber($number = 0, $options = array())
	{
		if(!isset($options['before'])) $options['before'] = '';
		if(!isset($options['places'])) $options['places'] = 0;
		return $this->Number->format($number, $options);
	}
	
	public function nicePrice($number = 0, $options = array())
	{
		if(!isset($options['before'])) $options['before'] = '$';
		if(!isset($options['places'])) $options['places'] = 2;
		return $this->niceNumber($number, $options);
	}
	
	public function formatBytes($bytes, $precision = 1)
	{	
		$units = array('B', 'KB', 'MB', 'GB', 'TB');
		
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);
		
		$bytes /= pow(1024, $pow);
		
		return __('%s %s', round($bytes, $precision), $units[$pow]);
	}
	
	public function toByteSize($p_sFormatted = 0) 
	{
		$aUnits = array('B'=>0, 'KB'=>1, 'M'=>2, 'MB'=>2, 'G'=>3, 'GB'=>3, 'T'=>4, 'TB'=>4, 'P'=>5, 'PB'=>5, 'E'=>6, 'EB'=>6, 'Z'=>7, 'ZB'=>7, 'Y'=>8, 'YB'=>8);
		$sUnit2 = strtoupper(trim(substr($p_sFormatted, -2)));
		$sUnit1 = strtoupper(trim(substr($p_sFormatted, -1)));
		$sUnit = false;
		$iUnits = false;
		
		if(intval($sUnit) !== 0) 
		{
			$sUnit = 'B';
		}
		if(in_array($sUnit2, array_keys($aUnits))) 
		{
			$sUnit = $sUnit2;
			$iUnits = trim(substr($p_sFormatted, 0, strlen($p_sFormatted) - 2));
		}
		if(in_array($sUnit1, array_keys($aUnits))) 
		{
			$sUnit = $sUnit1;
			$iUnits = trim(substr($p_sFormatted, 0, strlen($p_sFormatted) - 1));
		}
		
		if(!intval($iUnits) == $iUnits) 
		{
			return false;
		}
		return $iUnits * pow(1024, $aUnits[$sUnit]);
	}
	
	public function maxFileSize()
	{
		$max_upload = ini_get('upload_max_filesize')?ini_get('upload_max_filesize'):0;
		$max_upload = $this->toByteSize($max_upload);
		$max_post = ini_get('post_max_size')?ini_get('post_max_size'):0;
		$max_post = $this->toByteSize($max_post);
		$memory_limit = ini_get('memory_limit')?ini_get('memory_limit'):0;
		$memory_limit = $this->toByteSize($memory_limit);
		$upload_bytes = min($max_upload, $max_post, $memory_limit);
		
		if($upload_bytes)
		{
			return $this->formatBytes($upload_bytes);
		}
		return __('Unknown');
	}
	
	public function coloredCell($object = false, $options = array())
	{
		if(!$object)
			return false;
		
		$colorOptions = $this->setColorOptions($object);
		$options = array_merge($colorOptions, $options);
		
		$displayValue = false;
		
		$displayField = 'name';
		if(isset($options['displayField']))
		{
			$displayField = $options['displayField'];
			if(isset($object[$displayField]))
				$displayValue = $object[$displayField];
			unset($options['displayField']);
		}
		
		if(isset($options['displayValue']))
		{
			$displayValue = $options['displayValue'];
			unset($options['displayValue']);
		}
		
		if(isset($options['colorShow']))
		{
			$options['data-color-show'] = $options['colorShow'];
			unset($options['colorShow']);
		}
		
		if(!isset($object['color_code_hex']))
			return array(
				$displayValue,
				$options,
			);
		
		return array(
			$displayValue,
			$options,
		);
	}
	
	public function setColorOptions($object)
	{
		$options = array('data-color-show' => false);
		
		if(!isset($object['color_code_hex']))
			return $options;
		
		$options['data-color-hex'] = $object['color_code_hex'];
		
		if(!isset($object['color_code_rgb']))
			$object['color_code_rgb'] = $this->makeRGBfromHex( $object['color_code_hex']);
		$options['data-color-rgb'] = $object['color_code_rgb'];
		
		if(!isset($object['color_code_border']))
			$object['color_code_border'] = $this->makeBorderColor( $object['color_code_hex']);
		$options['data-color-border'] = $object['color_code_border'];
		
		return $options;
	}
	
	public function makeRGBfromHex($color = false, $opacity = '0.4')
	{
		if(!$color)
		{
			return 'rgb(255, 255, 255)';
		}
		
		$color = str_replace('#', '', $color);
		
		$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
		$rgb =  array_map('hexdec', $hex);
		
		if($opacity)
		{
			if(abs($opacity) > 1)
        		$opacity = 1.0;
			
			$output = 'rgba('.implode(",",$rgb).','.$opacity.')';
		}
		else
		{
			$output = 'rgb('.implode(",",$rgb).')';
		}
		
		return $output;
	}
	
	public function makeColorCode(Model $Model, $string = false)
	{
		
		$string = md5($string);
		$color = strtolower(substr($string, 0, 6));
		
		return '#'. $color;
	}
	
	public function makeBorderColor($color = false, $opacity = '1')
	{
		if(!$color)
		{
			return 'rgb(255, 255, 255)';
		}
		
		$color = str_replace('#', '', $color);
		
		$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
		$rgb =  array_map('hexdec', $hex);
		
		foreach($rgb as $i => $colorCode)
		{
			if($colorCode >= 50)
				$rgb[$i] = ($colorCode - 50);
		}
		
		if($opacity)
		{
			if(abs($opacity) > 1)
        		$opacity = 1.0;
			
			$output = 'rgba('.implode(",",$rgb).','.$opacity.')';
		}
		else
		{
			$output = 'rgb('.implode(",",$rgb).')';
		}
		return $output;
	}
	
	public function request($url = null, $query = [], $method = 'get', $headers = [], $curl_options = [])
	{
		$curl_options_default = [
			'followLocation' => true,
			'maxRedirs' => 5,
			'timeout' => 20,
			'connectTimeout' => 20,
			'cookieFile' => CACHE. 'dt_cookieFile',
			'cookieJar' => CACHE. 'dt_cookieJar',
			'header' => true,
			'sslVerifyPeer' => false,
			'sslVerifyHost' => 0,
			'cache' => true,
		];
		$curl_options = array_merge($curl_options_default, $curl_options);
		
		$data = false;
		$cacheKey = false;
		if(isset($curl_options['cache']))
		{
			$cacheKey = md5(serialize(['url' => $url, 'query' => $query, 'method' => $method, 'headers' => $headers, 'curl_options' => $curl_options]));
			unset($curl_options['cache']);
		}
		
		// check the cache
		if($cacheKey)
		{
			if($data = Cache::read($cacheKey, 'file'))
			{
				return $data;
			}
		}
		
		if(!$this->Curl)
		{
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
			$query_url = [];
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
			$this->Curl->url = $url;
		}
		else
		{
			$url .= $query_url;
			$this->Curl->url = $url;
		}
			
		$data = $this->Curl->execute();
		
		if($this->Curl->response_headers)
		{
			$this->curlHeaders = $this->Curl->response_headers;
		}
			
		if($this->Curl->error)
		{
		}
		else
		{
			if($cacheKey)
			{
				// cache it
				Cache::write($cacheKey, $data, 'file');
			}
		}
		
		return $data;
	}
	
	// allow to print out an indented list
	public function makeList($data = array(), $width = 72, $prefix = '', $implode = false, $sep = ':')
	{	
		$out = array();
		$max = $this->_getMaxLength($data, $prefix) + 3;
		foreach ($data as $k => $v) 
		{
			$line = Text::wrap($this->formatForList($prefix.$k.$sep, $v, $max), array(
				'width' => $width,
				'indent' => str_repeat(' ', $max),
				'indentAt' => 1
			));
			
			// incase the line was wrapped and implode is false
			$lines = explode("\n", $line);
			foreach($lines as $i => $_line)
			{
				if($prefix)
				{
					$strpos = strpos($_line, $prefix);
					if($strpos === false)
					{
						$strlen = strlen($prefix);
						$_line = substr_replace($_line, $prefix, 0, $strlen);
					}
				}
				$out[] = $_line;
			}
		}
		
		if($implode)
		{
			$impldelim = "\n";
			if(is_string($implode)) $impldelim = $implode;
			$out = implode($impldelim, $out);
		}
		return $out;
	}
	
	protected function _getMaxLength($collection = array(), $prefix = '') 
	{
		$max = 0;
		foreach ($collection as $k => $v) 
		{
			$max = (strlen($prefix.$k) > $max) ? strlen($prefix.$k) : $max;
		}
		return $max;
	}
	
	protected function formatForList($k = false, $v = false, $width = 0) 
	{
		if (strlen($k) < $width) 
		{
			$k = str_pad($k, $width, ' ');
		}
		return $k . $v;
	}
}