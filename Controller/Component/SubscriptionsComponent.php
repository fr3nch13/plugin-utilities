<?php

class SubscriptionsComponent extends Component 
{
	public $components = ['Session', 'Auth'];
	public $Controller = null;
	
	public $User = null;

	public function initialize(Controller $Controller) 
	{
		$this->Controller = & $Controller;
		
		$Controller->set('isSubscription', false);
		
		// if it's a post, the extension "sub", and the oauth id/passphrase and user id are set, it's a subscription request
		if($Controller->request->param('ext') == 'sub')
		{
			$userId = $this->Auth->user('id');
			
			if(!$userId)
			{
				if(!$this->Auth->validateApi())
				{
					throw new ForbiddenException(__('Invalid API credentials.'));
				}
				if(!$userId = $this->Auth->getApiUserId())
				{
					throw new ForbiddenException(__('Invalid API User ID.'));
				}
				$this->User = ClassRegistry::init('User');
				
				// find the user
				if(!$user = $this->User->read(null, $userId))
				{
					throw new ForbiddenException(__('Unknown User.'));
				}
			
				// spoof the user as logged in just for this request
				$this->Auth->login($user['User']);
			}
			
			// let the helpers and the views know we're a subscription request
			$Controller->set('isSubscription', true);
			
			// we'll use the default Email html layout
			$Controller->layout = 'Utilities.Emails/html/default';
			
		}
		return parent::initialize($Controller);
	}
	
	public function beforeRender(Controller $Controller)
	{
		if(isset($Controller->subscriptions) and in_array($Controller->action, $Controller->subscriptions))
		{
			$Controller->set('subscribable', true);
		}
		return parent::beforeRender($Controller);
	}
	
	public function fixUri($uri = false)
	{
		$uri = str_replace(Router::url('/', true), '', $uri);
		return $uri;
	}
	
	public function getAuthHeaders()
	{
	}
}
