<?php

App::uses('UtilitiesAppController', 'Utilities.Controller');

class SubscriptionsController extends UtilitiesAppController 
{
	public $components = [
		'Subscriptions' => [
			'className' => 'Utilities.Subscriptions',
		],
	];
	
	public $subscriptions = [];
	
	public function index()
	{
		$this->Prg->commonProcess();
		
		$conditions = [
			'Subscription.user_id' => AuthComponent::user('id'),
		];
		
		$this->paginate['conditions'] = $this->Subscription->conditions($conditions, $this->passedArgs);
		$subscriptions = $this->paginate();
		$this->set(compact('subscriptions'));
	}
	
	public function check()
	{
		if(!$this->request->is('ajax'))
		{
			throw new ForbiddenException(__('Request in not an Ajax request.'));
		}
		if (!$this->request->is('post')) 
		{
			throw new ForbiddenException(__('Request must be a POST.'));
		}
		if(!isset($this->request->data['subscribeurl']))
		{
			throw new NotFoundException(__('Subscribe URI is not set.'));
		}
		
		$message = false;
		$success = false;
		$uri = $this->Subscriptions->fixUri($this->request->data['subscribeurl']);
		
		$user_id = AuthComponent::user('id');
		
		$subscribed = $this->Subscription->isSubscribed($user_id, $uri);
		
		if($subscribed === false)
		{
			$message = $this->Subscription->modelError;
		}
		else
		{
			$success = true;
		}
		
		$this->set([
			'success' => $success,
			'message' => $message,
			'subscribed' => $subscribed,
			'user_id' => $user_id,
			'uri' => $uri,
			'redirect' => false,
		]);
		
		$this->layout = 'ajax_nodebug';
	}
	
	public function subscribe($field = null, $id = null)
	{
		if(!$this->request->is('ajax'))
		{
			throw new ForbiddenException(__('Request in not an Ajax request.'));
		}
		if (!$this->request->is('post')) 
		{
			throw new ForbiddenException(__('Request must be a POST.'));
		}
		if(!isset($this->request->data['subscribeurl']))
		{
			throw new NotFoundException(__('Subscribe URI is not set.'));
		}
		
		$message = false;
		$success = false;
		$redirect = false;
		$uri = $this->Subscriptions->fixUri($this->request->data['subscribeurl']);
		
		$user_id = AuthComponent::user('id');
		
		$subscribed = $this->Subscription->isSubscribed($user_id, $uri);
		
		if($subscribed === false)
		{
			$message = $this->Subscription->modelError;
		}
		else
		{
			$success = true;
			
			// not subscribed, forward them to the add form
			if($subscribed == 0)
			{
				$this->Session->write('Subscription.data', $this->request->data);
				$redirect = Router::url(['action' => 'add'], true);
			}
			else
			{
				// toggle the subscription
				if($subscription = $this->Subscription->getRecord($user_id, $uri))
				{
					$subscribed = ($subscription['Subscription']['active']?1:2);
					if($this->Subscription->toggleRecord($subscription['Subscription']['id'], 'active'))
					{
						$subscribed = ($subscribed==1?2:1);
					}
				}
			}
		}
		
		$this->set([
			'success' => $success,
			'message' => $message,
			'subscribed' => $subscribed,
			'user_id' => $user_id,
			'uri' => $uri,
			'redirect' => $redirect,
		]);
		
		$this->layout = 'ajax_nodebug';
	}
	
	public function add()
	{
		$sessionData = $this->Session->read('Subscription.data');
		$uri = $this->Subscriptions->fixUri($sessionData['subscribeurl']);
		
		if ($this->request->is('post'))
		{
			$this->Subscription->create();
			
			$this->request->data['Subscription']['user_id'] = AuthComponent::user('id');
			$this->request->data['Subscription']['uri'] = $uri;
			$this->request->data['Subscription']['active'] = true;
			
			if ($this->Subscription->save($this->request->data))
			{
				$this->Flash->success(__('The %s has been saved', __('Subscription')));
				$this->bypassReferer = true;
				
				if(isset($sessionData['redirect']))
				{
					$uri = $sessionData['redirect'];
				}
				else
				{
					$uri = '/'.trim($this->request->data['Subscription']['uri'], '/');
				}
				return $this->redirect($uri);
			}
			else
			{
				$this->Flash->error(__('The %s could not be saved. Please, try again.', __('Subscription')));
			}
		}
		else
		{
			$this->request->data = ['Subscription' => $sessionData];
			$this->request->data['Subscription']['uri'] = $uri;
		}
	}
	
