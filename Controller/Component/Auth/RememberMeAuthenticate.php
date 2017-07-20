<?php
App::uses('BaseAuthenticate', 'Controller/Component/Auth');
App::uses('FormAuthenticate', 'Controller/Component/Auth');
App::uses('AuthComponent', 'Controller/Component');
App::uses('Router', 'Routing');

/**
 * An authentication adapter for AuthComponent
 *
 * Provides the ability to authenticate using COOKIE
 *
 * {{{
 *	$this->Auth->authenticate = array(
 *		'Authenticate.Cookie' => array(
 *			'fields' => array(
 *				'username' => 'username',
 *				'password' => 'password'
 *	 		),
 *			'userModel' => 'User',
 *			'scope' => array('User.active' => 1),
 *			'crypt' => 'rijndael', // Defaults to rijndael(safest), optionally set to 'cipher' if required
 *			'cookie' => array(
 *				'name' => 'RememberMe',
 *				'time' => '+2 weeks',
 *			)
 *		)
 *	)
 * }}}
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class RememberMeAuthenticate extends FormAuthenticate {

/**
 * Constructor
 *
 * @param ComponentCollection $collection Components collection.
 * @param array $settings Settings
 */
	public function __construct(ComponentCollection $collection, $settings) {
		
		$request = Router::getRequest();
		$this->settings['cookie'] = array(
			'name' => 'RememberMe',
			'time' => '+2 weeks',
			'base' => ($request?$request->base:'/'),
		);
		$this->settings['crypt'] = 'cipher';
		
		// fix the settings from the app config
		$config = (Configure::read('RememberMe')?Configure::read('RememberMe'):array());
		$this->settings = Set::merge($this->settings, $config);
		
		parent::__construct($collection, $settings);
	}

/**
 * Authenticates the identity contained in the cookie.  Will use the
 * `settings.userModel`, and `settings.fields` to find COOKIE data that is used
 * to find a matching record in the `settings.userModel`. Will return false if
 * there is no cookie data, either username or password is missing, of if the
 * scope conditions have not been met.
 *
 * @param CakeRequest $request The unused request object
 * @return mixed False on login failure. An array of User data on success.
 * @throws CakeException
 */
	public function getUser(CakeRequest $request)
	{
		
		$this->setupCookieInfo();
		list(, $model) = pluginSplit($this->settings['userModel']);

		$data = $this->_Collection->Cookie->read($model);
		if(empty($data)) {
			return false;
		}

		extract($this->settings['fields']);
		if(empty($data[$username]) || empty($data[$password])) {
			return false;
		}

		$user = $this->_findUser($data[$username], $data[$password]);
		if($user) {
			// refresh the session
			$this->_Collection->Session->write(AuthComponent::$sessionKey, $user);
			// refresh the cookie
			$this->setCookie($model, $data, $this->settings['cookie']['time']);
			return $user;
		}
		return false;
	}

/**
 * Authenticate user
 *
 * @param CakeRequest $request Request object.
 * @param CakeResponse $response Response object.
 * @return array|boolean Array of user info on success, false on falure.
 */
	public function authenticate(CakeRequest $request, CakeResponse $response) {
		$user = false;
		
		// fix the settings from the app config
		$config = (Configure::read('RememberMe')?Configure::read('RememberMe'):array());
		$this->settings = Set::merge($this->settings, $config);
		
		// check for the cookie first
		if(!$user = $this->getUser($request))
		{
			if($user = parent::authenticate($request, $response))
			{
				$this->setupCookieInfo();
				list(, $model) = pluginSplit($this->settings['userModel']);
				
				$data = $request->data($model);
				// set the cookie
				extract($this->settings['fields']);
				if(empty($data[$username]) || empty($data[$password])) 
				{
					return false;
				}
				$this->setCookie($model, $data, $this->settings['cookie']['time']);
		
			}
		}
		return $user;
	}

/**
 * Called from AuthComponent::logout()
 *
 * @param array $user User record
 * @return void
 */
	public function logout($user)
	{
		parent::logout($user);
		
		$this->setupCookieInfo();
		$this->_Collection->Cookie->destroy();
	}
	
	public function setCookie($key, $value = null, $expires = null)
	{
		$this->setupCookieInfo();
		list(, $model) = pluginSplit($this->settings['userModel']);
		
		// set the actual cookie
		$this->_Collection->Cookie->write($key, $value, true, $expires);
		
		// set a cookie to track the expiration date
		$this->_Collection->Cookie->write($key. '_timeout', strtotime($expires), true, $expires);
	}
	
	public function timeout()
	{
		$this->setupCookieInfo();
		list(, $model) = pluginSplit($this->settings['userModel']);
		return (int)$this->_Collection->Cookie->read($model. '_timeout');
	}
	
	public function setupCookieInfo()
	{
		if(!isset($this->_Collection->Cookie) || !$this->_Collection->Cookie instanceof CookieComponent) {
			throw new CakeException('CookieComponent is not loaded');
		}
		
		// fix the settings from the app config
		$config = (Configure::read('RememberMe')?Configure::read('RememberMe'):array());
		$this->settings = Set::merge($this->settings, $config);
		
		$sessionName = (Configure::read('Session.cookie')?Configure::read('Session.cookie'):'');
		$this->_Collection->Cookie->name = (isset($this->settings['cookie']['name'])?$this->settings['cookie']['name']:'CakeCookie').$sessionName;
		$this->_Collection->Cookie->type($this->settings['crypt']);
	}
}
