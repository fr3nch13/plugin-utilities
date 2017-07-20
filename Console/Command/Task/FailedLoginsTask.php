<?php

class FailedLoginsTask extends Shell
{
	public function execute(Shell $Shell)
	{
		
		// make sure we have the models we need (User, LoginHistory)
		if(!is_object($Shell->User))
		{
			throw new MissingModelException(__('Unable to find the %s Model (1)', __('User')));
		}
		if(!$Shell->User instanceof Model)
		{
			throw new MissingModelException(__('Unable to find the %s Model (2)', __('User')));
		}
		if(!method_exists($Shell->User,'adminEmails'))
		{
			throw new MissingModelException(__('Unable to use the %s Model (2)', __('LoginHistory')));
		}
		
		if(!is_object($Shell->LoginHistory))
		{
			throw new MissingModelException(__('Unable to find the %s Model (1)', __('LoginHistory')));
		}
		if(!$Shell->LoginHistory instanceof Model)
		{
			throw new MissingModelException(__('Unable to find the %s Model (2)', __('LoginHistory')));
		}
		if(!method_exists($Shell->LoginHistory,'failedLogins'))
		{
			throw new MissingModelException(__('Unable to use the %s Model (2)', __('LoginHistory')));
		}
	
		// get the list of failed logins
		$failed_logins = $Shell->LoginHistory->failedLogins($Shell->params['minutes']);
		
		if(!$failed_logins)
		{
			$Shell->out(__('No failed logins'));
			return false;
		}
		
		$Shell->out(__('Found %s  failed logins.', count($failed_logins)), 1, Shell::QUIET);
		
		// get the admin users
		$emails = $Shell->User->adminEmails();
		
		if(!$emails)
		{
			$Shell->out(__('No admin email addresses found.'));
			return false;
		}
		
		$Shell->out(__('Found %s admin email addresses.', count($emails)), 1, Shell::QUIET);
		
		// load the email task
		$Email = $Shell->Tasks->load('Utilities.Email');
		$Email->set('template', 'Utilities.failed_logins');
		
		// set the variables so we can use view templates
		$viewVars = array(
			'failed_logins' => $failed_logins,
		);
		
		//set the email parts
		$Email->set('to', $emails);
		$Email->set('subject', __('Failed Logins: %s', count($failed_logins)));
		$Email->set('viewVars', $viewVars);
		
		// send the email
/*
		if(!$results = $Email->executeFull())
		{
			$Shell->out(__('Error sending notification email for %s.', __('Failed Logins')), 1, Shell::QUIET);
		}
*/
		
		$Shell->out(__('Sent ADMIN notification email for %s.', __('Failed Logins')), 1, Shell::QUIET);
		
		$Shell->out(__('Sending Emails to individual users %s.', __('Failed Logins')), 1, Shell::QUIET);
		
		$failed_login_emails = array();
		foreach($failed_logins as $failed_login)
		{
			if(!isset($failed_login['User']['email']))
				continue;
			
			$user_email = $failed_login['User']['email'];
			
			if(!isset($failed_login_emails[$user_email]))
				$failed_login_emails[$user_email] = array('user' => $failed_login['User'], 'failed_logins' => array());
			
			
			$failed_login_emails[$user_email]['failed_logins'][] = $failed_login;
		}
		
		foreach($failed_login_emails as $email_address => $failed_login_email)
		{
			$Email->reset();
			$Email->set('template', 'Utilities.failed_logins_user');
//			$Email->set('debug', true);
			
			// set the variables so we can use view templates
			$viewVars = array(
				'failed_logins' => $failed_login_email['failed_logins'],
			);
			
			//set the email parts
			$Email->set('to', $email_address);
			$Email->set('subject', __('Failed Logins: %s', count($failed_logins)));
			$Email->set('viewVars', $viewVars);
			if(!$results = $Email->executeFull())
			{
				$Shell->out(__('Error sending notification email for %s to %s.', __('Failed Logins'), $email_address), 1, Shell::QUIET);
			}
			
			$Shell->out(__('Sent notification email for %s %s to %s.', count($failed_login_email['failed_logins']), __('Failed Logins'), $email_address), 1, Shell::QUIET);
		}
	}
}