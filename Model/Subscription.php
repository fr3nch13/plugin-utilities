<?php

App::uses('UtilitiesAppModel', 'Utilities.Model');

class Subscription extends UtilitiesAppModel 
{
	public $belongsTo = [
		'User' => [
			'className' => 'User',
			'foreignKey' => 'user_id',
		],
	];
	
	public $actsAs = array(
		'Utilities.HttpRequest',
		'Utilities.Email',
	);
	
	// define the fields that can be searched
	public $searchFields = array(
		'Subscription.name',
		'Subscription.uri',
	);
	
	public $toggleFields = ['active', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
	
	public function beforeSave($options = [])
	{
		if(isset($this->data[$this->alias]['uri']))
		{
			$this->data[$this->alias]['uri'] = $this->fixUri($this->data[$this->alias]['uri']);
		}
		
		return parent::beforeSave($options);
	}
	
	public function getRecord($user_id = false, $uri = false)
	{
		if(!$user_id)
		{
			$this->modelError = __('Unknown User ID');
			return false;
		}
		if(!$uri)
		{
			$this->modelError = __('Unknown URI');
			return false;
		}
		$subscription = $this->find('first', [
			'conditions' => [
				$this->alias.'.user_id' => $user_id,
				$this->alias.'.uri' => $uri,
			],
		]);
		return $subscription;
	}
	
	public function isSubscribed($user_id = false, $uri = false)
	{
	/*
		0 = not subscribed, 1 = subscribed and active, 2 = subscribed and inactive
	*/
		if(!$user_id)
		{
			$this->modelError = __('Unknown User ID');
			return false;
		}
		if(!$uri)
		{
			$this->modelError = __('Unknown URI');
			return false;
		}
		$subscription = $this->getRecord($user_id, $uri);
		if(!$subscription)
		{
			return 0;
		}
		if($subscription[$this->alias]['active'])
			return 1;
		else
			return 2;
	}
	
	public function sendEmails()
	{
		// get a list of subscriptions that are active for this day and this hour
		// make sure the user is active
		$hour = date('H');
		$day = strtolower(date('D'));
		
		$this->shellOut(__('Checking for available %s for %s/%s.', __('Subscriptions'), $day, $hour), 'subscription');
		
		$subscriptions = $this->find('all', [
			'contain' => ['User'],
			'conditions' => [
				'User.active' => true,
				$this->alias.'.active' => true,
				$this->alias.'.'.$day => true,
				$this->alias.'.email_time' => $hour,
			],
		]);
		
		if(!$subscriptions)
		{
			$this->shellOut(__('No %s available to send for %s/%s.', __('Subscriptions'), $day, $hour), 'subscription');
			return true;
		}
		
		$subCount = count($subscriptions);
		$this->shellOut(__('Found %s %s to send for %s/%s.', $subCount, __('Subscriptions'), $day, $hour), 'subscription');
		
		$i = 0;
		foreach($subscriptions as $subscription)
		{
			$i++;
			// get the html of the page from the uri
			$uri = '/'.$subscription['Subscription']['uri'].'.sub';
			$uri = Router::url($uri, true);
			
			$this->shellOut(__('Found %s "%s" for %s for %s/%s.', __('Subscription'), $subscription['Subscription']['name'], $subscription['User']['name'], $day, $hour), 'subscription');
			
			// get the content of the uri
			$this->HTTP_reset();
			$this->HTTP_setRequestHeader('FO.OAuth.clientId', Configure::read('OAuth.clientId'));
			$this->HTTP_setRequestHeader('FO.OAuth.clientSecret', Configure::read('OAuth.clientSecret'));
			$this->HTTP_setRequestHeader('FO.User.id', $subscription['Subscription']['user_id']);
			$this->HTTP_setUri($uri);
			$this->shellOut(__('(%s/%s) Getting content %s "%s" for %s for %s/%s - URI: %s.', $i, $subCount, __('Subscription'), $subscription['Subscription']['name'], $subscription['User']['name'], $day, $hour, $uri), 'subscription');
			if($content = $this->HTTP_execute())
			{
				$this->shellOut(__('(%s/%s) Received content %s "%s" for %s for %s/%s - URI: %s.', $i, $subCount, __('Subscription'), $subscription['Subscription']['name'], $subscription['User']['name'], $day, $hour, $uri), 'subscription');
				
				$this->Email_reset();
				$this->Email_set('emailFormat', 'html');
				$this->Email_set('to', $subscription['User']['email']);
				$this->Email_set('from', 'example@example.com');
				$this->Email_set('template', 'Utilities.subscription');
				$this->Email_set('layout', 'Utilities.ajax');
				$this->Email_set('subject', __('Subscription: %s', $subscription['Subscription']['name']));
				$this->Email_set('viewVars', ['content' => $content]);

				if($results = $this->Email_executeFull())
				{
					$this->shellOut(__('(%s/%s) Sent email for %s "%s" for %s for %s/%s.', $i, $subCount, __('Subscription'), $subscription['Subscription']['name'], $subscription['User']['name'], $day, $hour), 'subscription');
				}
				else
				{
					$this->shellOut(__('(%s/%s) Email failed for %s "%s" for %s for %s/%s.', $i, $subCount, __('Subscription'), $subscription['Subscription']['name'], $subscription['User']['name'], $day, $hour), 'subscription', 'error');
				}
			}
			else
			{
				$err = $this->HTTP_getError();
				$this->shellOut(__('(%s/%s) Error getting content %s "%s" for %s for %s/%s - Error: (%s) %s - URI: %s.', $i, $subCount, __('Subscription'), $subscription['Subscription']['name'], $subscription['User']['name'], $day, $hour, $err['errno'], $err['msg'], $uri), 'subscription', 'error');
			}
		}
	}
	
	public function fixUri($uri = false)
	{
		$uri = str_replace(Router::url('/', true), '', $uri);
		$uri = preg_replace('/\.sub$/', '', $uri);
		return $uri;
	}
}