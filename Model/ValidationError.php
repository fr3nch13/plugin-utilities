<?php

App::uses('UtilitiesAppModel', 'Utilities.Model');
App::uses('CakeSession', 'Model/Datasource');
App::uses('Router', 'Routing');

class ValidationError extends UtilitiesAppModel 
{
	
	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
		),
	);
	
	public $actsAs = array(
		'Utilities.Email',
	);

	// $Model should be the model that errored out from the 
	// behavior Utilities.CommonBehavior::afterValidate();
	public function saveErrors(Model $Model)
	{
		$this->create();
		$this->data = array(
			'user_id' => (AuthComponent::user('id')?AuthComponent::user('id'):0),
			'model_name' => $Model->name,
			'model_alias' => $Model->alias,
			'model_id' => ($Model->id?$Model->id:0),
			'path' => Router::url(),
			'errors' => json_encode($Model->validationErrors),
			'data' => json_encode($Model->data),
		);
		
		if($this->save($this->data))
		{
			$this->shellOut(json_encode($this->data), 'validations', 'notice');
		}
	}
	
	// notify the admins of validation errors
	public function notifyAdmins($timeago = '-1 hour')
	{
		$validation_errors = $this->find('all', array(
			'recursive' => 0,
			'conditions' => array(
				'ValidationError.created >' => date('Y-m-d H:i:s', strtotime($timeago)),
			),
		));
		
		if(empty($validation_errors))
		{
			return true;
		}
		
		$this->shellOut(__('Found %s validation errors.', count($validation_errors)), 'validations', 'info');
		
		// all Admin 
		$adminEmails = $this->User->adminEmails();
		foreach($adminEmails as $adminEmail)
		{
			$emails[$adminEmail] = $adminEmail;
		}
		
	 	// rebuild it to use the EmailBehavior from the Utilities Plugin
	 	$this->Email_reset();
		// set the variables so we can use view templates
		$viewVars = array(
			'validation_errors' => $validation_errors,
		);
		
		$this->Email_set('to', $emails);
		$this->Email_set('subject', __('%s - Count: %s', __('Validation Errors'), count($validation_errors)));
		$this->Email_set('viewVars', $viewVars);
		$this->Email_set('template', 'Utilities.email_validation_errors');
		
		if($this->Email_executeFull())
		{
			$this->shellOut(__('Sent %s validation errors to %s.', count($validation_errors), implode(', ', $emails)), 'validations', 'info');
		}
	}
}