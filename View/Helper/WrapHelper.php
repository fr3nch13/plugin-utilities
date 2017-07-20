<?php

// plugins/utilities/View/Helper/WrapHelper.php
App::uses('UtilitiesAppHelper', 'Utilities.View/Helper');

/*
 * Used as a wrapper for the core helpers
 * Used to extend the core wrappers and add more functionality
 */
class WrapHelper extends UtilitiesAppHelper 
{
	public $helpers = array(
		'Html', 'Ajax', 'Time', 'Number', 'Js' => array('JqueryUi'),
		'Common' => array('className' => 'Utilities.Common'),
	);
	
	public function yesNo($toggle = 0, $raw = false)
	{
		return $this->Common->yesNo($toggle, $raw);
	}
	
	public function yesNoUnknown($toggle = 0)
	{
		return $this->Common->yesNoUnknown($toggle);
	}
	
	public function check($toggle = 0)
	{
		return $this->Common->check($toggle);
	}
	
	public function niceTime($timestamp = null, $useShort = false)
	{
		if($timestamp === false) return __('TBD');
		$timestamp = strtotime($timestamp);
		if(!$timestamp or $timestamp < 1) return __('Never');
		if($useShort)
		{
			$out = $this->Time->niceShort($timestamp);
			$out = str_replace(', 00:00', '', $out);
			return $out;
		}
		else
		{
			$out = $this->Time->nice($timestamp);
			$out = str_replace(', 00:00', '', $out);
			return $out;
		}
	}
	
	public function niceDay($timestamp = null, $naReturn = null)
	{
		if($timestamp === false) return __('TBD');
		if($naReturn === null)
			$naReturn = __('Never');
		$timestamp = strtotime($timestamp);
		if(!$timestamp or $timestamp < 1) return $naReturn;
		
		return date('M j, Y', $timestamp);
	}
	
	public function niceHour($selected = null)
	{
		if($selected === null) return ' '; // not even midnight is selected
		if($selected !== false)
			$selected = (int)$selected;
		
		$review_times = range(0, 23);
		$formatted_times = [];
		foreach($review_times as $hour)
		{
			$nice = $hour. ' am';
			if($hour > 12)
			{
				$nice = ($hour - 12). ' pm';
			}
			if($hour == 12) $nice = 'Noon';
			if($hour == 0) $nice = 'Midnight';
			$formatted_times[$hour] = $nice;
			if($selected === $hour) { return $formatted_times[$selected];}
 		}
 		
 		return $formatted_times;
	}
	
	public function niceSeconds($time = 0, $returnNice = true)
	{
		$out = array(
			'years'=>0, 
			'months'=>0, 
			'days'=>0, 
			'hours'=>0, 
			'minutes'=>0, 
			'seconds'=>0, 
			'nice'=>''
			);
		
		if($time == 0)
    	{
			if($returnNice)
			{
				return '0 seconds';
			}
			else
			{
				return $out;
			}
    	}
		
		if( $out['years'] = floatval((floor($time/31536000))) )
		{
			$out['nice'] .= $out['years']. ' year'.($out['years'] != 1?'s':''). ', '; 
			$time = $time % 86400;
		}
		
		if( $out['months'] = floatval((floor($time/2592000))) )
		{
			$out['nice'] .= ($out['nice'] != ''?', ':''). $out['months']. ' month'.($out['months'] != 1?'s':'');
			$time = $time % 86400;
		}
		
		if( $out['weeks'] = floatval((floor($time/604800))) )
		{
			$out['nice'] .= ($out['nice'] != ''?', ':''). $out['weeks']. ' week'.($out['weeks'] != 1?'s':'');
			$time = $time % 86400;
		}
		
		if( $out['days'] = floatval((floor($time/86400))) )
		{
			$out['nice'] .= ($out['nice'] != ''?', ':''). $out['days']. ' day'.($out['days'] != 1?'s':'');
			$time = $time % 86400;
		}
		
		if( $out['hours'] = floatval((floor($time/3600))) )
		{
			$out['nice'] .= ($out['nice'] != ''?', ':''). $out['hours']. ' hour'.($out['hours'] != 1?'s':'');
			$time = $time % 3600;
		}
		
		if( $out['minutes'] = floatval((floor($time/60))) )
		{
			$out['nice'] .= ($out['nice'] != ''?', ':''). $out['minutes']. ' minute'.($out['minutes'] != 1?'s':'');
			$time = $time % 60;
		}
		
		if( $out['seconds'] = floatval( $time ) )
		{
			$out['nice'] .= ($out['nice'] != ''?', ':''). $out['seconds']. ' second'.($out['seconds'] != 1?'s':'');
		}
		
		if($returnNice)
		{
			return $out['nice'];
		}
		else
		{
			return $out;
		}
	}
	
