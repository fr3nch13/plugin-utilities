<?php
/*
 * Pagination Recall CakePHP Component
 * Copyright (c) 2008 Matt Curry
 * www.PseudoCoder.com
 *
 * @author		mattc <matt@pseudocoder.com>
 * @version		2.0
 * @license		MIT
 * Changed to cakephp 2.x by 
 *
 */

class PaginationRecallComponent extends Component 
{
	public $components = array('Session');
	public $Controller = null;

	public function initialize(Controller $controller) 
	{
		$this->Controller = & $controller;

		$options = array_merge($this->Controller->request->params,
									$this->Controller->params['url'],
									$this->Controller->passedArgs
								  );
		$vars = array('page', 'sort', 'direction', 'limit', 'q', 'f', 'ex', 'p', 'showall');
		$keys = array_keys($options);
		$count = count($keys);

		for ($i = 0; $i < $count; $i++) {
			if (!in_array($keys[$i], $vars) || !is_string($keys[$i])) {
			  unset($options[$keys[$i]]);
			}
		}	
		// catch if we are listing objects specific to another object (hasMany, HABTM)
		$pass = array();
		if(isset($this->Controller->passedArgs) and $this->Controller->passedArgs)
		{
			$pass = $this->Controller->passedArgs;
			// removed the pagination vars for a proper md5 key
			$_vars = array_flip($vars);
			foreach($pass as $k => $v)
			{
				if(isset($_vars[$k])) unset($pass[$k]);
			}
			// for proper md5 key
			ksort($pass);
		}
		
		$md5 = md5(serialize($pass));
		
		$session_key = $this->RecallSessionKey($md5);
//		$session_key = "Pagination.{$this->Controller->name}.{$this->Controller->action}.{$this->Controller->modelClass}.".$md5.".options";
		$this->Controller->pagination_key = $session_key;
		
		//save the options into the session
		if ($options) {
			if ($this->Session->check($session_key)) {
				$options = array_merge($this->Session->read($session_key), $options);
			}
	  
			$this->Session->write($session_key, $options);
		}
		
		$this->getPaginationOptions($md5);
	}
	
	public function getPaginationOptions($md5 = '')
	{
		$session_key = $this->RecallSessionKey($md5);
		
		// recall previous options
		if ($this->Session->check($session_key)) {
			$options = $this->Session->read($session_key);
			$this->Controller->passedArgs = array_merge($this->Controller->passedArgs, $options);
			$this->Controller->request->params['named'] = $options;
		}
	}
	
	public function RecallSessionKey($md5 = '')
	{
		$session_key =  "Pagination.{$this->Controller->name}.{$this->Controller->action}.{$this->Controller->modelClass}.".$md5.".options";
		return $session_key;
	}
}