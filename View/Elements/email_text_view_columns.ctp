<?php 
/**
 * File: /plugins/utilities/View/Elements/email_html_view_columns.ctp
 * 
 * Use: provide a consistant layout for details pages.
 *
 * Usage: echo $this->element('email_html_view_columns', array([options]));
 */

/////// Default settings.

// main title of the page
$page_title = (isset($page_title)?$page_title:'');

if(isset($this->params['admin']) and $this->params['admin'])
{
	$page_title = __('Admin'). ' - '. $page_title;
}

$page_subtitle = (isset($page_subtitle)?$page_subtitle:'');

$page_description = (isset($page_description)?$page_description:false);

// options specific to this page
$page_options = (isset($page_options)?$page_options:false); 

// holds the title for the details
// format: array('[id]' => array('title' => __('[block_title]'), 'options' => '[block_options]', 'details' => array('name' => __('Name'), 'value' => '[value]')))
$details_blocks = (isset($details_blocks)?$details_blocks:array());

// hold the array of stats on this object
// format: $stats[] = array('name' => __('Name'), 'value' => [value], 'link' => [link to info])), // value should be a number
$stats = (isset($stats)?$stats:false);

// hold extra options for the stats element
$stats_options = (isset($stats_options)?$stats_options:false);

// hold the array of tabs on this object
// format: $tabs[] = array('key' => 'key', 'name' => __('Name'), 'content' => [content], 'url' => [url to content for ajax])), // value should be a number
$tabs = (isset($tabs)?$tabs:false);

// hold extra options for the tabs element
$tabs_options = (isset($tabs_options)?$tabs_options:false);

////////////////////////////////////////

if($page_title) $this->set('title_for_layout', $page_title);

$sep = (isset($sep)?$sep:str_repeat('-', 80));

?>
<?php echo $page_title; ?>
    
<?php if($page_subtitle): ?>
    
<?php echo $page_subtitle; ?>
    
<?php endif; ?>
    
<?php echo $sep; ?>
    
<?php if($page_options and is_array($page_options)): ?>
    
<?php 
$options_contents = array();
foreach ($page_options as $page_option)
{
	$options = false;
	$content = false;
	if(is_array($page_option))
	{
	if(isset($page_option['content'])) $content = $page_option['content'];
	if(isset($page_option['options'])) $options = $page_option['options'];
	}
	else
	{
	$content = $page_option;
	}
	$options_contents[] = $content;
}
?>
<?php echo __('Options:'); ?>
    
<?php echo implode("\n", $options_contents); ?>
    
<?php echo $sep; ?>
    
<?php endif; ?>
<?php if($page_description): ?>
    
<?php echo $page_description; ?>
    
<?php echo $sep; ?>
<?php endif; ?>
    
<?php 
if($details_blocks and is_array($details_blocks))
{
	$details_blocks_depth = $this->Common->arrayDepth($details_blocks);
		
	// only one column defined
	if($details_blocks_depth == 3)
	{
		if(is_array($details_blocks))
		{
			$block_text = $this->element('Utilities.email_text_details', array(
				'title' => (isset($details_blocks['title'])?$details_blocks['title']:false),
				'details' => (isset($details_blocks['details'])?$details_blocks['details']:array()),
				'options' => (isset($details_blocks['options'])?$details_blocks['options']:array()),
			));
		}
		else
		{
			$block_text = $details_block;
		}
?>
<?php echo $block_text; ?>
<?php
	}
	// one row of columns
	elseif($details_blocks_depth == 4)
	{
		foreach ($details_blocks as $details_i => $details_block)
		{
			if(is_array($details_blocks))
			{
				$block_text = $this->element('Utilities.email_text_details', array(
					'title' => (isset($details_block['title'])?$details_block['title']:false),
					'details' => (isset($details_block['details'])?$details_block['details']:array()),
					'options' => (isset($details_block['options'])?$details_block['options']:array()),
				));
			}
			else
			{
				$block_text = $details_block;
			}
?>
<?php echo $block_text; ?>
<?php
		}
	}
	// multiple rows of columns
	elseif($details_blocks_depth == 5)
	{
		foreach ($details_blocks as $details_i => $details_block_row)
		{
			$columns = array();
			foreach ($details_block_row as $details_i => $details_block)
			{
				if(is_array($details_blocks))
				{
					$block_text = $this->element('Utilities.email_text_details', array(
						'title' => (isset($details_block['title'])?$details_block['title']:false),
						'details' => (isset($details_block['details'])?$details_block['details']:array()),
						'options' => (isset($details_block['options'])?$details_block['options']:array()),
					));
				}
				else
				{
					$block_text = $details_block;
				}
?>
<?php echo $block_text; ?>
<?php
			}
		}
	}
?>
    
<?php
} // if($details_blocks and is_array($details_blocks))

if($stats and is_array($stats)) 
	echo $this->element('Utilities.email_text_stats', array(
		'stats' => $stats,
		'options' => $stats_options,
	)); 
?>
<?php 
if($tabs and is_array($tabs)) 
	echo $this->element('Utilities.tabs', array(
		'tabs' => $tabs,
		'options' => $tabs_options,
	)); 
?>