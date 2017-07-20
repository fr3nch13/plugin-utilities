<?php 

$page_options = [
	'add' => $this->Html->link(__('Add Subscription'), ['action' => 'add']),
];

$th = [
	'User.name' => ['content' => __('User'), 'options' => ['sort' => 'User.name']],
	'Subscription.name' => ['content' => __('Name'), 'options' => ['sort' => 'Subscription.name']],
	'Subscription.uri' => ['content' => __('URI'), 'options' => ['sort' => 'Subscription.uri']],
	'Subscription.active' => ['content' => __('Active'), 'options' => ['sort' => 'Subscription.active']],
	'Subscription.mon' => ['content' => __('Mon'), 'options' => ['sort' => 'Subscription.mon']],
	'Subscription.tue' => ['content' => __('Tues'), 'options' => ['sort' => 'Subscription.tue']],
	'Subscription.wed' => ['content' => __('Wed'), 'options' => ['sort' => 'Subscription.wed']],
	'Subscription.thu' => ['content' => __('Thurs'), 'options' => ['sort' => 'Subscription.thu']],
	'Subscription.fri' => ['content' => __('Fri'), 'options' => ['sort' => 'Subscription.fri']],
	'Subscription.sat' => ['content' => __('Sat'), 'options' => ['sort' => 'Subscription.sat']],
	'Subscription.sun' => ['content' => __('Sun'), 'options' => ['sort' => 'Subscription.sun']],
	'Subscription.email_time' => ['content' => __('Send Email At'), 'options' => ['sort' => 'Subscription.email_time']],
	'actions' => ['content' => __('Actions'), 'options' => ['class' => 'actions']],
];

$td = [];
foreach ($subscriptions as $i => $subscription)
{	
	$td[$i] = [
		$subscription['User']['name'],
		$this->Html->link($subscription['Subscription']['name'], Router::url('/'.$subscription['Subscription']['uri'], true)),
		$this->Html->link($subscription['Subscription']['uri'], Router::url('/'.$subscription['Subscription']['uri'], true)),
		$this->Html->toggleLink($subscription['Subscription'], 'active'),
		$this->Html->toggleLink($subscription['Subscription'], 'mon'),
		$this->Html->toggleLink($subscription['Subscription'], 'tue'),
		$this->Html->toggleLink($subscription['Subscription'], 'wed'),
		$this->Html->toggleLink($subscription['Subscription'], 'thu'),
		$this->Html->toggleLink($subscription['Subscription'], 'fri'),
		$this->Html->toggleLink($subscription['Subscription'], 'sat'),
		$this->Html->toggleLink($subscription['Subscription'], 'sun'),
		$this->Wrap->niceHour($subscription['Subscription']['email_time']),
		[
			$this->Html->link(__('View'), Router::url('/'.$subscription['Subscription']['uri'], true)).
			$this->Html->link(__('Edit'), ['action' => 'edit', $subscription['Subscription']['id']]).
			$this->Html->link(__('Delete'), ['action' => 'delete', $subscription['Subscription']['id']], ['confirm' => 'Are you sure?']), 
			['class' => 'actions'],
		],
	];
}

echo $this->element('Utilities.page_index', [
	'page_title' => __('Subscriptions'),
	'page_options' => $page_options,
	'th' => $th,
	'td' => $td,
]);