<?php
$title = (isset($title)?$title:__('Details'));
$data = (isset($data)?$data:array());
$options = (isset($options)?$options:array());
$class = (isset($options['class'])?$options['class']:'chart chart-line');

if(!isset($options['showToggle']))
	$options['showToggle'] = false;

$defaults = array(
	'includeImage' => false,
	'fontSize' => 9,
//	'legend' => array('position' => 'bottom'),
	'chartArea' => array('height' => '80%', 'width' => '88%', 'top' => 5, 'left' => 30),
	'orientation' => 'horizontal',
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
		'class' => 'chart-line-toggle',
		'div' => false,
		'label' => false,
	));
}

echo $this->Html->tag('div', $lineChartContent, array('class' => $class));
	
	if($options['showToggle'])
		echo $this->Html->tag('div', $select, array('class' => 'chart-line-options no-print'));
echo $this->Html->tag('div', '', array('class' => 'clearb'));
