<?php

class IssueReporterTask extends Shell
{
	public function execute(Shell $Shell, $command = false)
	{
	// Send an email to the admin users for any warning/errors that happen when a shell script is ran
	// see the AppModel::shellOut();
		$warnings = $errors = $notices = array();
		if(isset($Shell->uses) and is_array($Shell->uses))
		{
			foreach($Shell->uses as $use)
			{
				$plugin = false;
				if(stripos($use, '.') !== false)
				{
					list($plugin, $use) = explode('.', $use);
				}
				if(!is_object($Shell->{$use}))
				{
					continue;
				}
				$issues = array();
				
				if(method_exists($Shell->{$use}, 'getShellIssues'))
				{
					$issues = $Shell->{$use}->getShellIssues();
				}
				
				if(isset($issues['warning']))
				{
					foreach($issues['warning'] as $k => $v) $warnings[$k] = $v;
				}
				
				if(isset($issues['error']))
				{
					foreach($issues['error'] as $k => $v) $errors[$k] = $v;
				}
				
				if(isset($issues['notice']))
				{
					foreach($issues['notice'] as $k => $v) $notices[$k] = $v;
				}
			}
		}
		
		// yay, no problems.
		if(empty($warnings) and empty($errors) and empty($notices))
		{
			
			//$Shell->out(__('No issues to report.'), 1, Shell::QUIET);
			return true;
		}
		
		$emails = false;
		if(!is_object($Shell->User))
		{
			$Shell->loadModel('User');
			if(!is_object($Shell->User))
			{
				$Shell->out(__('Unable to load User Model'), 1, Shell::QUIET);
				return false;
			}
		}
		
		$emails = false;
		if(method_exists($Shell->User, 'adminEmails'))
		{
			$emails = $Shell->User->adminEmails();
		}
		
		if(!$emails)
		{
			$Shell->out(__('No admin email addresses found to send issues to.'), 1, Shell::QUIET);
			return false;
		}
		
		$Shell->out(__('Found %s admin email addresses to send issues to.', count($emails)), 1, Shell::QUIET);
		
		
		// load the email task
		$Email = $this->Tasks->load('Utilities.Email');
		
		$warning_count = count($warnings);
		$error_count = count($errors);
		
		if($warning_count or $error_count)
		{
			$subject = __('Issues - total: %s - command: %s::%s - errors: %s - warnings: %s', 
				($warning_count + $error_count),
				get_class($Shell),
				$Shell->command,
				$error_count,
				$warning_count
			);
			
			$this->out($subject, 1, Shell::QUIET);
			
			$body = array();
			foreach($errors as $error => $times)
			{
				$body[] = __('Error: %s', $Shell->User->obfuscateString($error));
				$body[] = __('Times occurred: %s', count($times));
				$last_time = array_pop($times);
				$body[] = __('Last occurred: %s', $last_time);
				$body[] = "------------------------------";
				$body[] = ' ';
			}
			
			foreach($warnings as $warning => $times)
			{
				$body[] = __('Warning: %s', $Shell->User->obfuscateString($warning));
				$body[] = __('Times occurred: %s', count($times));
				$last_time = array_pop($times);
				$body[] = __('Last occurred: %s', $last_time);
				$body[] = "------------------------------";
				$body[] = ' ';
			}
			
			$body = implode("\n", $body);
			
			//set the email parts
			$Email->set('to', $emails);
			$Email->set('subject', $subject);
			$Email->set('body', $body);
			
			// send the email
			if($Email->execute())
			{
				$email_results = __('Issues Email sucessfully sent to: %s', implode(', ', $emails));
			}
			else
			{
				$email_results = __('Issues Email failed to send.');
			}
			$Shell->out($email_results, 1, Shell::QUIET);
		}
		
		// Send notices in a different email
		$notice_count = count($notices);
		
		if($notice_count)
		{
			$subject = __('Notices - total: %s - command: %s::%s', 
				$notice_count,
				get_class($Shell),
				$Shell->command
			);
			
			$this->out($subject, 1, Shell::QUIET);
			
			$body = array();
			foreach($notices as $notice => $times)
			{
				$body[] = __('Notice: %s', $Shell->User->obfuscateString($notice));
				$body[] = __('Times occurred: %s', count($times));
				$last_time = array_pop($times);
				$body[] = __('Last occurred: %s', $last_time);
				$body[] = "------------------------------";
				$body[] = ' ';
			}
			
			$body = implode("\n", $body);
			
			//set the email parts
			$Email->set('to', $emails);
			$Email->set('subject', $subject);
			$Email->set('body', $body);
			
			// send the email
			if($Email->execute())
			{
				$email_results = __('Notices Email sucessfully sent to: %s', implode(', ', $emails));
			}
			else
			{
				$email_results = __('Notices Email failed to send.');
			}
			$Shell->out($email_results, 1, Shell::QUIET);
		}
	}
}