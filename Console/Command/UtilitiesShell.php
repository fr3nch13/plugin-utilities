<?php

App::uses('UtilitiesAppShell', 'Utilities.Console/Command');
App::uses('Inflector', 'Utility');
App::uses('Router', 'Utility');

class UtilitiesShell extends UtilitiesAppShell
{
	// the models to use
	public $uses = array(
		'User', 'Utilities.ValidationError', 'Utilities.SourceStat',
		'Utilities.Subscription'
	);
	
	public function startup() 
	{
		$this->clear();
		$this->out('Utilities Shell');
		$this->hr();
		return parent::startup();
	}
	
	public function getOptionParser()
	{
	/*
	 * Parses out the options/arguments.
	 * http://book.cakephp.org/2.0/en/console-and-shells.html#configuring-options-and-generating-help
	 */
	
		$parser = parent::getOptionParser();
		
		$parser->description(__d('cake_console', 'The Utilities Shell used to run cron jobs common in all of the apps.'));
		
		$parser->addSubcommand('failed_logins', array(
			'help' => __d('cake_console', 'Emails a list of failed logins to the admins every 5 minutes'),
			'parser' => array(
				'options' => array(
					'minutes' => array(
						'help' => __d('cake_console', 'Change the time frame from 5 minutes ago.'),
						'short' => 'm',
						'default' => 5,
					),
				),
			),
		));
		
		$parser->addSubcommand('validation_errors', array(
			'help' => __d('cake_console', 'Emails a list of validation_errors from the past hour.'),
			'parser' => array(
				'options' => array(
					'minutes' => array(
						'help' => __d('cake_console', 'Change the time frame from 1 hour ago.'),
						'short' => 'm',
						'default' => 5,
					),
				),
			),
		));
		
		$parser->addSubcommand('update_source_stats', [
			'help' => __d('cake_console', 'Updates the stats for external sources.'),
		]);
		
		$parser->addSubcommand('fix_users_records', [
			'help' => __d('cake_console', 'Goes through and tried to find the current users.'),
		]);
		
		$parser->addSubcommand('fix_user_records', [
			'help' => __d('cake_console', 'Fixes user records so their unique id matches the one from the accounts table.'),
			'parser' => [
				'options' => [
					'user_id' => [
						'help' => __d('cake_console', 'The old user id.'),
						'short' => 'u',
					],
					'new_user_id' => [
						'help' => __d('cake_console', 'The user id to change the records to.'),
						'short' => 'n',
					],
					'countdown' => [
						'help' => __d('cake_console', 'Count down from the user_id until you find a user with other records.'),
						'boolean' => true,
						'short' => 'c',
					],
				],
			],
		]);
		
		return $parser;
	}
	
	public function failed_logins()
	{
	////// Still needs to be built out and move all of the info from the individual apps to the utilities plugin
	
	// Emails a list of failed logins to the admins every 5 minutes
	// Only sends an email if there was a failed login
	// Everything is taken care of in the Task
		$FailedLogins = $this->Tasks->load('Utilities.FailedLogins')->execute($this);
	}
	
	public function validation_errors()
	{
		$timeago = '60';
		if(isset($this->args[0]))
		{
			$timeago = $this->args[0];
		}
		$timeago = __('-%s minutes', $timeago);
		
		/////////// email a list of validation errors
		return $this->ValidationError->notifyAdmins($timeago);
	}
	
	public function update_source_stats()
	{
		// get a list of hostnames that need to be looked up
		$results = $this->SourceStat->updateStats();
	}
	
