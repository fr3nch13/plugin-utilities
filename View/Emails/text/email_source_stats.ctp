<?php 
// File: plugins/utilities/View/Emails/text/email_source_stats.ctp

$this->Html->setFull(true);
$this->Html->asText(true);

$page_options = array(
//	$this->Html->link(__('View these %s', __('Source Stats')), array('controller' => 'source_stats', 'action' => 'index', 'admin' => true, 'plugin' => 'utilities')),
);

foreach ($source_stats as $source_name => $source_stat)
{
	$th = array();
	$td = array();
	
	foreach($source_stat as $key => $value)
	{
		$th[$key] = Inflector::humanize($key);
		$td[0][$key] = $value;
	}
	
	echo $this->element('Utilities.email_text_index', array(
		'page_title' => __('Source: %s', $source_name),
		'page_options' => $page_options,
		'th' => $th,
		'td' => $td,
	));
}