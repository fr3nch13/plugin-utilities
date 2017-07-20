<?php 
// File: plugins/utilities/View/Emails/html/failed_logins.ctp

$this->Html->setFull(true);

// content
$th = array(
	'LoginHistory.email' => array('content' => __('Email'), 'options' => array('sort' => 'LoginHistory.email')),
	'User.name' => array('content' => __('User'), 'options' => array('sort' => 'User.name')),
	'LoginHistory.ipaddress' => array('content' => __('Ip Address'), 'options' => array('sort' => 'LoginHistory.ipaddress')),
	'LoginHistory.user_agent' => array('content' => __('User Agent'), 'options' => array('sort' => 'LoginHistory.user_agent')),
	'LoginHistory.timestamp' => array('content' => __('Time'), 'options' => array('sort' => 'LoginHistory.timestamp')),
);

$td = array();
foreach ($failed_logins as $i => $failed_login)
{
	$email = $failed_login['LoginHistory']['email'];
	$user = '&nbsp';
	if($failed_login['LoginHistory']['user_id'] > 0)
	{
		$email = $this->Html->link($email, 'mailto:'. $email);
		$tmp = array('User' => $failed_login['User']);
		$user = $this->Html->link($tmp['User']['name'], array('controller' => 'users', 'action' => 'view', $tmp['User']['id']), array('escape' => false, 'class' => 'avatar_tiny'));  
	}
	$td[$i] = array(
		$email,
		$user,
		$failed_login['LoginHistory']['ipaddress'],
		$failed_login['LoginHistory']['user_agent'],
		$this->Wrap->niceTime($failed_login['LoginHistory']['timestamp']),
	);
}

echo $this->element('Utilities.email_html_index', array(
	'page_title' => __('Failed Logins'),
	'th' => $th,
	'td' => $td,
));