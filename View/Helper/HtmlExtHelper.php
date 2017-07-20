<?php

App::uses('HtmlHelper', 'View/Helper');
App::uses('UtilitiesAppHelper', 'Utilities.View/Helper');

/*
 * Used as a wrapper for the html helper
 * Used to extend the html helper and add more functionality
 */
class HtmlExtHelper extends HtmlHelper 
{
	public $helpers = array(
		'Ajax', 'Time', 'Number', 'Text', 'Js' => array('JqueryUi'),
		'Wrap' => array('className' => 'Utilities.Wrap'),
		'Common' => array('className' => 'Utilities.Common'),
	);
	
	public $full_url = false;
	
	public $inline = true;
	public $include_css = false;
	
	public $as_text = false;
	
	public $space_str = ' ';
	public $space_wrap = 0;
	
	public $queryKeys = array('tab', 'stat');
	
	public function referer()
	{
		return $this->request->referer();
//		$referer = Router::parse($referer);
	}
	
	public function asText($as_text = false)
	{
		$this->as_text = $as_text;
	}
	
	public function getAsText()
	{
		return $this->as_text;
	}
	
	public function setFull($full_url = false)
	{
		$base_url = false;
		if($full_url === true or $full_url === false)
		{
			$this->full_url = $full_url;
		}
		else
		{
			$base_url = $full_url;
		}
	}
	
	public function getFull()
	{
		return $this->full_url;
	}
	
	public function setInline($inline = false)
	{
		$this->inline = $inline;
	}
	
	public function getInline()
	{
		return $this->inline;
	}
	
	public function getExt($ext = false)
	{
		$thisExt = false;
		if(isset($this->request->params['ext']))
			$thisExt = $this->request->params['ext'];
		
		if($ext === false)
			return $thisExt;
		
		if(!is_array($ext))
			$ext = [$ext];
			
		foreach($ext as $_ext)
		{
			if($_ext == $thisExt)
				return $_ext;
		}
		return false;
	}
	
	public function link($title, $url = null, $options = array(), $confirmMessage = false)
	{
		$fullUrl = $this->getFull();
		$asText = $this->getAsText();
		if(isset($options['asText']) and $options['asText'])
		{
			$fullUrl = true;
			$asText = true;
			unset($options['asText']);
		}
		if($this->getExt(['txt', 'sub']))
		{
			$fullUrl = true;
		}
		if(isset($options['filter_field']) and is_array($url))
		{
			$url['f'] = $options['filter_field'];
			unset($options['filter_field']);
			
			$url['q'] = $title;
			if(isset($options['filter_value']))
			{
				$url['q'] = $options['filter_value'];
				unset($options['filter_value']);
			}
		}
		if(is_array($url))
		{
			$url = $this->fixUrl($url, $fullUrl);
		}
		elseif(is_string($url) and trim($url) != '#')
		{
			// see if it's an internal link
			if(!preg_match('/^https?:\/\//i', $url))
			{
				$url = preg_replace('!^'.preg_quote($this->request->base).'!i', '', $url);
				$url = $this->url($url, true);
			}
		}
		
		if(isset($options['rel']) and is_array($options['rel']))
		{
			$options['rel'] = $this->url($options['rel']);
		}
		if($asText)
		{
			return __('%s : %s', $title, $this->url($url));
		}
		
		if(!$title)
		{
			return false;
		}
		return parent::link($title, $url, $options, $confirmMessage);
	}
	
	public function fixUrl($url = array(), $fullUrl = false)
	{
		// usefull for html emails
		if($fullUrl and !isset($url['full_base']))
		{
			$this->setFull(true);
			$url['full_base'] = true;
		}
		if($this->getFull())
			$url['full_base'] = true;
		
		// wipe out all prefixes if needed
		if(array_key_exists('prefix', $url))
		{
			$prefixes = Configure::read('Routing.prefixes');
			if(!$url['prefix'])
			{
				foreach($prefixes as $prefix)
					$url[$prefix] = false;
			}
			else
			{
				foreach($prefixes as $prefix)
				{
					if($url['prefix'] == $prefix)
						$url[$prefix] = true;
					else
						$url[$prefix] = false;
				}
				
			}
			unset($url['prefix']);
		}
			
		$url = $this->buildQuery($url);
		return $url;
	}
	
	public function buildQuery($url = array())
	{
	/*
	 * Support for the new tabs, and possibly other things that rely on the query string
	 */
		if(!is_array($url))
			return $url;

		foreach($this->queryKeys as $queryKey)
		{
			if(isset($url[$queryKey]) and is_string($url[$queryKey]))
			{
				if(!isset($url['?']))
					$url['?'] = array();
				if(!isset($url['?'][$queryKey]))
					$url['?'][$queryKey] = $queryKey.'-'. $url[$queryKey];
				unset($url[$queryKey]);
			}
		}
		
		return $url;
	}
	
