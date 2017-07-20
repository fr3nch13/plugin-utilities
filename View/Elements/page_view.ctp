<?php 
/**
 * File: /app/View/Elements/page_view.ctp
 * 
 * Use: provide a consistant layout for details pages.
 *
 * Usage: echo $this->element('page_view', array([options]));
 */

/////// Default settings.
$this->set('trackReferer', true);

// main title of the page
$page_title = (isset($page_title)?$page_title:'');
$page_subtitle = (isset($page_subtitle)?$page_subtitle:'');
$page_subtitle2 = (isset($page_subtitle2)?$page_subtitle2:false);
$page_options_title = (isset($page_options_title)?$page_options_title:__('Options'));
$page_options = (isset($page_options)?$page_options:array());
$page_options_title2 = (isset($page_options_title2)?$page_options_title2:__('More Options'));
$page_options2 = (isset($page_options2)?$page_options2:array());
$page_options_html = (isset($page_options_html)?$page_options_html:array());
$page_description = (isset($page_description)?$page_description:false);
$use_search = (isset($use_search)?$use_search:true);
$use_filter = (isset($use_filter)?$use_filter:false);
$use_export = (isset($use_export)?$use_export:false);
$search_title_query = (isset($search_title_query)?$search_title_query:false);
$search_title_fields = (isset($search_title_fields)?$search_title_fields:false);
$subscribable = (isset($subscribable)?$subscribable:false);

/// displaying a status bar
$status_steps = (isset($status_steps)?$status_steps:array());

// hold the array of details
// format: $details[] = array('name' => __('Name'), 'value' => [value])),
$details = (isset($details)?$details:false);

// hold extra options for the details element
$details_options = (isset($details_options)?$details_options:false);

// hold the array of details
// format: $details_middle[] = array('name' => __('Name'), 'value' => [value])),
$details_middle = (isset($details_middle)?$details_middle:false);

// hold extra options for the details element
$details_middle_options = (isset($details_middle_options)?$details_middle_options:false);

// hold the array of stats on this object
// format: $stats[] = array('name' => __('Name'), 'value' => [value], 'link' => [link to info])), // value should be a number
$stats = (isset($stats)?$stats:false);

$stats_title = (isset($stats_title)?$stats_title:__('Stats'));

// hold extra options for the stats element
$stats_options = (isset($stats_options)?$stats_options:false);

// hold the array of tabs on this object
// format: $tabs[] = array('key' => 'key', 'name' => __('Name'), 'content' => [content], 'url' => [url to content for ajax])), // value should be a number
$tabs = (isset($tabs)?$tabs:false);

// hold extra options for the tabs element
$tabs_options = (isset($tabs_options)?$tabs_options:false);


////////////////////////////////////////

echo $this->element('Utilities.object_top', array(
	'page_title' => $page_title,
	'page_subtitle' => $page_subtitle,
	'page_subtitle2' => $page_subtitle2,
	'page_description' => $page_description,
	'page_options_title' => $page_options_title,
	'page_options' => $page_options,
	'page_options_title2' => $page_options_title2,
	'page_options2' => $page_options2,
	'page_options_html' => $page_options_html,
	'use_export' => $use_export,
	'use_search' => $use_search,
	'use_filter' => $use_filter,
	'search_title_query' => $search_title_query,
	'search_title_fields' => $search_title_fields,
	'subscribable' => $subscribable,
));
?>

<?php
$class_left = 'left';
$class_right = 'right';
$class_middle = 'middle_3';
if($details_middle)
{
	$class_left = 'left_3';
	$class_right = 'right_3';
}
	
?>
<div class="center">
	
	<?php if($status_steps): ?>
	<div class="status_steps"><?php echo $this->element('Utilities.status_bar', array('steps' => $status_steps)); ?></div>
	<?php endif; ?>
	
	<?php if($details and is_array($details)): 
		$details_id = 'object-details-'. rand(0, 1000);
	?>
	<div class="<?php echo $class_left; ?> object-details" id="<?= $details_id ?>">
	<?php 
		$details_html = $this->element('Utilities.details', array(
			'details' => $details,
			'options' => $details_options,
		)); 
		echo $this->Html->tag('div', $details_html, array('class' => 'details-content'));
	?>
	</div>
	<?php endif; ?>
	
	<?php if($details_middle): 
		$details_id = 'object-details-'. rand(0, 1000);
	?>
	<div class="<?php echo $class_middle; ?> object-details" id="<?= $details_id ?>">
	<?php 
	if($details_middle and is_array($details_middle))
	{
		$details_html = $this->element('Utilities.details', array(
			'details' => $details_middle,
			'options' => $details_middle_options,
		)); 
		echo $this->Html->tag('div', $details_html, array('class' => 'details-content'));
	}
	?>
	</div>
<?php if(!isset($isSubscription) or !$isSubscription) : ?>
<script type="text/javascript">
//<![CDATA[
$(document).ready(function ()
{
	var detailsOptions = {};
	
	$('div#<?php echo $details_id; ?>').objectDetails(detailsOptions);
});
//]]>
</script>
<?php endif; ?>
	<?php endif; ?>
	
	<div class="<?php if($details and is_array($details)) echo $class_right; ?>">
	<?php 
	// create the load order so the js buffer is in a different order.
	// e.g. tabs first
	$tabs_html = $stats_html = '';
	if($tabs and is_array($tabs))
	{
		$tabs_html = $this->element('Utilities.object_tabs', array(
			'tabs' => $tabs,
			'options' => $tabs_options,
		));
	}
	if($stats and is_array($stats)) 
	{
		$stats_html = $this->element('Utilities.stats', array(
			'stats' => $stats,
			'title' => $stats_title,
			'options' => $stats_options,
		)); 
	}
	?>
	
	<?php 
	if($stats and is_array($stats)) 
		echo $stats_html; 
	?>
	</div>
	<div class="clearb"> </div>
	<div class="full">
		
	<?php 
	if($tabs and is_array($tabs))  
		echo $tabs_html; 
	?>
	</div>
</div>
<?php

// include any scripts that would be created for things like pagination
if(!isset($isSubscription) or !$isSubscription)
{
	if(isset($this->Avatar))
	{
		echo $this->Avatar->avatarPreview();
	}
}
echo $this->Js->writeBuffer();