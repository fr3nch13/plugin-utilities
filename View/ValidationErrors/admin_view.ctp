<?php 
// File: plugins/utilities/View/ValidationErrors/view.ctp

$page_options = array();
$page_options[] = $this->Form->postLink(__('Delete'),array('action' => 'delete', $validation_error['ValidationError']['id']), array('confirm' => 'Are you sure?'));


$details = array();
$details[] = array('name' => __('User'), 'value' => $this->Html->link($validation_error['User']['name'], array('controller' => 'users', 'action' => 'view', $validation_error['User']['id'], 'plugin' => false)));
$details[] = array('name' => __('Model Alias'), 'value' => $validation_error['ValidationError']['model_alias']);
$details[] = array('name' => __('Model Name'), 'value' => $validation_error['ValidationError']['model_name']);
$details[] = array('name' => __('Model ID'), 'value' => $validation_error['ValidationError']['model_id']);
$details[] = array('name' => __('Url'), 'value' => $this->Html->link($validation_error['ValidationError']['path'], $validation_error['ValidationError']['path']));
$details[] = array('name' => __('Created'), 'value' => $this->Wrap->niceTime($validation_error['ValidationError']['created']));


$stats = array(
);


$tabs = array(
);

$tabs[] = array(
	'key' => 'errors',
	'title' => __('Errors'),
	'content' => $this->Wrap->descView(print_r(json_decode($validation_error['ValidationError']['errors']), true)),
);

$tabs[] = array(
	'key' => 'data',
	'title' => __('Data'),
	'content' => $this->Wrap->descView(print_r(json_decode($validation_error['ValidationError']['data']), true)),
);

echo $this->element('Utilities.page_view', array(
	'page_title' => __('%s - %s: %s', __('Validation Error'), $validation_error['ValidationError']['model_alias'], $validation_error['ValidationError']['model_id']),
	'page_options' => $page_options,
	'details_title' => __('Details'),
	'details' => $details,
	'stats' => $stats,
	'tabs_id' => 'tabs',
	'tabs' => $tabs,
));