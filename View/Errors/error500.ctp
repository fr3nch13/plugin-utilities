<?php

$page_options = array();

$location = $this->request->here;
if(isset($_SERVER['REQUEST_URI']))
	$location = $_SERVER['REQUEST_URI'];

$this->start('error_content'); ?>
<p class="error-location"><?php echo __('Location: %s', $location); ?></p>
<p class="error-method"><?php echo __('Method: %s', $this->request->method()); ?></p>
<p class="error-post-uuid"><?php echo __('Post UUID: %s', CakeSession::read('postId')); CakeSession::write('postId', false); ?></p>
<p class="error">
	<strong><?php echo __d('cake', 'Error'); ?>: </strong>
	<?php echo __d('cake', 'An Internal Error Has Occurred.'); ?>
</p>
<?php
if (Configure::read('debug') > 0 )
{
	echo $this->element('exception_stack_trace');
}

$this->end();

echo $this->element('Utilities.page_generic', array(
	'page_title' => __('Error: %s', $this->response->statusCode()),
	'page_subtitle' => $name,
	'page_options' => $page_options,
	'page_content' => $this->fetch('error_content'),
));

//get it to send an error message via email.
// I know this is a weird place to do it, but it seems the only real good place for it.
App::uses('CakeEmail', 'Network/Email');
$Email = new CakeEmail();

$from = array('example@example.com' => 'Portals');

if(class_exists('AuthComponent') and AuthComponent::user('email'))
	$from = array(AuthComponent::user('email') => AuthComponent::user('name'));

$Email->from($from);
$Email->to('example@example.com');
$Email->subject(__('500 Error: - Code: %s - Msg: %s', $this->response->statusCode(), $name));
$Email->emailFormat('html');
$Email->send($this->fetch('error_content'));