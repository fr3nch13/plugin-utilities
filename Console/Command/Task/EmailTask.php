<?php

App::uses('CakeEmail', 'Network/Email');
App::uses('Shell', 'Console');

class EmailTask extends Shell
{
	
	public $to = false;
	
	public $from = false;
	
	public $replyTo = false;
	
	public $subject = false;
	
	public $body = false;
	
	public $emailFormat = 'both';
	
	public $theme = 'Fo';
	
	public $template = false;
	
	public $viewVars = array();
	
	public $helpers = array(
		'Html' => array('className' => 'Utilities.HtmlExt'),
		'Text',
		'Wrap' => array('className' => 'Utilities.Wrap'),
		'Common' => array('className' => 'Utilities.Common'),
	);
	
	public $debug = false;
	
	public $config_vars = false;
	
	// use digest file instead of sending a bunch of emails
	public $digest_file = false;
	public $digest_file_variables = false;
	public $digest_results = false;
	public $digest_results_count = false;
	
	// when using digest, force sending this particular email
	// used for things like errors.
	public $force_send = false;
	
	public function reset()
	{
		$this->to = false;
		$this->cc = false;
		$this->bcc = false;
		$this->from = false;
		$this->replyTo = false;
		$this->subject = false;
		$this->body = false;
		$this->emailFormat = 'both';
		$this->theme = 'Fo';
		$this->template = false;
		$this->viewVars = array();
		$this->config_vars = false;
		$this->headers = array();
		$this->attachments = array();
		$this->debug = false;
	}
	
	public function executeDigest($config = 'default')
	{
		$this->digest_results = false;
		$this->digest_error = false;
		if(!$this->digest_file)
		{
			$this->digest_error = __('digest_file is not set.');
			return false;
		}
		
		$cron_name = basename($this->digest_file);
		$this->digest_file_variables = $this->digest_file. '.headers';
		$this->digest_file .= '.email';
		
		if(!file_exists($this->digest_file))
		{
			$this->digest_error = __('digest_file "%s" doesn\'t exist', basename($this->digest_file));
			return false;
		}
		
		if(!file_exists($this->digest_file_variables))
		{
			$this->digest_error = __('digest_file_variables "%s" doesn\'t exist', basename($this->digest_file_variables));
			return false;
		}
		
		if(!$variables = file_get_contents($this->digest_file_variables))
		{
			$this->digest_error = __('unable to get digest_file_variables from %s', basename($this->digest_file_variables));
			return false;
		}
		unlink($this->digest_file_variables);
		$variables = unserialize($variables);
		
		foreach($variables as $key => $value)
		{
			$this->set($key, $value);
		}
		
		if(!$this->body = file_get_contents($this->digest_file))
		{
			$this->digest_error = __('unable to get body from %s', basename($this->digest_file));
			return false;
		}
		unlink($this->digest_file);
		
		$site_title = Configure::read('Site.title');
		$site_url = Configure::read('Site.base_url');
		
		if($site_title and $site_url)
		{
			$prepend = __('Site: %s', $site_title). "    \n";
			$prepend .= __('Url: %s', $site_url). "    \n";
			$prepend .= "\n------------------------------\n";
			$this->body = $prepend. $this->body;
		}
		
		$this->digest_results_count = substr_count($this->body, 'digest_instance');
		$this->body = str_replace('digest_instance', '', $this->body);

		// overwrite email subject to include Digest results and 'Digest' in the subject for filtering
		$this->subject = __('%s (count: %s)', $cron_name, $this->digest_results_count);
		$this->digest_results = $this->subject;

		$this->subject = __('%s - Cron Digest - %s', $site_title, $this->subject);
		
		if($this->to)
		{
			if(is_string($this->to))
			{
				$tos = explode(',', $this->to);
				$tosnice = array();
				foreach($tos as $i => $to)
				{
					$to = trim($to);
					$tosnice[$to] = $to;
				}
				$this->to = $tosnice;
			}
		}
		
		$this->emailFormat = 'text';
		
		$email = new CakeEmail($config);
		
		$email->to($this->to);
		if($this->from) $email->from($this->from);
		if($this->replyTo) $email->replyTo($this->replyTo); 
		if($this->subject) $email->subject($this->subject);
		if($this->emailFormat) $email->emailFormat($this->emailFormat);
		if($this->template) $email->template($this->template);
		if($this->viewVars) $email->viewVars($this->viewVars);
		
		if(!$email->send($this->body))
		{
			$this->digest_error = __('unable to send email');
			return false;
		}
		return true;
	}

