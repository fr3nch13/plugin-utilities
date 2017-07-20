<?php
App::uses('AuthComponent', 'Controller/Component');


/*
 * Extends the Auth Component and includes things like the auth timeout
 *
 */

class AuthExtComponent extends AuthComponent
{
	public $Controller = null;

	public function initialize(Controller $Controller) 
	{
		$this->Controller = & $Controller;
	}
	
	public function setFlash($message, $element = 'default', $params = array(), $key = 'flash')
	{
		return parent::setFlash($message, $element, $params, $key);
	}
	
	public function timeout($shortest = false)
	{
		if (empty($this->_authenticateObjects))
		{
			$this->constructAuthenticate();
		}
		
		$results = array();
		
		foreach ($this->_authenticateObjects as $auth)
		{
			if(!method_exists($auth, 'timeout')) continue;
			
			$result = $auth->timeout();
			if (!empty($result) && is_int($result))
			{
				$results[$result] = $result;
			}
		}
		if(!empty($results))
		{
			if($shortest)
			{
				arsort($results);
			}
			else
			{
				asort($results);
			}
			return array_pop($results);
		}
		return false;
	}
	
	public function logout()
	{
		$redirect = parent::logout();
		
		if($this->Controller->Components->enabled('OAuthClient.OAuthClient') and 
			isset($this->Controller->OAuthClient->settings['serverLogout']) and 
			$this->Controller->OAuthClient->settings['serverLogout'] and 
			$this->Controller->{$this->Controller->modelClass}->Behaviors->loaded('OAuthClient'))
		{
			$redirect = $this->Controller->{$this->Controller->modelClass}->OAC_getLogoutUrl();
		}
		
		$query = $this->Controller->request->query;
		if($query)
		{
			$redirect = Router::parse($redirect);
			$redirect['?'] = $query;
		}
		
		return $redirect;
	}
	
	public function getApiHeaders()
	{
		$headers = apache_request_headers();
		
		$out = [];
		foreach($headers as $k => $v)
		{
			if(substr($k, 0, 3) !== 'FO.')
				continue;
			$k = preg_replace('/^FO\./', '', $k);
			$out[$k] = $v;
		}
		return $out;
	}
	
	public function validateApi()
	{
		$apiHeaders = $this->getApiHeaders();
		
		if(!isset($apiHeaders['OAuth.clientId']))
			return false;
		if(!isset($apiHeaders['OAuth.clientSecret']))
			return false;
		
		$oauthConfig = Configure::read('OAuth');
		if($apiHeaders['OAuth.clientId'] == $oauthConfig['clientId'] and $apiHeaders['OAuth.clientSecret'] == $oauthConfig['clientSecret'])
			return true;
		
		return false;
	}
	
	public function getApiUserId()
	{
		$apiHeaders = $this->getApiHeaders();
		if(isset($apiHeaders['User.id']))
			return $apiHeaders['User.id'];
		return false;
	}
}