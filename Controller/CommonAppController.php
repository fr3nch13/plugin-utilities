<?php

App::uses('Hash', 'Core');
App::uses('Controller', 'Controller');
App::uses('CookieAuthorize', 'Utilities.Controller/Component/Auth');
class CommonAppController extends Controller
{
	// use the FO theme found in the Utilities plugin
	public $theme = 'Fo';
	
	public $components = array();
	public $common_components = array(
//		'DebugKit.Toolbar' => array('panels' => array('history' => false)),
		'RequestHandler',
		'Session',
		'Cookie',
		'Flash' => array(
			'className' => 'Utilities.FlashExt',
		),
		'Auth' => array(
			'className' => 'Utilities.AuthExt',
			'loginAction' => array('controller' => 'users', 'action' => 'login', 'admin' => false, 'manager' => false, 'reviewer' => false, 'basic' => false, 'prefix' => false, 'plugin' => false),
			'loginRedirect' => array('controller' => 'users', 'action' => 'index', 'admin' => false, 'manager' => false, 'reviewer' => false, 'basic' => false, 'prefix' => false, 'plugin' => false),
			'logoutRedirect' => array('controller' => 'users', 'action' => 'login', 'admin' => false, 'manager' => false, 'reviewer' => false, 'basic' => false, 'prefix' => false, 'plugin' => false),
			'authorize' => array('Controller'),
			'authenticate' => array(
				'Utilities.RememberMe' => array( // <!-- Extends FormAuthenticate/Form
					'fields' => array('username' => 'email', 'password' => 'password'),
					'scope' => array('User.active' => 1),
					'cookie' => array(
						'time' => '+30 minutes',
					),
				),
			),
			'authError' => 'Please login to access that location.',
		),
		'Search.Prg',
		'Search.Searchable',
		'Utilities.PaginationRecall',
		'Utilities.Foapi',
		'OAuthClient.OAuthClient',
		// Common functions we would like to have all apps available to them
		'Utilities.Common',
		'Utilities.Subscriptions',
		'Filter.Filter',
	);
	
	public $helpers = array();
	public $common_helpers = array(
		// Core Helpers
		'Session', 'Time', 
		'Html' => array('className' => 'Utilities.HtmlExt'),
		'Paginator' => array('className' => 'Utilities.PaginatorExt'),
		'Form' => array('className' => 'Utilities.FormExt' ),
		'Js' => array('Utilities.JqueryUi'),
		'Flash' => array('className' => 'Utilities.FlashExt'),
		'Tag' => array('className' => 'Tags.Tag' ),
		'Cache' => array('className' => 'Utilities.CacheExt'),
		'Utilities.Wrap',
		'Utilities.AutoComplete',
		'Avatar' => array(
			'className' => 'Upload.Avatar',
			'path' => '/files/user/photo',
			'id_field' => 'User.id',
			'photo_field' => 'User.photo',
		),
		'Utilities.Exporter',
		// Common functions we would like to have all apps available to them
		'Utilities.Common',
		'Utilities.Subscriptions',
		'Contacts' => array('className' => 'Contacts.Contacts'),
		'Usage.Usage',
		'GoogleChart' => array('className' => 'Utilities.GoogleChart' ),
	);
	
	// default pagination limit
	public $paginate = array(
       	'limit' => 25,
    );
    
	// used with 'Search.Prg' Component
	public $presetVars = array(
		array('field' => 'q', 'type' => 'value', 'encode' => true, 'trim' => true),
		array('field' => 'f', 'type' => 'checkbox', 'encode' => true),
		array('field' => 'ex', 'type' => 'value', 'encode' => false),
		array('field' => 'showall', 'type' => 'value', 'encode' => false),
		array('field' => 'getcount', 'type' => 'value', 'encode' => false),
		array('field' => 'items', 'type' => 'checkbox', 'encode' => true),
	);
	
	// switch to allow admins to delete using this admin_delete
	public $allowAdminDelete = false;
	
	public $refererRequest = false;
	
	public $formReferer = false;
	public $bypassReferer = false;
	
	public $countChecked = false;
	
	public $conditions = array();
	
	public $urlQueryKeys = array('tab', 'stat');
	
	public function __construct($request = null, $response = null)
	{
		$this->buildComponents();
		$this->buildHelpers();
		//$this->buildReferer($request);
		
		parent::__construct($request, $response);
	}
	
	public function buildComponents()
	{
		$this->components = $this->mergeSettings($this->common_components, $this->components);
	}
	
	public function buildHelpers()
	{
		
		$this->helpers = $this->mergeSettings($this->common_helpers, $this->helpers);
	}
	
	public function buildReferer($request)
	{
		if($request instanceof CakeRequest)
		{
			$referer = $request->referer();
			$referer = explode($request->base, $referer);
			$referer = $request->base. array_pop($referer);
			
			$this->refererRequest = new CakeRequest($referer);
		}
	}
	
	public function mergeSettings($defaults = array(), $new = array())
	{
		$defaults = Hash::flatten($defaults, '@@@@');
		$new = Hash::flatten($new, '@@@@');
		$combined = array_merge($defaults, $new);
		return Hash::expand($combined, '@@@@');
	}
	