	public function url($url = null, $full = false)
	{
		if($this->full_url)
		{
			$full = true;
		}
		if(is_array($url))
		{
			$url = $this->fixUrl($url);
		}
			
		return parent::url($url, $full);
	}
	
	public function urlModify($newParams = array())
	{
		$current = $this->urlBase();
		
		if(is_array($this->_View->passedArgs))
		{
			foreach($this->_View->passedArgs as $k => $v)
			{
				$current[$k] = $v;
			}
		}
		
		if(is_array($newParams))
		{
			foreach($newParams as $i => $newParam)
			{
				$current[$i] = $newParam;
			}
		}
		
		$current = $this->encodeParams($current);
		
		$current = $this->fixUrl($current);
		
		return $current;
	}
	
	public function urlHere($compile = false)
	{
		$url = $this->urlModify();
		if($compile)
			$url = $this->url($url);
		return $url;
	}
	
	public function urlBase($compiled = false)
	{
	/*
	 * Creates the base url for the page we're on
	 * parts can be overwritten with the url argument
	 */
		
		$current = array(
			'plugin' => $this->request->plugin,
			'controller' => $this->params->controller,
			'action' => $this->params->action,
		);
		
		if(is_array($this->request->pass))
		{
			foreach($this->request->pass as $k => $v)
			{
				$current[$k] = $v;
			}
		}
		if($compiled)
		{
			return $this->url($current);
		}
		return $current;
	}
	
	public function modifyLink($display = false, $url = null, $options = array(), $confirmMessage = false)
	{
		$display = trim($display);
		
		return $this->link($display, $this->urlModify($url), $options, $confirmMessage);
	}
	
	
	public function permaLink()
	{
	// provides the link to the current page including with the search fields and pagination settings from the session
		return $this->url($this->urlModify(array('full_base' => true)));
	}
	
	public function roleLink($role = false, $viewPath = false)
	{
	/*
	 * Creates a link to the role that the user has, if they have one, and if the view for that role exists
	 */
		if(!$role) return false;
		if(!$viewPath) return false;
		
		if($this->request->is('ajax'))
		{
			return false;
		}
		
		$viewPath = explode(DS, $viewPath);
		$viewPathName = array_pop($viewPath);
		
		$urlModify = array();
		$roleTitle = false;
		
		// we're viewing the role
		if(preg_match('/^'.$role.'_/', $viewPathName))
		{
			$viewPathName = preg_replace('/^'.$role.'_/', '', $viewPathName);
			$roleTitle = __('Regular');
			
			$action = explode('.', $viewPathName);
			$action = array_shift($action);
			$urlModify = array($role => false, 'action' => $action );
		}
		// we're viewing the regular page
		else
		{
			$viewPathName = $role. '_'. $viewPathName;
			$roleTitle = Inflector::humanize(strtolower($role));
			$urlModify = array($role => true);
		}
		
		array_push($viewPath, $viewPathName);
		$viewPath = implode(DS, $viewPath);
		if(is_readable($viewPath))
		{
			$linkTitle = __('View this as %s', $roleTitle);
			return $this->link($linkTitle, $this->urlModify($urlModify));
		}
	}
	
	public function filterUrl($url = false)
	{
		$url_defaults = $this->urlModify(array('page' => 1));
		
		$params = $url_defaults;
		
		if(is_array($url))
		{
			$params = array_merge($params, $url);
		}
		
		// clear some things if we're not staying in the same controller
		if($params['controller'] != $url_defaults['controller'])
		{
			if(isset($params['sort']))
				unset($params['sort']);
			if(isset($params['direction']))
				unset($params['direction']);
		}
		
		if(isset($params['field']))
		{
			$params['f'] = $params['field'];
			unset($params['field']);
		}
		
		if(isset($params['value']))
		{
			$params['q'] = $params['value'];
			unset($params['value']);
		}
		
		if(isset($params['escape']) and $params['escape'])
		{
			unset($params['escape']);
			$display = $this->escape($display);
		}
		
		$params = $this->encodeParams($params);
		return $params;
	}
	
	public function filterLink($display = false, $url = null, $options = array(), $confirmMessage = false)
	{
		$display = trim($display);
		
		return $this->link($display, $this->filterUrl($url), $options, $confirmMessage);
	}
	
	public function userLink($userid = false)
	{
		if(!$userid)
			return false;
		
		return $this->link($userid, 'http://user.example.com?id='. $userid, array('class' => 'userlink', 'target' => 'USER'));
	}
	
