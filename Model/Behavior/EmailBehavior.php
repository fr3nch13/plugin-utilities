<?php

App::uses('CakeEmail', 'Network/Email');
App::uses('Shell', 'Console');

class EmailBehavior extends ModelBehavior
{
	public $Email = false;
	
	public $to = false;
	
	public $cc = false;
	
	public $bcc = false;
	
	public $from = false;
	
	public $replyTo = false;
	
	public $subject = false;
	
	public $body = false;
	
	public $emailFormat = 'both';
	
	public $theme = 'Fo';
	
	public $template = false;
	
	public $layout = 'Utilities.default';
	
	public $viewVars = array();
	
	public $config_vars = false;
	
	public $headers = array();
	
	public $attachments = array();
	
	public $debug = false;
	
	public $helpers = array(
		'Html' => array('className' => 'Utilities.HtmlExt'),
		'Text',
		'Wrap' => array('className' => 'Utilities.Wrap'),
		'Common' => array('className' => 'Utilities.Common'),
	);
	
	public function Email_reset()
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

	public function Email_execute(Model $Model, $config = 'default')
	{
		$this->Email = new CakeEmail($config);
		if($this->debug)
		{
			Configure::write('debug', 2);
			$this->Email->transport('Debug');
		}
		
		// validation
		if(!$this->subject) return false;
		if(!$this->body) return false;
		
		$site_title = Configure::read('Site.title');
		$site_url = Configure::read('Site.base_url');
		
		if($site_title)
		{
			// for just text emails right now
			if($this->emailFormat == 'text')
			{
				$prepend = __('Site: %s', $site_title). "\n";
				$prepend .= __('Url: %s', $site_url). "\n";
				$prepend .= "\n------------------------------\n";
				$this->body = $prepend. $this->body;
			}
		}
		
		if($this->to) $this->Email->to($this->to);
		if($this->from) $this->Email->from($this->from);
		if($this->subject) $this->Email->subject($this->subject);
		if($this->emailFormat) $this->Email->emailFormat($this->emailFormat);
		if($this->template) $this->Email->template($this->template);
		if($this->viewVars) $this->Email->viewVars($this->viewVars);
		if($this->headers) $this->Email->setHeaders($this->headers);
		
		return $this->Email->send($this->body);
	}
	
	public function Email_executeFull(Model $Model, $config = 'default')
	{
		$this->Email = new CakeEmail($config);
		if($this->debug)
		{
			Configure::write('debug', 2);
			$this->Email->transport('Debug');
		}
		
		if($this->config_vars)
		{
			$this->Email->config($this->config_vars);
		}
		
		// validation
		if(!$this->subject) 
		{
			$Model->modelError = __('No subject line set.');
			return false;
		}
		
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
			$this->Email->to($this->to);
		}
		if($this->cc) $this->Email->cc($this->cc);
		if($this->bcc) $this->Email->bcc($this->bcc);
		if($this->from) $this->Email->from($this->from);
		if($this->replyTo) $this->Email->replyTo($this->replyTo);
		if($this->subject) $this->Email->subject($this->subject);
		
		if($this->emailFormat) $this->Email->emailFormat($this->emailFormat);
		if($this->template) $this->Email->template($this->template, $this->layout);
		if($this->theme) $this->Email->theme($this->theme);
		if($this->helpers) $this->Email->helpers($this->helpers);
		if($this->headers) $this->Email->setHeaders($this->headers);
		if($this->attachments) 
		{
			foreach($this->attachments as $attachment)
			{
				$this->Email->addAttachments($attachment);
			}
		}
		if($this->viewVars)
		{
			$this->viewVars['title_for_layout'] = $this->subject;
			$this->Email->viewVars($this->viewVars);
		}
		return $this->Email->send();
	}
	
	public function Email_set(Model $Model, $variable = false, $value = false)
	{
	/*
	 * Used to set the values for an email to be sent.
	 */
		if(!$variable) return false;
		if(!$value) return false;
		
		if($variable == 'subject')
		{
			$site_title = Configure::read('Site.title');
			if($site_title)
			{
				$value = $site_title. ' - '. $value;
			}
		}
		
		$this->{$variable} = $value;
		return true;
	}
	
	public function Email_setHeader(Model $Model, $variable = false, $value = false)
	{
	/*
	 * Used to set the values for an email to be sent.
	 */
		if(!$variable) return false;
		if(!$value) return false;
		
		$this->headers[$variable] = $value;
		return true;
	}
	
	public function Email_config_vars(Model $Model, $config_vars = null)
	{
		$this->config_vars = $config_vars;
	}
}