	// direct methods that can be overwritten
	
	
//
	public function isAuthorized($user = array())
	{
		// group reviewers, managers, and admins can access the reviewers path
		if(isset($this->request->params['reviewer']) and $this->request->params['reviewer'])
		{
        	// if the user is an admin
			if(isset($user['role']) && in_array($user['role'], array('admin', 'manager', 'reviewer'))) 
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		
		// group managers, and admins can access the managers path
		if(isset($this->request->params['manager']) and $this->request->params['manager'])
		{
        	// if the user is an admin
			if(isset($user['role']) && in_array($user['role'], array('admin', 'manager'))) 
			{
				return true;
			}
			elseif(isset($user['manager']) and $user['manager'])
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		
		// Only admins can access the admin path
		if(isset($this->request->params['admin']) and $this->request->params['admin'])
		{
        	// if the user is an admin
			if(isset($user['role']) && $user['role'] === 'admin')
			{
				return true;
			}
			else
			{
				return false;
			}
		}
        
		// Any registered user can access normal paths
		return true; 
	}
	
	public function beforeFilter() 
	{
		$allows = array(
			'user_roles',
			'proctime',
			'versions'
		);
		
		// see if we're recaching this request
		if($this->request->query('recache'))
		{
			unset($this->request->query['recache']);
			
			// remove some other things from the query string so we get a proper cache key
			if(isset($this->request->query['tab']))
				unset($this->request->query['tab']);
			if(isset($this->request->query['key']))
				unset($this->request->query['key']);
			$allows[] = $this->request->action;
			
			$here = $this->request->here();
			$here = strtolower(Inflector::slug($here));
			clearCache($here);
		}
		
		
		// check to see if this is marked as a cached action
		if(isset($this->cacheAction[$this->request->action]))
		{
			if(isset($this->request->query['tab']))
				unset($this->request->query['tab']);
			if(isset($this->request->query['key']))
				unset($this->request->query['key']);
		}
		
		$this->Auth->allow($allows);
		
		// handle all posts here before the Controller gets ahold of it, and possibly changes it.
		if ($this->request->is('post') || $this->request->is('put'))
		{
			$this->postLog();
		}
		
		if(!isset($this->viewVars)) 
			$this->viewVars = array();
		
		// don't need any of the below for just counts
		if(isset($this->passedArgs['getcount']) and $this->passedArgs['getcount'])
		{
			return parent::beforeFilter();
		}
		
		if(isset($this->passedArgs['hash']))
		{
			$this->Session->write('Url.hash', $this->passedArgs['hash']);
		}
		elseif(isset($this->passedArgs['#']))
		{
			$this->Session->write('Url.hash', $this->passedArgs['#']);
		}
		
		// add detectors to the request object
		$this->request->addDetector(
			'iphone',
			array('env' => 'HTTP_USER_AGENT', 'pattern' => '/iPhone/i')
		);
		$this->request->addDetector(
			'windows',
			array('env' => 'HTTP_USER_AGENT', 'pattern' => '/\(Windows\s+/i')
		);
		$this->request->addDetector(
			'macs',
			array('env' => 'HTTP_USER_AGENT', 'pattern' => '/\(Macintosh;\s+/i')
		);
		
		// change the paginate, if needed
		$user = AuthComponent::user();
		if(isset($this->params['ext']) and isset($this->passedArgs['limit']))
		{
			$this->paginate['limit'] = $this->passedArgs['limit'];
			$this->paginate['maxLimit'] = $this->passedArgs['limit'];
		}
		elseif(isset($user['paginate_items']) and $user['paginate_items'])
		{
			$this->paginate['limit'] = $user['paginate_items'];
			$this->paginate['maxLimit'] = $user['paginate_items'];
		}
		
		// tells the browser to download the results, and defines the filename of the download
		if(isset($this->request->params['ext']) and in_array($this->request->params['ext'], (Configure::read('Site.export_extensions')?Configure::read('Site.export_extensions'):array()) ))
		{
			// force a download only if debug is turned off
			if(Configure::read('debug') < 2)
			{
				$filename = $this->params['controller']. '_'. $this->params['action'];
				// add the filtered text to the filename
				if(isset($this->params['named']['q']))
				{
					$filename .= '_q-'. Inflector::slug($this->params['named']['q']);
				}
				$filename .= '.'. $this->params['ext'];
				$this->RequestHandler->respondAs($this->params['ext'], array('attachment' => $filename));
			}
			else
			{
				$this->RequestHandler->respondAs($this->params['ext']);
			}
		}
		
		$this->set('search_model', $this->modelClass);
		$this->set('presetVars', $this->presetVars);
		$this->set('cookieHelper', $this->Cookie);
		$this->set('passedArgs', $this->passedArgs);
		$this->set('auth_timeout', $this->Auth->timeout());
		
		//// tracking the page we're on before a form
		if(!$this->request->is('ajax') and !$this->request->is('requested') and $this->request->is('get'))
		{
			if(!preg_match('/(add$|edit$|multiselect)/', $this->request->params['action']))
			{
				$requestParams = $this->request->params;
				
				// check the pass, if set, merge it into the normal and remove it
				if(isset($requestParams['pass']))
				{
					foreach($requestParams['pass'] as $k => $v)
					{
						$requestParams[$k] = $v;
					}
					unset($requestParams['pass']);
				}
				
				// check the pass, if set, merge it into the normal and remove it
				if(isset($requestParams['named']))
				{
					foreach($requestParams['named'] as $k => $v)
					{
						$requestParams[$k] = $v;
					}
					unset($requestParams['named']);
				}
				
				// include the passed args
				foreach($this->passedArgs as $k => $v)
				{
					$requestParams[$k] = $v;
				}
				
				// if no plugin is set, make sure it is turned off in this path
				if(!isset($requestParams['plugin'])) $requestParams['plugin'] = false;
				
				// check the prefixes incase the link to the form goes into a prefix
				if($prefixes = Configure::read('Routing.prefixes'))
				{
					foreach($prefixes as $prefix)
					{
						if(!isset($requestParams[$prefix])) $requestParams[$prefix] = false;
					}
				}
				
				$this->Session->write('formReferer',$requestParams);
			}
		}
		
		parent::beforeFilter();
	}
	
	public function beforeRender()
	{
		// don't need any of the below for just counts
		if(isset($this->passedArgs['getcount']) and $this->passedArgs['getcount'])
		{
			return parent::beforeRender();
		}
		
		// see if we have fstats from the cacher plugin
		$cacher_fstats = array();
		foreach($this->uses as $model)
		{
			if(!isset($this->{$model})) continue;
			
			if(isset($this->{$model}->recach_fstat) and !empty($this->{$model}->recach_fstat))
			{
				$cacher_fstats[$model] = $this->{$model}->recach_fstat;
			}
		}
		
		$this->set('cacher_fstats', $cacher_fstats);
		
		$this->set('passedArgs', $this->passedArgs);
		
		$this->set('searchFields', (isset($this->{$this->modelClass}->searchFields)?$this->{$this->modelClass}->searchFields:false));
		$this->set('primaryKey', (isset($this->{$this->modelClass}->primaryKey)?$this->{$this->modelClass}->primaryKey:false));
		
		if($this->request->is('ajax') and isset($this->request->params['ext']) and $this->request->params['ext'] == 'json')
		{
			$viewKeys = array_keys($this->viewVars);
			$this->set('_serialize', $viewKeys);
		}
		
		parent::beforeRender();
	}
	
	public function buildUrlQuery($url = array())
	{
	/*
	 * Support for the new tabs, and possibly other things that rely on the query string
	 */
		if(!is_array($url))
			return $url;

		foreach($this->urlQueryKeys as $queryKey)
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
	
	public function countCheck($object = null, $scope = array(), $whitelist = array(), $extra = array())
	{
		if(isset($this->passedArgs['getcount']) and $this->passedArgs['getcount'])
		{
			$this->countChecked = true;
			$object = $this->_getObject($object);
			$paginate = $this->paginate;
			$paginate['getcount'] = true;
			
			$count = 0;
			if(isset($extra['findType']))
			{
				$paginate['type'] = 'count';
				$paginate['recursive'] = -1;
				$paginate['contain'] = array();
				
				$count = $object->getCachedCounts($extra['findType'], $paginate);
			}
			else
			{
				$count = $object->getCachedCounts('count', $paginate);
			}
			if(is_array($count))
				$count = count($count);
			
			if($count === null)
				$count = 0;
			
			$this->set('count', $count);
			return $this->render('Utilities./Elements/getcount', 'ajax_nodebug');
		}
		return false;
	}
	
	protected function _getObject($object) 
	{
		if (is_string($object)) {
			$assoc = null;
			if (strpos($object, '.') !== false) {
				list($object, $assoc) = pluginSplit($object);
			}
			if ($assoc && isset($this->{$object}->{$assoc})) {
				return $this->{$object}->{$assoc};
			}
			if ($assoc && isset($this->{$this->modelClass}->{$assoc})) {
				return $this->{$this->modelClass}->{$assoc};
			}
			if (isset($this->{$object})) {
				return $this->{$object};
			}
			if (isset($this->{$this->modelClass}->{$object})) {
				return $this->{$this->modelClass}->{$object};
			}
		}
		if (empty($object) || $object === null) {
			if (isset($this->{$this->modelClass})) {
				return $this->{$this->modelClass};
			}

			$className = null;
			$name = $this->uses[0];
			if (strpos($this->uses[0], '.') !== false) {
				list($name, $className) = explode('.', $this->uses[0]);
			}
			if ($className) {
				return $this->{$className};
			}

			return $this->{$name};
		}
		return $object;
	}
	
	public function get($var = false, $key = false)
	{
		if(!$var)
			return $this->viewVars;
		
		if(!isset($this->viewVars[$var]))
			return false;
		
		if($key)
		{
			return (isset($this->viewVars[$var][$key])?$this->viewVars[$var][$key]:false);
		}
		
		return $this->viewVars[$var];
	}
	
	public function passedArg($arg = false)
	{
		if(!$arg)
			return false;
		if(!isset($this->passedArgs[$arg]))
			return false;
		return $this->passedArgs[$arg];
	}
	
	public function redirect($url, $status = null, $exit = true)
	{
		// track if we have a hash to append to the url
		$hash = false;
		if(isset($this->passedArgs['hash']))
		{
			$hash = $this->passedArgs['hash'];
		}
		elseif(isset($this->passedArgs['#']))
		{
			$hash = $this->passedArgs['#'];
		}
		elseif($this->Session->check('Url.hash'))
		{
			$hash = $this->Session->read('Url.hash');
			$this->Session->delete('Url::hash');
		}
		
		if($hash)
		{
			if(is_array($url))
			{
				$url['#'] = $hash;
			}
			elseif(is_string($url))
			{
				$url .= '#'. $hash;
			}
			$this->passedArgs['#'] = $hash;
		}
		
		if(isset($this->passedArgs['ajaxhijack']))
		{
			echo json_encode(array(
				'success' => true,
				'message' => CakeSession::read('Message.flash.message'),
			));
			exit;
		}
		
		if(!$this->request->is('ajax') and !$this->request->is('requested') and !$this->request->is('get') and !$this->bypassReferer)
		{
			// see if we're tracking a form
			if($formReferer = $this->Session->read('formReferer'))
			{
				if($hash)
					$formReferer['#'] = $hash;
				
				$url = $formReferer;
			}
		}
		
		$url = $this->buildUrlQuery($url);
		
		$response = parent::redirect($url, $status, $exit);
	}
	
	public function paginate($object = null, $scope = array(), $whitelist = array())
	{
	/*
	 * Used to see if we get an exception thrown, 
	 * If so, send them to the first page, and set the flash so they know
	 */
		$object = $this->_getObject($object);
		try
		{
			$this->paginate = array_merge(
				array(
					'conditions' => null, 'fields' => null, 'joins' => array(),
					'order' => null, 'page' => null, 'callbacks' => true, 'limit' => 0,
				),
				(array)$this->paginate
			);
			
			if(isset($this->paginate['limit']) and !$this->paginate['limit'])
				$this->paginate['empty'] = true;
			
			if(isset($this->paginate['maxLimit']) and !$this->paginate['maxLimit'])
				$this->paginate['empty'] = true;
			
			$results = array();
			if(isset($this->paginate['empty']) and $this->paginate['empty'])
			{
				/// obviously a 0
				if(isset($this->passedArgs['getcount']) and $this->passedArgs['getcount'])
				{
					$this->countChecked = true;
					$this->set('count', '0');
					return $this->render('Utilities./Elements/getcount', 'ajax_nodebug');
				}
				
				$paging = array(
					'page' => 0,
					'current' => 0,
					'count' => 0,
					'prevPage' => 0,
					'nextPage' => 0,
					'pageCount' => 0,
					'order' => false,
					'limit' => false,
				);

				if (!isset($this->request['paging'])) {
					$this->request['paging'] = array();
				}
				$this->request['paging'] = array_merge(
					(array)$this->request['paging'],
					array($object->alias => $paging)
				);
			}
			else
			{
				// we're trying to list ALL of the results with paginate
				if(isset($this->passedArgs['showall']) and $this->passedArgs['showall'])
				{
					if(isset($this->paginate['page']) and $this->paginate['page'] != 1)
					{
						return $this->redirect(array_merge($this->passedArgs, array('page' => 1, 'showall' => 1)));
					}
					elseif(isset($this->passedArgs['page']) and $this->passedArgs['page'] != 1)
					{
						return $this->redirect(array_merge($this->passedArgs, array('page' => 1, 'showall' => 1)));
					}
					$count = 0;
					if(isset($extra['findType']))
					{
						$paginate = $this->paginate;
						$paginate['type'] = 'count';
						$paginate['recursive'] = -1;
						$paginate['contain'] = array();
						$count = $object->find($extra['findType'], $paginate);
					}
					else
					{
						$count = $object->find('count', $this->paginate);
					}
					if(is_array($count))
						$count = count($count);
					
					$this->paginate['limit'] = $this->paginate['maxLimit'] = $count;
					$this->paginate['page'] = 1;
				}
						
				$results = array();
				$extra = array();
				
				// added support for something I never knew cakephp had
				// this also adds support for Utilities.FamilyBehavior, and any other cusom finders
				if(isset($this->paginate['findType']))
				{
					$extra['findType'] = $this->paginate['findType'];
				}
				
				$results = $this->countCheck($object, $scope, $whitelist, $extra);
				
				if($results === false)
				{
					$results = array();
					if($this->paginate['limit'])
						$results = $this->Components->load('Paginator', $this->paginate)->paginate($object, $scope, $whitelist);
				}
			}
		
			return $results;
		}
		catch (NotFoundException $e)
		{
			//Do something here like redirecting to first or last page.
			//$this->request->params['paging'] will give you required info.
			$page = (isset($this->passedArgs['page'])?$this->passedArgs['page']:1);
			
			$this->Flash->error(__('Unable to find results on page: %s. Redirected you to page 1.', $page));
			return $this->redirect(array('page' => 1));
		}
	}
	
	public function dashboard()
	{
	// generic placeholder for controllers that don't have a dashboard defined
		$this->set('page_title', Inflector::humanize(Inflector::underscore($this->viewPath)));
		
		$viewFilename = APP. 'View'. DS. $this->viewPath. DS. 'dashboard.ctp';
		
		$this->autoRender = false;
		if(file_exists($viewFilename))
		{
			return $this->render();
		}
		return $this->render('Utilities./Elements/page_dashboard');
	}
	
	public function my_dashboard()
	{
		$this->loadModel('DbMyblock');
		
		// check the UsageCount
		if($this->DbMyblock instanceof AppModel)
		{
			// reload the UsageCount
			App::uses('DbMyblock', 'Utilities.Model');
			$this->DbMyblock = new DbMyblock();
		}
		
		$dbMyblocks = $this->DbMyblock->find('list', array(
			'conditions' => array(
				'DbMyblock.user_id' => AuthComponent::user('id'),
				'DbMyblock.type' => array('', 'block'),
			),
			'fields' => array('DbMyblock.key', 'DbMyblock.uri'),
		));
		$this->set(compact('dbMyblocks'));
		
		$dbMytabs = $this->DbMyblock->find('list', array(
			'conditions' => array(
				'DbMyblock.user_id' => AuthComponent::user('id'),
				'DbMyblock.type' => 'tab',
			),
			'fields' => array('DbMyblock.key', 'DbMyblock.uri'),
		));
		$this->set(compact('dbMytabs'));
		
	// generic placeholder for controllers that don't have a dashboard defined
		$this->set('page_title', Inflector::humanize(Inflector::underscore($this->viewPath)));
		
		$viewFilename = APP. 'View'. DS. $this->viewPath. DS. 'my_dashboard.ctp';
		
		$this->autoRender = false;
		if(file_exists($viewFilename))
		{
			return $this->render();
		}
		return $this->render('Utilities./Main/my_dashboard');
	}
	
	public function db_myblock($key = false)
	{
		$this->autoRender = false;
		$this->loadModel('DbMyblock');
		
		if($this->DbMyblock instanceof AppModel)
		{
			App::uses('DbMyblock', 'Utilities.Model');
			$this->DbMyblock = new DbMyblock();
		}
		
		$results = $this->DbMyblock->find('first', array(
			'conditions' => array(
				'DbMyblock.user_id' => AuthComponent::user('id'),
				'DbMyblock.key' => $key,
			),
		));
		
		// setter
		if ($this->request->is('post') || $this->request->is('put'))
		{
			if(!isset($this->request->data['action']))
			{
				$results = ($results?true:false);
				echo json_encode(array('bookmarked' => $results));
				return;
			}
			
			if($this->request->data['action'] == 'add')
			{
				if($results)
				{
					$this->DbMyblock->id = $results['DbMyblock']['id'];
					$this->request->data['id'] = $this->DbMyblock->id;
				}
				else
				{
					$this->DbMyblock->create();
					$this->request->data['user_id'] = AuthComponent::user('id');
				}
				
				$results = $this->DbMyblock->save($this->request->data);
			}
			elseif($this->request->data['action'] == 'remove' and $results)
			{
				$this->DbMyblock->id = $results['DbMyblock']['id'];
				$results = $this->DbMyblock->delete();
			}
		}
		$results = ($results?true:false);
		echo json_encode(array('bookmarked' => $results));
	}
	
	public function user_roles()
	{
		$roles = Configure::read('Routing.prefixes');
		$this->set(compact('roles'));
		$this->set('_serialize', array('roles'));
	}
	
	public function download($id = false, $modelClass = false, $filename = false)
	{
	// works with the Model->manageUploads from Utilities.CommonBehavior
	// can be overwritten if needed
		if(!$id)
		{
			throw new NotFoundException(__('Invalid %s', __('ID')));
		}
		
		if(!$modelClass)
		{
			$modelClass = $this->modelClass;
		}
		
		if(!$this->{$modelClass}->Behaviors->loaded('Common'))
		{
			$this->{$modelClass}->Behaviors->load('Common');
		}
		
		if(!$this->{$modelClass}->Behaviors->enabled('Common'))
		{
			$this->{$modelClass}->Behaviors->enable('Common');
		}
		
		if(!$params = $this->{$modelClass}->downloadParams($id))
		{
			throw new NotFoundException($this->{$modelClass}->modelError);
		}
		///
		if($filename)
		{
			$ext = false;
			if(stripos($filename, '.') !== false)
			{
				$filename_parts = explode('.', $filename);
				$ext = array_pop($filename_parts);
				$filename = implode('.', $filename_parts);
			}
			$params['extension'] = ($ext?$ext:($this->params->ext?$this->params->ext:false) );
			$params['name'] = $filename;
			$params['id'] = $params['name'].'.'.$params['extension'];
		}
		
		$this->viewClass = 'Media';
		$this->set($params);
	}
	
	public function manager_download($id = false, $modelClass = false)
	{
		return $this->download($id, $modelClass);
	}
	
	public function reviewer_download($id = false, $modelClass = false)
	{
		return $this->download($id, $modelClass);
	}
	
	public function admin_download($id = false, $modelClass = false)
	{
		return $this->download($id, $modelClass);
	}
	
	// simple placeholder for searching
	public function search($action = false)
	{
		$info = $this->Searchable->getInfo(array('action' => $action));
		$this->set('info', $info);
		return $this->Searchable->render();
	}
	
	public function search_results()
	{
	}
	
	public function admin_search($action = false)
	{
		return $this->search($action);
	}
	
	public function stats()
	{
		$this->set('stats', $this->{$this->modelClass}->stats());
	}
	
	public function toggle($field = null, $id = null)
	{
		if($this->{$this->modelClass}->toggleRecord($id, $field))
		{
			$this->Flash->success(__('The %s has been updated.', Inflector::humanize(Inflector::underscore($this->modelClass))));
		}
		else
		{
			$this->Flash->error($this->{$this->modelClass}->modelError);
		}
		
		return $this->redirect($this->referer());
	}
	
	public function reviewer_toggle($field = null, $id = null)
	{
		if($this->{$this->modelClass}->toggleRecord($id, $field))
		{
			$this->Flash->success(__('The %s has been updated.', Inflector::humanize(Inflector::underscore($this->modelClass))));
		}
		else
		{
			$this->Flash->error($this->{$this->modelClass}->modelError);
		}
		
		return $this->redirect($this->referer());
	}
	
	public function admin_toggle($field = null, $id = null)
	{
		if($this->{$this->modelClass}->toggleRecord($id, $field))
		{
			$this->Flash->success(__('The %s has been updated.', Inflector::humanize(Inflector::underscore($this->modelClass))));
		}
		else
		{
			$this->Flash->error($this->{$this->modelClass}->modelError);
		}
		
		return $this->redirect($this->referer());
	}
	
	public function gridadd()
	{
		if(!$this->request->is('ajax'))
		{
			throw new InternalErrorException(__('Request in not an Ajax request.'));
		}
		
		$results = false;
		if ($this->request->is('post') || $this->request->is('put'))
		{
			if(is_object($this->{$this->modelClass}))
			{
				if(isset($this->request->data[$this->modelClass]) and is_array($this->request->data[$this->modelClass]))
				{
					$user_id = AuthComponent::user('id');
					if($user_id)
						$this->request->data[$this->modelClass]['added_user_id'] = $user_id;
				}
				
				if(!$results = $this->{$this->modelClass}->gridAdd($this->request->data))
				{
					throw new InternalErrorException(__('Failed to Add Item'));
				}
			}
		}
		
		$this->set('results', $results);
		return $this->render('Utilities./Elements/gridedit', 'ajax_nodebug');
	}
	
	public function reviewer_gridadd()
	{
		return $this->gridadd();
	}
	
	public function manager_gridadd()
	{
		return $this->gridadd();
	}
	
	public function admin_gridadd()
	{
		return $this->gridadd();
	}
	
	public function gridedit()
	{
		if(!$this->request->is('ajax'))
		{
			throw new InternalErrorException(__('Request in not an Ajax request.'));
		}
		
		$results = false;
		$initial = array();
		$diff = array();
		
		if ($this->request->is('post') || $this->request->is('put'))
		{
			if(is_object($this->{$this->modelClass}))
			{
				if(isset($this->request->data[$this->modelClass]) and is_array($this->request->data[$this->modelClass]))
				{
					$user_id = AuthComponent::user('id');
					if($user_id)
						$this->request->data[$this->modelClass]['modified_user_id'] = $user_id;
				}
				
				
				if(isset($this->request->data[$this->modelClass][$this->{$this->modelClass}->primaryKey]))
				{
					$initial = $this->{$this->modelClass}->read(null, $this->request->data[$this->modelClass][$this->{$this->modelClass}->primaryKey]);
				}
				
				if(!$results = $this->{$this->modelClass}->gridEdit($this->request->data))
				{
					throw new InternalErrorException(__('Failed to Update Item Reason: %s', $this->{$this->modelClass}->modelError));
				}
			}
		}
		
		if($results)
		{
			$initialCompare = $initial;
			
			$resultsCompare = $results;
			if(isset($resultsCompare['saveMethod']))
				unset($resultsCompare['saveMethod']);
			if(isset($resultsCompare['message']))
				unset($resultsCompare['message']);
			
			$initialCompare = Hash::flatten($initialCompare);
			ksort($initialCompare);
			$resultsCompare = Hash::flatten($resultsCompare);
			ksort($resultsCompare);
			
			$diff_old = array_diff($initialCompare, $resultsCompare);
			$diff_new = array_diff($resultsCompare, $initialCompare);
		}
		
		$resultsLog = array(
			'initial' => $initial,
			'results' => $results,
			'diff_old' => $diff_old,
			'diff_new' => $diff_new,
		);
		
		$this->grededitLog($resultsLog);
		
		$this->set('results', $results);
		return $this->render('Utilities./Elements/gridedit', 'ajax_nodebug');
	}
	
	public function grededitLog($results = array())
	{
		$info = array(
			'here' => $this->request->here,
			'method' => $this->request->method(),
			'referer' => $this->request->referer(),
			'clientIp' => $this->request->clientIp(),
			'user' => array(
				'id' => AuthComponent::user('id'),
				'email' => AuthComponent::user('email'),
				'adaccount' => AuthComponent::user('adaccount'),
				'userid' => AuthComponent::user('userid'),
				'name' => AuthComponent::user('name'),
			),
			'data' => $this->request->data,
			'query' => $this->request->query,
			'results' => $results,
		);
		
		return CakeLog::write('grid_edit', json_encode($info));
	}
	
	public function reviewer_gridedit()
	{
		return $this->gridedit();
	}
	
	public function manager_gridedit()
	{
		return $this->gridedit();
	}
	
	public function admin_gridedit()
	{
		return $this->gridedit();
	}
	
	public function griddelete()
	{
		if(!$this->request->is('ajax'))
		{
			throw new InternalErrorException(__('Request in not an Ajax request.'));
		}
		
		$results = false;
		
		if ($this->request->is('post') || $this->request->is('put'))
		{
			if(is_object($this->{$this->modelClass}))
			{
				if(!$results = $this->{$this->modelClass}->gridDelete($this->request->data))
				{
					throw new InternalErrorException(__('Failed to Delete Item'));
				}
			}
		}
		
		$this->set('results', $results);
		return $this->render('Utilities./Elements/gridedit', 'ajax_nodebug');
	}
	
	public function reviewer_griddelete()
	{
		return $this->griddelete();
	}
	
	public function manager_griddelete()
	{
		return $this->griddelete();
	}
	
	public function admin_griddelete()
	{
		return $this->griddelete();
	}
	
	// used to update an object's hostname, or ip
	// this is a local lookup, so no external ips should be looked up
	public function do_nslookup($id = false, $field = false)
	{
		$results = false;
		
		if ($this->request->is('post') || $this->request->is('put'))
		{
			if(is_object($this->{$this->modelClass}))
			{
				if(!$results = $this->{$this->modelClass}->gridDelete($this->request->data))
				{
					throw new InternalErrorException(__('Failed to Delete Item'));
				}
			}
		}
		else
		{
			if(is_object($this->{$this->modelClass}))
			{
				$results = $this->{$this->modelClass}->Common_nslookup($id, $field);
			}
		}
pr($results);
exit;
	}
	
	public function getcount($key = false, $id = false, $count_map = array())
	{
	// placeholder that will always return 0.
	// overwrite this if you want the proper return
		if(!$this->request->is('ajax'))
		{
			//throw new InternalErrorException(__('Request in not an Ajax request.'));
		}
		
		if(!$key)
		{
			throw new InternalErrorException(__('Unknown paramater %s.', 'key'));
		}
		
		if(!$id)
		{
			throw new InternalErrorException(__('Unknown paramater %s.', 'id'));
		}
		
		if(empty($count_map))
		{
			throw new InternalErrorException(__('Unknown paramater %s', 'count_map'));
		}
		
		if(empty($count_map))
		{
			throw new InternalErrorException(__('Unknown paramater %s.', 'count_map'));
		}
		
		if(!isset($count_map[$key]))
		{
			throw new InternalErrorException(__('Unknown paramater %s.', 'count_map -> key'));
		}
		
		$count_settings = $count_map[$key];
		extract($count_settings);
		
		$count = 0;
		
		if(!is_object($this->{$this->modelClass}))
		{
			App::uses($this->modelClass, 'Model');
			if(class_exists($this->modelClass)) $this->{$this->modelClass} = new $this->modelClass;
		}
		
		if(is_object($this->{$this->modelClass}))
		{
			$count = $this->{$this->modelClass}->find('count', $paramaters);
		}
		
		$this->set('count', 0);
		return $this->render('Utilities./Elements/getcount', 'ajax_nodebug');
	}
	
	public function sorted()
	{
		$data = array();
		if($this->request->is('ajax') and $this->request->is('post'))
		{
			$data = $this->request->data;
		}
	}
	
	public function apikey()
	{
		$this->loadModel('User');
		
		$this->User->id = AuthComponent::user('id');
		$this->User->recursive = 0;
		if (!$user = $this->User->read(null, $this->User->id))
		{
			throw new NotFoundException(__('Invalid %s', __('User')));
		}
		
		// auto generate one if they don't have one
		if(in_array('api_key', $user['User']) and !$user['User']['api_key'])
		{
			$this->User->Foapi_genApiKey($this->User->id);
			$user = $this->User->read(null, $this->User->id);
		}
		
		// we want to generate a new key
		if ($this->request->is('post') || $this->request->is('put'))
		{
			if($this->User->Foapi_genApiKey($this->User->id))
			{
				$this->Flash->success(__('Your API key has been regenerated.'));
			}
			else
			{
				$this->Flash->error(__('Your API key was NOT regenerated. Please, try again.'));
			}
			$this->bypassReferer = true;
			return $this->redirect(array('action' => 'edit', 'tab' => 'apikey'));
		}
		
		$this->request->data = $user;
	}
	
	public function admin_delete($id = null) 
	{
		if(!$this->allowAdminDelete)
		{
			throw new NotFoundException(__('Not allowed to use this form of deleting items.'));
		}
		
		$nice_name = Inflector::humanize(Inflector::underscore($this->{$this->modelClass}->alias));
	
		$this->{$this->modelClass}->id = $id;
		if(!$this->{$this->modelClass}->exists()) 
		{
			throw new NotFoundException(__('Invalid %s', $nice_name));
		}
		if(($this->request->is('post') || $this->request->is('put')) and !empty($this->request->data)) 
		{
			if($this->{$this->modelClass}->transferRecords($this->{$this->modelClass}->id, $this->request->data)) 
			{
				$this->{$this->modelClass}->id = $id;
				if($this->{$this->modelClass}->delete($this->{$this->modelClass}->id, false)) 
				{
					$this->Flash->success(__('%s deleted', $nice_name));
					$this->bypassReferer = true;
					return $this->redirect(array('action' => 'index'));
				}
				else
				{
					$this->Flash->error(__('The %s could not be deleted. Please, try again. (2)', $nice_name));
				}
			}
			else
			{
				$this->Flash->error(__('The %s could not be deleted. Please, try again. (1)', $nice_name));
			}
		}
		$options = $this->getTransferVariables($this->{$this->modelClass}, $id);
		
		// it doesn't have any associated models that need to be transfered
		if(!$options['associations'])
		{
			$this->{$this->modelClass}->id = $id;
			if($this->{$this->modelClass}->delete($this->{$this->modelClass}->id, false)) 
			{
				$this->Flash->success(__('%s deleted', $nice_name));
				$this->bypassReferer = true;
				return $this->redirect(array('action' => 'index'));
			}
			else
			{
				$this->Flash->error(__('The %s could not be deleted. Please, try again. (2)', $nice_name));
			}
		}
		
		$this->render('Utilities./Elements/default_admin_delete');
	}
	
	/// Config for the app
	public function admin_config()
	{
		// check that we can read/write to the config
		if(!$this->{$this->modelClass}->configCheck())
		{
			throw new InternalErrorException(__('Error with the %s file: "%s". Error: %s. Please check the permissions for writing to this file.', __('App Config'), $this->User->configPath, $this->User->configError));
		}
		
		if ($this->request->is('post') || $this->request->is('put'))
		{
			// check that we can read/write to the config
			if(!$this->{$this->modelClass}->configCheck(true))
			{
				throw new InternalErrorException(__('Error with the %s file: "%s". Error: %s. Please check the permissions for writing to this file.', __('App Config'), $this->User->configPath, $this->User->configError));
			}
			if ($this->{$this->modelClass}->configSave($this->request->data))
			{
				$this->Flash->success(__('The %s has been saved', __('App Config')));
				return $this->redirect(array('action' => 'config'));
			}
			else
			{
				$this->Flash->error(__('The %s could not be saved. Please, try again.', __('App Config')));
				return $this->redirect(array('action' => 'config'));
			}
		}
		
		$this->set('fields', $this->{$this->modelClass}->configKeys());
		
		$this->request->data = $this->{$this->modelClass}->configRead();
		
		return $this->render('Utilities./Elements/admin_app_config');
	}

	public function edit_session()
	{
	// this should only ever be called through the portal's 'users' controller
		$this->User->id = AuthComponent::user('id');
		$this->User->recursive = 0;
		if (!$user = $this->User->read(null, $this->User->id))
		{
			throw new NotFoundException(__('Invalid %s', __('User')));
		}
		$availableRoles = $this->User->availableRoles(AuthComponent::user('id'));
		
		if ($this->request->is('post') || $this->request->is('put'))
		{
			$this->bypassReferer = true;
			$chosenRole = false;
			if(!isset($this->request->data['User']['role']))
			{
				$this->Flash->error(__('Unknown %s %s - %s', __('User'), __('Role'), '1'));
				return $this->redirect(array('action' => 'edit_session'));
			}
			
			$chosenRole = $this->request->data['User']['role'];
			if(!in_array($chosenRole, array_keys($availableRoles)))
			{
				$this->Flash->error(__('Unknown %s %s - %s', __('User'), __('Role'), '2'));
				return $this->redirect(array('action' => 'edit_session'));
			}
			$this->Session->write('Auth.User.role', $chosenRole);
			
			if(isset($this->request->data['User']['debug']))
			{
				$this->Session->write('Auth.User.debug', $this->request->data['User']['debug']);
			}
			
			$this->Flash->success(__('Your settings have been changed for this session.'));
			return $this->redirect(array('action' => 'edit_session'));
		}
		
		$availableRoles = $this->User->availableRoles(AuthComponent::user('id'));
		$this->set('availableRoles', $availableRoles);
	}
	
	public function getTransferVariables(Model $Model, $id = false)
	{
		$object = $Model->read(null, $id);
		
		if($this->request->is('post') || $this->request->is('put'))
		{
		}
		else
		{
			$this->request->data = $object;
			$this->request->data[$Model->alias]['current_id'] = $id;
		}
		
		$typeFormList = $Model->typeFormList();
		// better then array_merge. this preserves the keys
		$options = array(0 => __('[ Don\'t Change ]')) + $typeFormList;
		
		// remove the current option from the list
		if(isset($options[$id])) unset($options[$id]);
		
		// 
		
		// only use the ones that have objects assigned to this one
		$associations = $Model->getAssociated();
		$allowed_associations = array('hasOne', 'hasMany', 'hasAndBelongsToMany');
		foreach($associations as $model => $association)
		{
			if(!in_array($association, $allowed_associations)) unset($associations[$model]);
		}
		$associations = array_keys($associations);
		
		$nice_name = Inflector::humanize(Inflector::underscore($this->{$this->modelClass}->alias));
		
		$out = array(
			'options' => $options,
			'associations' => $associations,
			'item_name' => $object[$Model->alias][$Model->displayField],
			'nice_name' => $nice_name,
			'model_name' => $this->modelClass,
		);
		
		$this->set($out);
		return $out;
	}
	
	public function postLog()
	{
		if (Configure::read('debug') > 0 )
		{
			// don't track the process times
			if($this->request->controller == 'proctimes')
				return true;
			
			// track the post with the session in case an error happens,
			// this is an easy way to grep the post log
			App::uses('CakeText', 'Utility');
			$postId = CakeText::uuid();
			CakeSession::write('postId', $postId);
			
			$info = array(
				'postId' => $postId,
				'here' => $this->request->here,
				'method' => $this->request->method(),
				'referer' => $this->request->referer(),
				'clientIp' => $this->request->clientIp(),
				'user' => array(
					'id' => AuthComponent::user('id'),
					'email' => AuthComponent::user('email'),
					'adaccount' => AuthComponent::user('adaccount'),
					'userid' => AuthComponent::user('userid'),
					'name' => AuthComponent::user('name'),
				),
				'data' => $this->request->data,
				'params' => $this->request->params,
				'query' => $this->request->query,
			);
			
			return CakeLog::write('post', json_encode($info));
		}
		return true;
	}
}