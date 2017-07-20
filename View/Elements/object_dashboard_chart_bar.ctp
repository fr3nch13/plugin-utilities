<?php
$title = (isset($title)?$title:__('Details'));
$data = (isset($data)?$data:array());
$content = (isset($content)?$content:false);
$options = (isset($options)?$options:array());
$class = (isset($options['class'])?$options['class']:'dashboard-chart dashboard-chart-bar');

$defaults = array(
	'includeImage' => true,
	'width' => 390,
	'height' => 400,
	'fontSize' => 9,
//	'legend' => array('position' => 'bottom'),
	'chartArea' => array('height' => '390', 'width' => '50%', 'top' => 1, 'left' => 55),
	'orientation' => 'vertical',
	'slices' => array(),
	'allowHide' => true,
	'sliceVisibilityThreshold' => '1',
);
$i = 0;

if(!isset($options['title']) and $title)
	$options['title'] = $title;

$options = array_merge($defaults, $options);
$barChartContent = $this->GoogleChart->displayBarChart($options, $data);

echo $content;

echo $this->Html->tag('div', $barChartContent, array('class' => $class));
echo $this->Html->tag('div', '', array('class' => 'clearb'));
