<?php

if($request_data_ajax['proctime'])
{
	echo $this->Html->tag('div', __('Initial Process time: %s', $request_data['proctime']));
	echo $this->Html->tag('div', __('This Process time: %s', $request_data_ajax['proctime']));
	echo $this->Html->tag('div', __('Total Process time: %s', ($request_data['proctime'] + $request_data_ajax['proctime'])));
}
else
{
	echo $this->Html->tag('div', __('Process time: %s', $request_data['proctime']));
}

$threshold = Configure::read('Proctime.threshold');
$second = __('Second');
if($threshold > 1)
{
	$second = __('%ss', $second);
}
elseif($threshold < 1 and $threshold > 0)
{
	$second = __('of 1 %s', $second);
}

echo $this->Html->tag('div', __('Threshold: %s %s', $threshold, $second));
if($request_data['proctime_id'])
{
	echo $this->Html->tag('div', __('Logged'));
}

$memory = memory_get_usage();
echo $this->Html->tag('div', __('Memory usage: %s (%s)', $this->Wrap->formatBytes($memory), $memory));