	public function autocache_views()
	{
		$this->out(__('Begin autocaching'));
		
		$urls = array();
		$controllers = App::objects('controller');
		
		$prefixes = Configure::read('Routing.prefixes');
		
		foreach($controllers as $controller)
		{
			App::uses($controller, 'Controller');
			
			$classVars = get_class_vars($controller);
			
			$controller = str_replace('Controller', '', $controller);
			
			if(!isset($classVars['cacheAction']) or !$classVars['cacheAction'])
				continue;
			
			if(!is_array($classVars['cacheAction']))
				continue;
			
			$controllerUrl = Inflector::tableize($controller);
			if($controllerUrl == 'mains')
				$controllerUrl = 'main';
			
			$urlDefault = array(
				'controller' => $controllerUrl,
			);
			
			foreach($classVars['cacheAction'] as $action => $actionSettings)
			{
				if(!isset($actionSettings['recache']) or !$actionSettings['recache'])
					continue;
				
				$this->out(__('Found recache for controller: %s - action: %s', $controller, $action));
				
				$url = $urlDefault;
				$url['action'] = $action;
				
				// see if it has a prefix
				foreach($prefixes as $prefix)
				{
					if(preg_match('/^'.$prefix.'_/', $action))
					{
						$url['prefix'] = $prefix;
						$url[$prefix] = true;
						break;
					}
				}
				
				// consider them seperate calls
				if(isset($actionSettings['urls']) and is_array($actionSettings['urls']))
				{
					$thisUrl = $url;
					foreach($actionSettings['urls'] as $i => $arg)
					{
						if(is_array($arg))
						{
							$thisUrl = array_merge($url, $arg);
							
							$urlKey = Router::url($thisUrl);
							$urlKey = Inflector::slug(strtolower($urlKey)). '.php';
							
							$thisUrl['?']['recache'] = true;
							$urls[$urlKey] = $thisUrl;
							
						}
					}
				}
				else
				{	
					$urlKey = Router::url($url);
					$urlKey = Inflector::slug(strtolower($urlKey)). '.php';
					$url['?']['recache'] = true;
					$urls[$urlKey] = $url;
				}
			}
		}
		
		$fullstart = time();
		$this->out(__('Found %s urls to recache', count($urls)));
		
		$pathBase = CACHE. 'views'. DS;
		
		foreach($urls as $key => $url)
		{
			$url = Router::url($url, true);
			
			$this->out(__('Checking url: %s', $url));
			
			if(is_file($pathBase. $key) and is_readable($pathBase. $key))
			{
				$this->out(__('Cache EXISTS for url: %s - %s', $url, $pathBase. $key));
				$line = fgets(fopen($pathBase. $key, 'r'));
				$cachetime = false;
				$matches = array();
				if(preg_match('/cachetime:(\d+)/', $line, $matches))
				{
					// still valid
					if (time() < $matches['1'])
					{
						$this->out(__('Cache VALID for url: %s', $url));
						continue;
					}
					$this->out(__('Cache EXPIRED for url: %s', $url));
				}
			}
			
			$start = time();
			$this->out(__('Begin recaching of url: %s', $url));
			
			$result = file_get_contents($url);
			
			$diff = time() - $start;
			$this->out(__('Recached - time: %s - url: %s', $diff, $url));
		}
		$totaldiff = time() - $fullstart;
		$this->out(__('Finished recaching %s urls in %s seconds', count($urls), $totaldiff));
	}
	
	public function send_subscriptions()
	{
		$results = $this->Subscription->sendEmails();
	}
	