	public function toggleLink($object = false, $field = false, $action = 'toggle', $prefix = false)
	{
		if(!$object)
			return false;
		if(!$field)
			return false;
		if(!isset($object[$field]))
			return false;
		
		$linkClasses = ['link-toggle' => 'link-toggle'];
		
		$fa_class = 'fa-toggle-off';
		$linkClasses['toggle-sate'] = 'toggle-off';
		$title = __('No');
		if($object[$field])
		{
			$fa_class = 'fa-toggle-on';
			$linkClasses['toggle-sate'] = 'toggle-on';
			$title = __('Yes');
		}
		
		if($this->getExt('sub'))
		{
			$icon = $title;
			$this->setFull(true);
		}
		else
		{
			$icon = $this->tag('i', '', array('aria-hidden' => true, 'class' => 'fa fa-lg '. $fa_class));
		}
			
		$title = __('%s - Click to toggle', $title);
		
		$url = array('action' => $action, $field, $object['id']);
		if($prefix)
		{
			$url['prefix'] = $prefix;
			$url[$prefix] = true;
		}
		
		if($this->_View->get('isSubscription'))
			$this->setFull(true);
		
		return $this->link($icon, $url, array('data-confirm' => true, 'class' => $linkClasses, 'escape' => false, 'title' => $title));
	}
	
	public function defaultLink($object = false, $field = false, $prefix = false)
	{
		return $this->toggleLink($object, $field, 'setdefault', $prefix);
	}
	
	public function nlsookupLink($object = false, $field = false, $options = array())
	{
	// works with Utilities.CommonAppController:do_nslookup()
	// Utilities.CommonBehabior::Common_nslookup()
		if(!$object)
			return false;
		if(!$field)
			return false;
		if(!isset($object[$field]))
			return false;
		if(!isset($object['id']))
			return false;
		if(!$object['id'])
			return false;
		
		$defaults = array(
			'url' => $this->urlModify(array('action' => 'view', 0 => $object['id'])),
		);
		
		$url = false;
		$options = array_merge($defaults, $options);
		
		if(!isset($options['class']))
			$options['class'] = array();
		
		if($options['url'])
		{
			$url = $options['url'];
			unset($options['url']);
		}
		
		$options['class']['link-nslookup'] = 'link-nslookup';
		
		if($object[$field])
		{
			$options['class']['link-nslookup-off'] = 'link-nslookup-off';
			return $this->link($object[$field], $url, $options);
		}
// disabled for now
		return false;
		
		$options['class']['link-nslookup-on'] = 'link-nslookup-on';
		$url = $this->urlModify(array('action' => 'do_nslookup', 0 => $object['id'], $field));
		return $this->link('[lookup]', $url, $options);
	}
	
	public function confirmLink($title = false, $uri = [], $options = [])
	{
		if(!isset($options['confirm']))
			$options['confirm'] = __('Are you sure?');
		return $this->link($title, $uri, $options);
	}
	
	public function setIncludeCss($include = false)
	{
		$this->include_css = $include;
	}
	
	public function getIncludeCss()
	{
		return $this->include_css;
	}
	
	public function css($path, $options = [])
	{
		if($this->getIncludeCss())
		{
			$url = $this->assetUrl($path, $options + ['pathPrefix' => Configure::read('App.cssBaseUrl'), 'ext' => '.css']);
			$url = str_replace(Router::url('/'), '', $url);
			$url = trim($url, '/');
			$url = Router::url('/', true). $url;
			
			if($content = $this->Common->request($url))
			{
				$content = "/* origin: $url */\n". $content;
				$content = $this->tag('style', $content, ['type' => 'text/css']);
			}
			return $content;
		}
		
		if($this->getFull())
		{
			$options['fullBase'] = true;
		}
		if(!isset($options['inline']))
		{
			$options['inline'] = $this->getInline();
		}
		
		if($result = parent::css($path, $options))
		{
			return "\n". $result;
		}
		return false;
	}
	
	public function tableDesc($text, $options = array())
	{
		if(!isset($options['limit']))
			$options['limit'] = 100;
		
		return array($text, array('class' => 'textarea', 'value' => $text, 'data-limit' => $options['limit']));
	}
	
	public function divClear($options = array())
	{
	 	return $this->div('clearb', ' ', $options);
	}
	
	public function clearb($options = array()) // alias for divClear
	{
		return $this->divClear($options);
	}
	
	public function escape($string = false)
	{
		$string = str_replace('@', '[@]', $string);
		$string = str_replace('.', '[.]', $string);
		return $string;
	}
	
	public function setSpaceWrap($strings = array(), $buffer = 4)
	{
		$this->space_wrap = $buffer;
		$highest_len = 0;
		foreach($strings as $string)
		{
			$strlen = strlen($string);
			if($strlen > $highest_len) $highest_len = $strlen;
		}
		$this->space_wrap = ($highest_len + $buffer); 
	}
	
