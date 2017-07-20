<?php

class FoapiComponent extends Component 
{
	public $components = array(
		'Session',
		'Security',
		'Auth' => array(
			'authorize' => array('Controller'),
			'authenticate' => array(
				'Form' => array(
					'fields' => array('username' => 'email', 'password' => 'api_key'),
					'scope' => array('User.active' => 1),
					'passwordHasher' => 'Plain',
				),
			),
		),
	);
	
	// defaults
	public $config = array();
	
	// the Controller object
	public $Controller = null;
	
	// CakeRequest object from the Controller
	public $Request = false;
	
	// CakeResponse object from the Controller
	public $Response = false;
	
	public $ext = false;
	
	public $apikey = false;

	public function initialize(Controller $Controller) 
	{
		$this->config = Configure::read('Foapi');
		$this->Controller = & $Controller;
		$this->Request = $this->Auth->request = & $this->Controller->request;
		$this->Response = $this->Auth->response = & $this->Controller->response;
		
		// See if we are using the api by the prefix
		if(isset($this->Request->params['api']) and $this->Request->params['api'])
		{
			if(!isset($this->Request->params['ext']) or !$this->Request->params['ext'])
			{
				$this->Request->params['ext'] = 'json';
				$this->Request->url .= '.json';
				$this->Request->here .= '.json';
			}
			
			$this->Auth->allow();
			$this->Security->requireSecure();
			
			$this->ext = (isset($this->Request->params['ext'])?$this->Request->params['ext']:false);
			
			if(!$this->authenticate())
			{
				throw new ForbiddenException(__('Unable to authenticate you.'));
			}
		}
	}
	
	public function authenticate()
	{
		// they've already been authenticated
		if($this->Auth->user())
		{
			return true;
		}
		
		// get the api key from the session
		$apikey = $this->Session->read('FOAPIKEY');
		
		// get it from the query string
		if(!$apikey and isset($this->Request->query['apikey']) and $this->Request->query['apikey'])
			$apikey = $this->Request->query['apikey'];
		
		// check the post fields
		if(!$apikey and isset($this->Request->data['apikey']) and $this->Request->data['apikey'])
			$apikey = $this->Request->data['apikey'];
		
		// check the headers for X-apikey
		if($headers = getallheaders())
		{
			if(isset($headers['X-apikey']))
				$apikey = $headers['X-apikey'];
		}
		
		if(!$apikey)
		{
			throw new ForbiddenException(__('Unable to locate the API Key. (1)'));
		}

		$this->Controller->loadModel('User');
		
		$user = $this->Controller->User->find('first', array(
			'recursive' => -1,
			'conditions' => array(
				'User.api_key' => $apikey,
			),
		));
		
		if(!$user)
		{
			throw new ForbiddenException(__('Unable to unable to find your account by the api key (2) Given API KEY: %s', $apikey));
		}
		
		if(!$user['User']['active'])
		{
			throw new ForbiddenException(__('Your account is not locally active. (3)'));
		}
		
		if($this->Auth->login($user['User']))
		{
			// set the api key in the session
			$this->Session->write('FOAPIKEY', $apikey);
			$this->lastLogin($user['User']['id']);
			
			return $user['User'];
		}
		return false;
	}
	
	public function lastLogin($user_id = null)
	{
		if($user_id)
		{
			$this->Controller->loadModel('User');
			
			$this->Controller->User->id = $user_id;
			return $this->Controller->User->saveField('lastlogin_api', date('Y-m-d H:i:s'));
		}
		return false;
	}
	
	public function url($url = array()) 
	{
		if(!isset($url['ext']))
		{
			$url['ext'] = $this->ext;
		}
	
		App::uses('Router', 'Routing');
		return Router::url($url, true);
	}
	
	public function objectToArray($obj) 
	{
		$arrObj = is_object($obj) ? get_object_vars($obj) : $obj;
		$arr = '';
		if($arrObj)
		{
			foreach ($arrObj as $key => $val) 
			{
				$val = (is_array($val) || is_object($val)) ? $this->objectToArray($val) : $val;
				$arr[$key] = $val;
			}
		}
		return $arr;
	}
}