<?php 
// File: app/View/Proctimes/view.ctp

$page_options = array();
$page_options[] = $this->Form->postLink(__('Delete'),array('action' => 'delete', $proctime['Proctime']['id']), array('confirm' => 'Are you sure?'));


$details = array();
$details[] = array('name' => __('Processing Time'), 'value' => $proctime['Proctime']['proctime']);
$details[] = array('name' => __('Process ID'), 'value' => $proctime['Proctime']['pid']);
$details[] = array('name' => __('Url'), 'value' => $this->Html->link($proctime['Proctime']['url'], $proctime['Proctime']['url']));
$details[] = array('name' => __('User'), 'value' => $this->Html->link($proctime['User']['name'], array('controller' => 'users', 'action' => 'view', $proctime['User']['id'], 'plugin' => false)));
$details[] = array('name' => __('Created'), 'value' => $this->Wrap->niceTime($proctime['Proctime']['created']));


$stats = array(
	array(
		'id' => 'ProctimeSqlStat',
		'name' => __('SQL Stats'), 
		'value' => $proctime['Proctime']['counts']['ProctimeSqlStat.all'], 
		'tab' => array('tabs', '1'), // the tab to display
	),
	array(
		'id' => 'ProctimeQuery',
		'name' => __('Queries'), 
		'value' => $proctime['Proctime']['counts']['ProctimeQuery.all'], 
		'tab' => array('tabs', '2'), // the tab to display
	),
);


$tabs = array(
	array(
		'key' => 'ProctimeSqlStat',
		'title' => __('Related %s', __('SQL Stats')), 
		'url' => array('controller' => 'proctime_sql_stats', 'action' => 'proctime', $proctime['Proctime']['id']),
	),
	array(
		'key' => 'ProctimeQuery',
		'title' => __('Related %s', __('Queries')), 
		'url' => array('controller' => 'proctime_queries', 'action' => 'proctime', $proctime['Proctime']['id']),
	),
);

echo $this->element('Utilities.page_view', array(
	'page_title' => __('Process time: %s on %s', $proctime['Proctime']['proctime'], $this->Wrap->niceTime($proctime['Proctime']['created'])),
	'page_options' => $page_options,
	'details_title' => __('Details'),
	'details' => $details,
	'stats' => $stats,
	'tabs_id' => 'tabs',
	'tabs' => $tabs,
));