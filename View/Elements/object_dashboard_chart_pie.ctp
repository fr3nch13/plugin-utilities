<?php
$title = (isset($title)?$title:__('Details'));
$data = (isset($data)?$data:array());
$options = (isset($options)?$options:array());
$class = (isset($options['class'])?$options['class']:'dashboard-chart dashboard-chart-pie');

$defaults = array(
	'includeImage' => true,
	'is3D' => true,
	'width' => 390,
	'height' => 200,
	'fontSize' => 9,
//	'pieSliceText' => 'none',
	'legend' => array('position' => 'labeled'),
//	'chartArea' => array('top' => 6, 'height' => '93%', 'width' => '93%'),
	
	'chartArea' => array('top' => 10, 'left' => 10, 'width' => '95%', 'height' => '99%'),
	'sliceVisibilityThreshold' => '0',
	'tooltip' => array('trigger' => 'selection'),
	'slices' => array(),
);
$i = 0;

if(!isset($options['title']) and $title)
	$options['title'] = $title;

$options = array_merge($defaults, $options);

$content = $this->GoogleChart->displayPieChart($options, $data);
echo $this->Html->tag('div', $content, array('class' => $class));
echo $this->Html->tag('div', '', array('class' => 'clearb'));
