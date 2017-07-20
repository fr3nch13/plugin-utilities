<?php 
// File: plugins/utilities/View/Emails/html/email_validation_errors.ctp

$this->Html->setFull(true);

$page_options = array(
	$this->Html->link(__('View these %s', __('Validation Errors')), array('controller' => 'validation_errors', 'action' => 'index', 'admin' => true, 'plugin' => 'utilities')),
);

// content
$th = array(
	'User.name' => array('content' => __('User'), 'options' => array('sort' => 'User.name')),
	'ValidationError.model_alias' => array('content' => __('Model'), 'options' => array('sort' => 'ValidationError.model_alias')),
	'ValidationError.model_id' => array('content' => __('Model ID'), 'options' => array('sort' => 'ValidationError.model_id')),
	'ValidationError.path' => array('content' => __('URL'), 'options' => array('sort' => 'ValidationError.path')),
	'ValidationError.created' => array('content' => __('Created'), 'options' => array('sort' => 'ValidationError.created')),
	'actions' => array('content' => __('Actions'), 'options' => array('class' => 'actions')),
);

$td = array();
foreach ($validation_errors as $i => $validation_error)
{
	$actions = $this->Html->link(__('View'), array('controller' => 'validation_errors', 'action' => 'view', $validation_error['ValidationError']['id'], 'admin' => true, 'plugin' => 'utilities'));
//	$actions .= $this->Html->link(__('Delete'),array('controller' => 'validation_errors', 'action' => 'delete', $validation_error['ValidationError']['id'], 'admin' => true, 'plugin' => 'utilities'),array('confirm' => 'Are you sure?'));
	
	
	$td[$i] = array(
		$this->Html->link($validation_error['User']['name'], array('controller' => 'users', 'action' => 'view', $validation_error['User']['id'], 'plugin' => false)),
		$validation_error['ValidationError']['model_alias'],
		$validation_error['ValidationError']['model_id'],
		$validation_error['ValidationError']['path'],
		$this->Wrap->niceTime($validation_error['ValidationError']['created']),
		array(
			$actions,
			array('class' => 'actions'),
		),
	);
}

echo $this->element('Utilities.email_html_index', array(
	'page_title' => __('All %s', __('Validation Errors')),
	'page_options' => $page_options,
	'th' => $th,
	'td' => $td,
));