	public function execute($config = 'default')
	{	
		// validation
		if(!$this->subject) return false;
		if(!$this->body) return false;
		
		if($this->to)
		{
			if(is_string($this->to))
			{
				$tos = explode(',', $this->to);
				$tosnice = array();
				foreach($tos as $i => $to)
				{
					$to = trim($to);
					$tosnice[$to] = $to;
				}
				$this->to = $tosnice;
			}
		}
		
		$site_title = Configure::read('Site.title');
		$site_url = Configure::read('Site.base_url');
		
		if($this->digest_file)
		{
			$this->body .= "\ndigest_instance";
		}
		
		if($site_title and $site_url and !$this->digest_file)
		{
			if($site_title)
			{
				$this->subject = $site_title. ' - '. $this->subject;
			}
		
			$prepend = __('Site: %s', $site_title). "    \n";
			$prepend .= __('Url: %s', $site_url). "    \n";
			$prepend .= "\n------------------------------\n";
			$this->body = $prepend. $this->body;
		}
		
		$this->emailFormat = 'text';
		
		// save this information in a digest file to be sent once a day
		if($this->digest_file)
		{
			$this->digest_file_variables = $this->digest_file. '.headers';
			$this->digest_file .= '.email';
			
			if(!file_exists($this->digest_file))
			{
				touch($this->digest_file);
			}
			if(!file_exists($this->digest_file_variables))
			{
				touch($this->digest_file_variables);
				
				// save the variables that are need to eventually send the digest email
				$variables = serialize(array(
					'to' => $this->to,
					'from' => $this->from,
					'subject' => $this->subject,
					'emailFormat' => $this->emailFormat,
					'template' => $this->template,
					'viewVars' => $this->viewVars,
				));
				
				if($fh = fopen($this->digest_file_variables, 'w'))
				{
					fwrite($fh, $variables);
					fclose($fh);
				}
			}
			
			if($fh = fopen($this->digest_file, 'a'))
			{
				$stringData = __("Subject: %s    \nDate: %s    \nBody:    \n%s    \n------------------------------    \n\n", $this->subject, date('Y-m-d H:i:s'), $this->body);
				fwrite($fh, $stringData);
				fclose($fh);
			}
			
			if(!$this->force_send)
			{
				return true;
			}
		}
		
		$email = new CakeEmail($config);
		if($this->debug)
		{
			$email->transport('Debug');
		}
		
		$email->to($this->to);
		if($this->from) $email->from($this->from);
		if($this->subject) $email->subject($this->subject);
		if($this->emailFormat) $email->emailFormat($this->emailFormat);
		if($this->template) $email->template($this->template);
		if($this->viewVars) $email->viewVars($this->viewVars);
		
		return $email->send($this->body);
	}
	
	public function executeFull($config = 'default')
	{
		$email = new CakeEmail($config);
		if($this->debug)
		{
			$email->transport('Debug');
		}
		
		if($this->config_vars)
		{
			$email->config($this->config_vars);
		}
		
		// validation
		if(!$this->subject) return false;
		
		if($this->to)
		{
			if(is_string($this->to))
			{
				$tos = explode(',', $this->to);
				$tosnice = array();
				foreach($tos as $i => $to)
				{
					$to = trim($to);
					$tosnice[$to] = $to;
				}
				$this->to = $tosnice;
			}
			$email->to($this->to);
		}
		if($this->from) $email->from($this->from);
		if($this->subject) $email->subject($this->subject);
		
		if($this->emailFormat) $email->emailFormat($this->emailFormat);
		if($this->template) $email->template($this->template);
		if($this->helpers) $email->helpers($this->helpers);
		if($this->theme) $email->theme($this->theme);
		if($this->viewVars)
		{
			$this->viewVars['title_for_layout'] = $this->subject;
			$email->viewVars($this->viewVars);
		}
		
		return $email->send();
	}
	
	public function set($variable = false, $value = false)
	{
	/*
	 * Used to set the values for an email to be sent.
	 */
		if(!$variable) return false;
		if(!$value) return false;
		
		$this->{$variable} = $value;
		return true;
	}
	
	public function getVariable($variable = false)
	{
	/*
	 * Used to get a value from this instance.
	 */
		if(!$variable) return false;
		if(!isset($this->{$variable})) return false;
		if(!$this->{$variable}) return false;
		
		return $this->{$variable};
	}
	
	public function config_vars($config_vars = null)
	{
		$this->config_vars = $config_vars;
	}
}