<?php 
// File: app/View/Proctimes/admin_index.ctp


$page_options = array();

// content
$th = array(
	'Proctime.proctime' => array('content' => __('Processing Time'), 'options' => array('sort' => 'Proctime.proctime')),
	'Proctime.pid' => array('content' => __('PID'), 'options' => array('sort' => 'Proctime.pid')),
	'Proctime.url' => array('content' => __('URL'), 'options' => array('sort' => 'Proctime.pid')),
	'User.name' => array('content' => __('User'), 'options' => array('sort' => 'User.name')),
	'Proctime.created' => array('content' => __('Created'), 'options' => array('sort' => 'Proctime.created')),
	'actions' => array('content' => __('Actions'), 'options' => array('class' => 'actions')),
	'multiselect' => true,
);

$td = array();
foreach ($proctimes as $i => $proctime)
{
	$actions = $this->Html->link(__('View'), array('action' => 'view', $proctime['Proctime']['id']));
	$actions .= $this->Html->link(__('Delete'),array('action' => 'delete', $proctime['Proctime']['id']),array('confirm' => 'Are you sure?'));
	
	
	$td[$i] = array(
		$this->Html->link($proctime['Proctime']['proctime'], array('controller' => 'proctimes', 'action' => 'view', $proctime['Proctime']['id'])),
		$proctime['Proctime']['pid'],
		$this->Html->link($proctime['Proctime']['url'], $proctime['Proctime']['url']),
		$this->Html->link($proctime['User']['name'], array('controller' => 'users', 'action' => 'view', $proctime['User']['id'], 'plugin' => false)),
		$this->Wrap->niceTime($proctime['Proctime']['created']),
		array(
			$actions,
			array('class' => 'actions'),
		),
		'multiselect' => $proctime['Proctime']['id'],
	);
}

$use_multiselect = false;

echo $this->element('Utilities.page_index', array(
	'page_title' => __('All %s', __('Process Times')),
	'page_options' => $page_options,
	'th' => $th,
	'td' => $td,
));