	public function fix_user_records($user_id = false)
	{
		$countdown = $this->param('countdown');
		
		if(!$user_id)
		{
			if(!$user_id = $this->param('user_id'))
			{
				$this->error(__('No user id was set.'));
			}
		}
		$this->out(__('Current User id set to: %s', $user_id));
		
		if($new_user_id = $this->param('new_user_id'))
		{
			$this->out(__('New User id set to: %s', $new_user_id));
		}
		
		if(!$user = $this->User->read(null, $user_id))
		{
			$this->out('<warning>'.__('No user record found for id: %s', $user_id).'</warning>');
			return false;
		}
		
		
		
		$this->out(__('Finding results for User: %s', $user['User']['name']));
		
		if(!$user['User']['old_id'])
		{
			$old_id = $this->in(__('The user record doesn\'t have their old id set. What is it?', range(0,999), $user['User']['id']));
			if($old_id)
			{
				$this->User->updateAll(
					['User.old_id' => $old_id],
					['User.id' => $user['User']['id']]
				);
			}
		}
		
		if($new_user_id)
		{
			$newUser = $this->User->read(null, $new_user_id);
			$this->out(__('Change results from User: %s to New User: %s', $user['User']['name'], $newUser['User']['name']));
		}
		
		$hasRecords = false;
		foreach($this->User->hasMany as $subModel => $subModelSettings)
		{
			//$this->out(__('Finding results for model: %s', $subModel));
			
			if(!isset($subModelSettings['foreignKey']))
			{
				$this->out('<error>'.__('Foreign Key not set for model %s', $subModel).'</error>');
				continue;
			}
			
			$conditions = [
				$this->User->{$subModel}->alias.'.'.$subModelSettings['foreignKey'] => $user_id,
			];
			
			$count = $this->User->{$subModel}->find('count', [
				'conditions' => $conditions,
			]);
			
			if($count)
			{
				$this->out('<info>'.__('Found %s records in %s for user id %s', $count, $subModel, $user_id).'</info>');
				$hasRecords = true;
			}
			
			if($new_user_id and $count)
			{
				$this->out(__('Changing %s records in %s from user id %s to %s', $count, $subModel, $user_id, $new_user_id));

				$this->User->{$subModel}->updateAll(
					[$this->User->{$subModel}->alias.'.'.$subModelSettings['foreignKey'] => $new_user_id],
					[$this->User->{$subModel}->alias.'.'.$subModelSettings['foreignKey'] => $user_id]
				);
			}
		}
		if(!$hasRecords)
		{
			$this->out('<warning>'.__('Found no records for user (%s) %s', $user_id, $user['User']['name']).'</warning>');
		}
		return true;
	}
	
	public function fix_users_records()
	{
		$this->out(__('Fixing user ids.'));
		$users = $this->User->find('list', ['order' => ['User.id' => 'DESC'], 'fields' => ['User.id', 'User.name'] ]);
		
		$this->out(__('Found %s total users.', count($users)));
		
		// track this user's possible old id
		$this->out(__('Step 1: Map users to their old ids'));
		foreach($users as $user_id => $user_name)
		{
			$this->fix_user_map_id($user_id);
		}
		
		// track this user's possible old id
		$this->out(str_repeat('-', 64));
		$this->out(__('Step 2: Check to see if users with different old ids have records that need to be updated'));
		$users = $this->User->find('list', [
			'order' => ['User.id' => 'DESC'], 
			'fields' => ['User.id', 'User.old_id'],
			'conditions' => ['User.old_id !=' => 'User.id'],
		]);
		
		foreach($users as $user_id => $old_id)
		{
			if($user_id == $old_id)
				unset($users[$user_id]);
		}
		
		$this->out(__('Found %s users to check.', count($users)));
		
		foreach($users as $user_id => $user_name)
		{
			$this->fix_user_check_update($user_id);
		}
	}
	
	private function fix_user_map_id($user_id = false)
	{
		if(!$user_id)
		{
			$this->out('<error>'.__('No user ID set.').'</error>');
			return false;
		}
		
		if(!$user = $this->User->read(null, $user_id))
		{
			$this->out('<error>'.__('Unable to find user with id %s', $user_id).'</error>');
			return false;
		}
		
		$this->out(__('Checking User: (%s) %s', $user['User']['id'], $user['User']['name']));
		
		if(!$user['User']['old_id'])
		{
			$old_id = $this->in(__('The user record doesn\'t have their old id set. What is it?', range(0,999), $user['User']['id']));
			if(!$old_id)
				$old_id = $user['User']['id'];
			
			if($old_id)
			{
				if($this->User->updateAll(
					['User.old_id' => $old_id],
					['User.id' => $user['User']['id']]
				))
				{
					$this->out('<info>'.__('User: (%s) %s is mapped to old id: %s.', $user['User']['id'], $user['User']['name'], $old_id).'</info>');
				}
			}
		}
		else
		{
			$old_id = $user['User']['old_id'];
			$this->out('<info>'.__('User: (%s) %s is mapped to old id: %s.', $user['User']['id'], $user['User']['name'], $old_id).'</info>');
		}
		return true;
	}
	