	public function niceNumber($number = 0, $options = array())
	{
		return $this->Common->niceNumber($number, $options);
	}
	
	public function formatBytes($bytes, $precision = 1)
	{
		return $this->Common->formatBytes($bytes, $precision);
	}
	
	public function toByteSize($p_sFormatted = 0) 
	{
		return $this->Common->toByteSize($p_sFormatted);
	}
	
	public function maxFileSize()
	{
		return $this->Common->maxFileSize();
	}
	
	public function fileIcon($type = false)
	{
		$base = '/img/fileicons/';
		if(!$type) $type = 'file';
		$sys_base = WWW_ROOT . ltrim(str_replace('/', DS, $base), DS);
		if(!file_exists($sys_base. $type. '.png')) $type = 'file';
		return $this->Html->image($base. $type. '.png', array('alt' => 'File type: '. $type));
	}
	
	public function descView($desc = '', $force_no_wrap = true)
	{
		$desc = trim($desc);
		$desc = ($desc?htmlentities($desc):' ');
		
		$lines = explode("\n", $desc);
		
		$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/([a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3})(\/\S*)?/";
		
		foreach($lines as $i => $line)
		{
			$url = array();
			if(preg_match($reg_exUrl, $line, $url))
			{
				$target = "_blank";
				if(isset($url[2]))
					$target = Inflector::slug($url[2]);
				
				$lines[$i] = preg_replace($reg_exUrl, '<a href="'.$url[0].'" target="'.$target.'">'.$url[0].'</a> ', $lines[$i]);
			}
			$lines[$i] = str_replace("\t", "    ", $lines[$i]);
		}
		
		$desc = implode("\n", $lines);
		
		if($force_no_wrap)
		{
//			$desc = str_replace("\t", "&nbsp; &nbsp; &nbsp;", $desc);
//			$desc = str_replace(" ", "&nbsp;", $desc);
		}
//		$desc = str_replace("\n", "<br />\n", $desc);
		
		return $this->Html->tag('pre', $desc, array('class' => 'description'));
//		return $desc;
	}
	
	public function niceWord($string = '')
	{
	/*
	 * mainly used to turn the vector type to a nice, human readable format.
	 */
		$string = strtolower($string);
		
		switch($string)
		{
			case 'ipaddress':
				$string = 'ip_address';
				break;
			case 'hostname':
				$string = 'host_name';
				break;
		}
		
		return Inflector::humanize($string);
	}
	
	public function divClear()
	{
	/*
	 * Writes a div that clears both sides
	 */
	 	return $this->Html->div('clearb', ' ');
	}
	
	public function escape($string = false)
	{
		$string = str_replace('@', '[@]', $string);
		$string = str_replace('.', '[.]', $string);
		return $string;
	}
	