	public function spaceWrap($string = false, $space_str = false)
	{
		$space_str = ($space_str?$space_str:$this->space_str);
		$space_wrap = $this->space_wrap;
		$strlen = strlen($string);
		$repeat_times = ($space_wrap - $strlen);
		return $string. str_repeat($space_str, $repeat_times);
	}
	
	public function encodeParams($params = array())
	{
		if(!$params)
			return $params;
		
		// set in the Utilities.CommonAppController::beforeFilter();
		$presetVars = ($this->_View->get('presetVars')?$this->_View->get('presetVars'):array());
		
		if(!$presetVars)
			return $params;
		
		if(!is_array($presetVars))
			return $params;
		
		// we only want to know which fields we need to encode
		foreach($presetVars as $k => $varSettings)
		{
			if(!isset($varSettings['field']))
				continue;
			if(!$varSettings['field'])
				continue;
			if(!isset($varSettings['encode']))
				continue;
			if(!$varSettings['encode'])
				continue;
			$field = $varSettings['field'];
			
			if(isset($params[$field]))
				$params[$field] = $this->encodeData($params[$field]);
		}
		return $params;
	}
	
	public function decodeParams($params = array())
	{
		if(!$params)
			return $params;
		
		// set in the Utilities.CommonAppController::beforeFilter();
		$presetVars = ($this->_View->get('presetVars')?$this->_View->get('presetVars'):array());
		
		if(!$presetVars)
			return $params;
		
		if(!is_array($presetVars))
			return $params;
		
		// we only want to know which fields we need to encode
		foreach($presetVars as $k => $varSettings)
		{
			if(!isset($varSettings['field']))
				continue;
			if(!$varSettings['field'])
				continue;
			if(!isset($varSettings['encode']))
				continue;
			if(!$varSettings['encode'])
				continue;
			$field = $varSettings['field'];
			
			if(isset($params[$field]))
				$params[$field] = $this->decodeData($params[$field]);
		}
		return $params;
	}
	
	/* these are used to encode certian fields in urls */
	
	public function encodeData($string = false)
	{
		if(is_string($string))
			$string = trim($string);
		$string = base64_encode($string);
		$string = str_replace(array('+', '/', '='), array('-', '_', '^'), $string);
		return $string;
	}
	
	public function decodeData($string = false)
	{
		if(is_string($string))
			$string = trim($string);
		$string = str_replace(array('-', '_', '^'), array('+', '/', '='), $string);
		
		return base64_decode($string);
	}

/**
 * Returns a formatted string of table rows (TR's with TD's in them).
 *
 * @param array $data Array of table data
 * @param array $oddTrOptions HTML options for odd TR elements if true useCount is used
 * @param array $evenTrOptions HTML options for even TR elements
 * @param bool $useCount adds class "column-$i"
 * @param bool $continueOddEven If false, will use a non-static $count variable,
 *    so that the odd/even count is reset to zero just for that call.
 * @return string Formatted HTML
 * @link http://book.cakephp.org/2.0/en/core-libraries/helpers/html.html#HtmlHelper::tableCells
 */
	public function tableCells($data, $oddTrOptions = null, $evenTrOptions = null, $useCount = false, $continueOddEven = true) {
/*
 * Overriding the core HtmlHelper::tableCells() because it doesn't allow named indexes
 *
		if (empty($data[0]) || !is_array($data[0])) {
			$data = array($data);
		}
*/
		if ($oddTrOptions === true) {
			$useCount = true;
			$oddTrOptions = null;
		}

		if ($evenTrOptions === false) {
			$continueOddEven = false;
			$evenTrOptions = null;
		}

		if ($continueOddEven) {
			static $count = 0;
		} else {
			$count = 0;
		}

		foreach ($data as $line) {
			$count++;
			$cellsOut = array();
			$i = 0;
			foreach ($line as $cell) {
				$cellOptions = array();

				if (is_array($cell)) {
					$cellOptions = $cell[1];
					$cell = $cell[0];
				}

				if ($useCount) {
					if (isset($cellOptions['class'])) {
						$cellOptions['class'] .= ' column-' . ++$i;
					} else {
						$cellOptions['class'] = 'column-' . ++$i;
					}
				}

				$cellsOut[] = sprintf($this->_tags['tablecell'], $this->_parseAttributes($cellOptions), $cell);
			}
			$options = $this->_parseAttributes($count % 2 ? $oddTrOptions : $evenTrOptions);
			$out[] = sprintf($this->_tags['tablerow'], $options, implode(' ', $cellsOut));
		}
		return implode("\n", $out);
	}
}