	private function fix_user_check_update($user_id = false)
	{
		if(!$user_id)
		{
			$this->out('<error>'.__('No user ID set.').'</error>');
			return false;
		}
		
		if(!$user = $this->User->read(null, $user_id))
		{
			$this->out('<error>'.__('Unable to find user with id %s', $user_id).'</error>');
			return false;
		}
		
		$userName = __('(%s) %s', $user['User']['id'], $user['User']['name']);
		
		$user_id = $user['User']['id'];
		$old_id = $user['User']['old_id'];
		
		$oldUser = $this->User->read(null, $old_id);
		
		$oldUserName = __('(%s) Not Found', $old_id);
		if($oldUser)
		{
			$oldUserName = __('(%s) %s', $oldUser['User']['id'], $oldUser['User']['name']);
		}
		$this->out(__('Old User: %s - Current User: %s', $oldUserName, $userName));
		
		$hasRecords = false;
		foreach($this->User->hasMany as $subModel => $subModelSettings)
		{
			//$this->out(__('Finding results for model: %s', $subModel));
			
			if(!isset($subModelSettings['foreignKey']))
			{
				$this->out('<error>'.__('Foreign Key not set for model %s', $subModel).'</error>');
				continue;
			}
			
			$conditions = [
				$this->User->{$subModel}->alias.'.'.$subModelSettings['foreignKey'] => $old_id,
			];
			
			// if their schema has a created date, update only ones before 11/03/2106
			$schema = $this->User->{$subModel}->schema();
			
			if(isset($schema['created']))
				$conditions[$this->User->{$subModel}->alias.'.created <='] = '2016-11-04 14:30:00';
			
			
			$count = $this->User->{$subModel}->find('count', [
				'conditions' => $conditions,
			]);
			
			$updateRecord = false;
			if($count)
			{
				$this->out(__('Old User: %s - Current User: %s', $oldUserName, $userName));
				$this->out('<info>'.__('Found %s records in %s for user id %s', $count, $subModel, $old_id).'</info>');
				$hasRecords = true;
				$updateRecord = $this->in(__('Would you like to map the records with the id %s to the new id %s', $old_id, $user_id), ['Y', 'N'], 'Y');
				
				if($updateRecord !== 'Y')
					$updateRecord = false;
				else
					$updateRecord = true;
			}
			if($updateRecord and $count)
			{
				$this->out('<info>'.__('Changing %s records in %s from user id %s to %s', $count, $subModel, $old_id, $user_id).'</info>');

				$this->User->{$subModel}->updateAll(
					[$this->User->{$subModel}->alias.'.'.$subModelSettings['foreignKey'] => $user_id],
					[$this->User->{$subModel}->alias.'.'.$subModelSettings['foreignKey'] => $old_id]
				);
			}
		}
		
		if(!$hasRecords)
		{
			$this->out('<warning>'.__('Found no records for user %s', $oldUserName).'</warning>');
		}
		
		if($oldUser and $user['User']['email'] == $oldUser['User']['email'])
		{
			$removeOldUser = $this->in(__('Would you like to delete the old user record - %s?', $oldUserName), ['Y', 'N'], 'Y');
			if($removeOldUser == 'Y')
			{
				if($this->User->delete($old_id, false))
				{
					$this->out('<info>'.__('Deleted the old user record.').'</info>');
				}
			}
		}
	}
}