<?php 
// File: plugins/utilities/View/Emails/text/failed_logins.ctp

$this->Html->setFull(true);
$this->Html->asText(true);

// content
$th = array(
	'LoginHistory.email' => array('content' => __('Email'), 'options' => array('sort' => 'LoginHistory.email')),
	'LoginHistory.ipaddress' => array('content' => __('Ip Address'), 'options' => array('sort' => 'LoginHistory.ipaddress')),
	'LoginHistory.user_agent' => array('content' => __('User Agent'), 'options' => array('sort' => 'LoginHistory.user_agent')),
	'LoginHistory.timestamp' => array('content' => __('Time'), 'options' => array('sort' => 'LoginHistory.timestamp')),
);

$td = array();
foreach ($failed_logins as $i => $failed_login)
{
	$td[$i] = array(
		$failed_login['LoginHistory']['email'],
		$failed_login['LoginHistory']['ipaddress'],
		$failed_login['LoginHistory']['user_agent'],
		$this->Wrap->niceTime($failed_login['LoginHistory']['timestamp']),
	);
}

$email_address = 'example@example.com';
$description = array();
$description[] = __('Please note the following failed login attempts for this Email Address/Account below.');
$description[] = __('If this is your doing, you can disregard this security concern.');
$description[] = __('If you need to change your password, or this activity is a security concern, please email %s.', $email_address);

echo $this->element('Utilities.email_text_index', array(
	'page_title' => __('Failed Logins'),
	'page_description' => implode("\n", $description),
	'th' => $th,
	'td' => $td,
));