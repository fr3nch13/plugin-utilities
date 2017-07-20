<?php
$title = (isset($title)?$title:__('Details'));
$data = (isset($data)?$data:array());
$options = (isset($options)?$options:array());
$class = (isset($options['class'])?$options['class']:'dashboard-chart dashboard-chart-line');

$defaults = array(
	'includeImage' => true,
	'width' => 390,
	'height' => 400,
	'fontSize' => 9,
//	'legend' => array('position' => 'bottom'),
	'chartArea' => array('height' => '370', 'width' => '50%', 'top' => 1, 'left' => 55),
	'orientation' => 'vertical',
	'slices' => array(),
	'allowHide' => true,
);
$i = 0;

if(!isset($options['title']) and $title)
	$options['title'] = $title;

$options = array_merge($defaults, $options);
$lineChartContent = $this->GoogleChart->displayLineChart($options, $data);

// allow the ability to show/hide the lines

if(isset($data['legend']))
{
	$selectOptions = $data['legend'];
	array_shift($selectOptions); // remove the title/description of the items
	array_unshift($selectOptions, __('Show/Hide Lines'));
	$select = $this->Form->input('line_toggle', array(
		'options' => $selectOptions,
		'class' => 'dashboard-chart-line-toggle',
		'div' => false,
		'label' => false,
	));
	echo $this->Html->tag('div', $select, array('class' => 'dashboard-chart-line-options no-print'));
}

echo $this->Html->tag('div', $lineChartContent, array('class' => $class));
echo $this->Html->tag('div', '', array('class' => 'clearb'));
