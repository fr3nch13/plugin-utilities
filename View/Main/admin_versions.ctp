<?php 
// File: Plugin/Utilities/View/Main/admin_versions.ctp


$page_options = array();

// content
$th = array(
	'name' => array('content' => __('Name') ),
	'version' => array('content' => __('Version') ),
	'time' => array('content' => __('Timestamp') ),
	'homepage' => array('content' => __('Homepage') ),
	'description' => array('content' => __('Description') ),
	'keywords' => array('content' => __('Keywords') ),
);

$td = array();
foreach ($versions as $i => $version)
{
	$td[] = array(
		array(
			$version['name'],
			array('class' => 'nowrap')
		),
		$version['version'],
		$this->Wrap->niceTime($version['time']),
		$this->Html->link($version['homepage'], $version['homepage'], array('target' => Inflector::slug($version['name']))),
		$version['description'],
		implode(', ', $version['keywords']),
	);
}

echo $this->element('Utilities.page_index', array(
	'page_title' => __('Installed Packages'),
	'page_options' => $page_options,
	'th' => $th,
	'td' => $td,
	'use_search' => false,
	'use_pagination' => false,
));

pr($_SERVER);