	public function exportButtons($settings = array())
	{
	/*
	 * Draws an export button if exporting is active, and the export template is present for this page
	 */
		if(!Configure::read('Site.exportable')) return false;
		
		$limit = 100;
		
		if(isset($this->params['paging']) and is_array($this->params['paging']))
		{
			foreach($this->params['paging'] as $paging_settings)
			{
				if(isset($paging_settings['limit'])) 
				{
					if($limit < $paging_settings['limit'])
					{
						$limit = $paging_settings['limit'];
					}
				}
				if(isset($paging_settings['count'])) 
				{
					if($limit < $paging_settings['count'])
					{
						$limit = $paging_settings['count'];
					}
				}
			}
		}
		
		$extensions = Configure::read('Site.export_extensions');
		
		$buttons = array();
		
		$base = APP. 'View'. DS. Inflector::camelize($this->params['controller']); 
		
		foreach($extensions as $extension)
		{
			$template_file = $base.  DS. $extension. DS. $this->params['action']. '.ctp';
			if(is_file($template_file) and is_readable($template_file))
			{
				$url = $this->Html->urlModify(array('page' => 1, 'ext' => $extension, 'limit' => $limit));
				$buttons[] = $this->Html->tag('li', $this->Html->link(__('Export to %s', strtoupper($extension)), $url, array('class' => 'export_button no-icon')) );
			}
		}
		
		if(!$buttons)
			return false;
		
		$content = '';
		$link = '';
		$div_options = array('class' => 'export-options');
		$ul_options = array();
		if(count($buttons) > 2)
		{
			$linkContent = __('%s %s %s', '<i class="fa fa-floppy-o fa-fw"></i>', __('Export Options'), '<i class="fa fa-caret-down fa-fw"></i>');
			
			$link = $this->Html->link($linkContent, '#', array('escape' => false)); 
			$link = $this->Html->tag('span', $link);
			$ul_options['class'] = ' qtip-menu-list';
			$div_options['class'] .= ' qtip-menu';
		}
		else
		{
			$div_options['class'] .= ' no-menu';
		}
			
		$content = $this->Html->tag('ul', implode(' ', $buttons), $ul_options); 
		$content = $this->Html->tag('div', $link. $content, $div_options);
		
		return $content;
	}
	
	public function filterLink($display = false, $params = array())
	{
		$defaults = array(
			'controller' => $this->params['controller'],
			'action' => 'index',
			'page' => 1,
		);
		
		$params = array_merge($defaults, $params);
		
		if(isset($params['field']))
		{
			$params['f'] = $params['field'];
			unset($params['field']);
		}
		
		if(isset($params['value']))
		{
			if(!$display) $display = str_replace("\n", '</br>', $params['value']);
			$params['q'] = $params['value'];
			unset($params['value']);
		}
		
		if(!trim($display))
		{
			return '&nbsp;';
		}
		if(isset($params['escape']) and $params['escape'])
		{
			unset($params['escape']);
			$display = $this->escape($display);
		}
		
		$params = $this->Html->encodeParams($params);
		
		return $this->Html->link($display, $params);
	}
	
	public function multisearchLink($url = array(), $options = array(), $confirmMessage = false)
	{
		$url_defaults = array(
			'controller' => $this->params->controller,
			'action' => 'multisearch',
			'admin' => false,
			'?' => array('previous_action' => $this->params->action),
		);
		
		$url = array_merge($url_defaults, $url);
		
		$folder = Inflector::pluralize(Inflector::camelize($url_defaults['controller']));
		
		if(!is_file(APP. DS. 'View'. DS. $folder. DS. $url_defaults['action']. '.ctp'))
		{
			return false;
		}
		return $this->Html->link(__('Multi-search'), $url, $options, $confirmMessage);
	}
	
	/* A copy of this is in the CommonHelper. Use it instead. */
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
	
	/* A copy of this is in the CommonHelper. Use it instead. */
	public function userRole($role = false)
	{
		return Inflector::humanize($role);
	}
	
	/* A copy of this is in the CommonHelper. Use it instead. */
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
	
	// allow to print out an indented list
	public function makeList($data = array(), $width = 72)
	{
		App::uses('String', 'Utility');
		$out = array();
		$max = $this->_getMaxLength($data) + 3;
		foreach ($data as $k => $v) 
		{
			$out[] = String::wrap($this->formatForList($k.':', $v, $max), array(
				'width' => $width,
				'indent' => str_repeat(' ', $max),
				'indentAt' => 1
			));
		}
		return $out;
	}
	
	protected function _getMaxLength($collection) 
	{
		$max = 0;
		foreach ($collection as $k => $v) 
		{
			$max = (strlen($k) > $max) ? strlen($k) : $max;
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