	public function edit($id = null) 
	{
		$this->Subscription->id = $id;
		
		if (!$subscription = $this->Subscription->find('first', [
			'conditions' => ['Subscription.id' => $id],
		]))
		{
			throw new NotFoundException(__('Invalid %s', __('Subscription')));
		}
		
		if ($this->request->is('post') || $this->request->is('put')) 
		{
			if ($this->Subscription->save($this->request->data)) 
			{
				$this->Flash->success(__('The %s has been saved', __('Subscription')));
				return $this->redirect(['action' => 'index']);
			}
			else
			{
				$this->Flash->error(__('The %s could not be saved. Please, try again.', __('Subscription')));
			}
		}
		else
		{
			$this->request->data = $subscription;
		}
	}
	
	public function delete($id = null) 
	{
		$this->Subscription->id = $id;
		if (!$this->Subscription->exists()) 
		{
			throw new NotFoundException(__('Invalid %s', __('Subscription')));
		}
		if ($this->Subscription->delete()) 
		{
			$this->Flash->success(__('%s deleted', __('Subscription')));
			return $this->redirect(['action' => 'index']);
		}
		
		$this->Flash->error(__('%s was not deleted', __('Subscription')));
		return $this->redirect(['action' => 'index']);
	}
	
	public function admin_index()
	{
		$this->Prg->commonProcess();
		
		$conditions = [];
		
		$this->paginate['contain'] = ['User'];
		
		$this->paginate['conditions'] = $this->Subscription->conditions($conditions, $this->passedArgs);
		$subscriptions = $this->paginate();
		$this->set(compact('subscriptions'));
	}
	
	public function admin_add()
	{
		$sessionData = $this->Session->read('Subscription.data');
		$uri = $this->Subscriptions->fixUri($sessionData['subscribeurl']);
		
		if ($this->request->is('post'))
		{
			$this->Subscription->create();
			$this->request->data['Subscription']['active'] = true;
			
			if ($this->Subscription->save($this->request->data))
			{
				$this->Flash->success(__('The %s has been saved', __('Subscription')));
				$this->bypassReferer = true;
				$uri = '/'.trim($this->request->data['Subscription']['uri'], '/');
				return $this->redirect($uri);
			}
			else
			{
				$this->Flash->error(__('The %s could not be saved. Please, try again.', __('Subscription')));
			}
		}
		else
		{
			$this->request->data = ['Subscription' => $sessionData];
			$this->request->data['Subscription']['uri'] = $uri;
		}
		$users = $this->Subscription->User->typeFormList();
		$this->set(compact('users'));
	}
	
	public function admin_edit($id = null) 
	{
		$this->Subscription->id = $id;
		
		if (!$subscription = $this->Subscription->find('first', [
			'conditions' => ['Subscription.id' => $id],
		]))
		{
			throw new NotFoundException(__('Invalid %s', __('Subscription')));
		}
		
		if ($this->request->is('post') || $this->request->is('put')) 
		{
			if ($this->Subscription->save($this->request->data)) 
			{
				$this->Flash->success(__('The %s has been saved', __('Subscription')));
				return $this->redirect(['action' => 'index']);
			}
			else
			{
				$this->Flash->error(__('The %s could not be saved. Please, try again.', __('Subscription')));
			}
		}
		else
		{
			$this->request->data = $subscription;
		}
		$users = $this->Subscription->User->typeFormList();
		$this->set(compact('users'));
	}
	
	public function admin_delete($id = null) 
	{
		$this->Subscription->id = $id;
		if (!$this->Subscription->exists()) 
		{
			throw new NotFoundException(__('Invalid %s', __('Subscription')));
		}
		if ($this->Subscription->delete()) 
		{
			$this->Flash->success(__('%s deleted', __('Subscription')));
			return $this->redirect(['action' => 'index']);
		}
		
		$this->Flash->error(__('%s was not deleted', __('Subscription')));
		return $this->redirect(['action' => 'index']);
	}
}