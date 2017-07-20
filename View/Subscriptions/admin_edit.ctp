<?php

$inputs = [];
$inputs['id'] = [
	'type' => 'hidden',
];
$inputs['user_id'] = [
	'div' => ['class' => 'half'],
	'label' => __('User'),
];
$inputs['clear2'] = ['type' => 'clear'];
$inputs['name'] = [
	'div' => ['class' => 'third'],
	'label' => __('Subscription Name'),
];
$inputs['uri'] = [
	'div' => ['class' => 'twothird'],
	'label' => __('Subscription URL'),
	'type' => 'text',
];
$inputs['clear1'] = ['type' => 'clear'];

$inputs['email_time'] = [
	'div' => ['class' => 'third'],
	'label' => __('What time of day the Subscription Email should be sent?'),
	'default' => '10',
	'options' => $this->Wrap->niceHour(false),
	'class' => ['not-chosen'],
];

$days = ['mon' => __('Mon'), 'tue' => __('Tues'), 'wed' => __('Wed'), 'thu' => __('Thur'), 'fri' => __('Fri'), 'sat' => __('Sat'), 'sun' => __('Sun')];
$dayInputs = [
	$this->Html->tag('label', __('Days of the week.')),
];
foreach($days as $k => $label)
{
	$dayInputs[] = $this->Form->input('Subscription.'.$k, [
		'label' => $label,
		'type' => 'toggle',
		'div' => ['style' => 'display: inline; float: left; clear: none;'],
	]);
}
$inputs['days'] = [
	'type' => 'raw',
	'value' => $this->Html->tag('div', implode("\n", $dayInputs), ['class' => 'twothird']),
];

echo $this->element('Utilities.page_form_basic', [
	'inputs' => $inputs,
]);