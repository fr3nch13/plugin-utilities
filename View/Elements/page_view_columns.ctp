<?php 
/**
 * File: /app/View/Elements/page_compare.ctp
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
$details_options = (isset($details_options)?$details_options:array());
$subscribable = (isset($subscribable)?$subscribable:false);

/// displaying a status bar
$status_steps = (isset($status_steps)?$status_steps:array());

// holds the title for the details
// format: array('[id]' => array('title' => __('[block_title]'), 'options' => '[block_options]', 'details' => array('name' => __('Name'), 'value' => '[value]')))
$details_blocks = (isset($details_blocks)?$details_blocks:array());

// hold the array of stats on this object
// format: $stats[] = array('name' => __('Name'), 'value' => [value], 'link' => [link to info])), // value should be a number
$stats = (isset($stats)?$stats:array());

// hold extra options for the stats element
$stats_options = (isset($stats_options)?$stats_options:array());

// hold multiple stats
// format: $multi_stats = array(0 => array('title' => '[title]', 'options' => array(), 'stats' => array([format of stats above])) );
$multi_stats = (isset($multi_stats)?$multi_stats:false);

// hold the array of tabs on this object
// format: $tabs[] = array('key' => 'key', 'name' => __('Name'), 'content' => [content], 'url' => [url to content for ajax])), // value should be a number
$tabs = (isset($tabs)?$tabs:array());

// hold extra options for the tabs element
$tabs_options = (isset($tabs_options)?$tabs_options:array());


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

<div class="center">
	
	<?php if($status_steps): ?>
	<div class="status_steps"><?php echo $this->element('Utilities.status_bar', array('steps' => $status_steps)); ?></div>
	<?php endif; ?>
	
	<?php 
	if($details_blocks and is_array($details_blocks))
	{
		$details_blocks_depth = $this->Common->arrayDepth($details_blocks);
		
		$details_rows = array();
		
		// only one column defined
		if($details_blocks_depth == 3)
		{
			if(isset($details_blocks['details']))
			{
				$block_html = $this->element('Utilities.details', array(
					'title' => (isset($details_blocks['title'])?$details_blocks['title']:false),
					'details' => (isset($details_blocks['details'])?$details_blocks['details']:array()),
					'options' => (isset($details_blocks['options'])?$details_blocks['options']:array()),
				));
			}
			elseif(isset($details_blocks['content']))
			{
				$block_html = $this->element('Utilities.generic_block', array(
					'block_title' => (isset($details_blocks['title'])?$details_blocks['title']:false),
					'block_content' => (isset($details_blocks['content'])?$details_blocks['content']:false),
				));
			}
			$block_div = $this->Html->tag('div', $block_html, array('class' => 'detail_block'));
			$details_rows[] = $this->Html->tag('tr', $this->Html->tag('td', $block_div, array('class' => 'detail_block')));
		}
		// one row of columns
		elseif($details_blocks_depth == 4)
		{
			foreach ($details_blocks as $details_i => $details_block)
			{
				if(isset($details_block['details']))
				{
					$block_html = $this->element('Utilities.details', array(
						'title' => (isset($details_block['title'])?$details_block['title']:false),
						'details' => (isset($details_block['details'])?$details_block['details']:array()),
						'options' => (isset($details_block['options'])?$details_block['options']:array()),
					));
				}
				elseif(isset($details_block['content']))
				{
					$block_html = $this->element('Utilities.generic_block', array(
						'block_title' => (isset($details_block['title'])?$details_block['title']:false),
						'block_content' => (isset($details_block['content'])?$details_block['content']:false),
					));
				}
				$block_div = $this->Html->tag('div', $block_html, array('class' => 'detail_block'));
				$details_rows[] =  $this->Html->tag('tr', $this->Html->tag('td', $block_div, array('class' => 'detail_block_cell')));
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
					$block_html = '';
					if(isset($details_block['details']))
					{
						$block_html = $this->element('Utilities.details', array(
							'title' => (isset($details_block['title'])?$details_block['title']:false),
							'details' => (isset($details_block['details'])?$details_block['details']:array()),
							'options' => (isset($details_block['options'])?$details_block['options']:array()),
						));
					}
					elseif(isset($details_block['content']))
					{
						$block_html = $this->element('Utilities.generic_block', array(
							'block_title' => (isset($details_block['title'])?$details_block['title']:false),
							'block_content' => (isset($details_block['content'])?$details_block['content']:false),
						));
					}
					$block_div = $this->Html->tag('div', $block_html, array('class' => 'detail_block'));
					$columns[] = $this->Html->tag('td', $block_div, array('class' => 'detail_block_cell'));
				}
				$details_rows[] = $this->Html->tag('tr', implode("\n", $columns), array('class' => 'detail_block_row'));
			}
		}
		
		$details_id = 'object-details-'. rand(0, 1000);
		
		$details_table = $this->Html->tag('table', implode("\n", $details_rows), array('class' => 'details_blocks', 'cellspacing' => 0, 'cellpadding' => 0));
		$details_table = $this->Html->tag('div', $details_table, array('class' => 'details-content'));
		$details_table = $this->Html->tag('div', $details_table, array('class' => 'object-details', 'id' => $details_id));
		
		if($this->Html->getExt('txt'))
			echo strip_tags(implode("\n", $details_rows));
		else
			echo $details_table;
		if(!$this->Html->getExt('txt')):
?>
<?php if(!isset($isSubscription) or !$isSubscription) : ?>
<script type="text/javascript">
//<![CDATA[
$(document).ready(function ()
{
	var detailsOptions = <?php echo json_encode($details_options); ?>;
	
	$('div#<?php echo $details_id; ?>').objectDetails(detailsOptions);
});
//]]>
</script>
<?php endif; ?>
<?php
		endif; // if(!$this->Html->getExt('txt'))
	} // if($details_blocks and is_array($details_blocks))
	
?>
	<div class="">
<?php 
	// create the load order so the js buffer is in a different order.
	// e.g. tabs first
	$tabs_html = false;
	if($tabs and is_array($tabs))
	{
		$tabs_html = $this->element('Utilities.object_tabs', array(
			'tabs' => $tabs,
			'options' => $tabs_options,
			'stats' => $stats,
			'stats_options' => $stats_options,
		)); 
	}
	
	$stats_html = false;
	if($stats and is_array($stats)) 
	{
		$stats_html = $this->element('Utilities.stats', array(
			'stats' => $stats,
			'options' => $stats_options,
		)); 
	}
	
	$multi_stats_html = false;
	if(isset($multi_stats) and is_array($multi_stats))
	{
		foreach($multi_stats as $i => $multi_stat_group)
		{
			$multi_stat_group_title = (isset($multi_stat_group['title'])?$multi_stat_group['title']:__('Stats'));
			$multi_stat_group_options = (isset($multi_stat_group['options'])?$multi_stat_group['options']:array());
			$multi_stat_group_stats = (isset($multi_stat_group['stats'])?$multi_stat_group['stats']:array());
			
			$multi_stats_html .= $this->element('Utilities.stats', array(
				'title' => $multi_stat_group_title,
				'options' => $multi_stat_group_options,
				'stats' => $multi_stat_group_stats,
				'multi_split' => true,
			));
		}
	}
	
	if($stats_html) 
		echo $stats_html; 
		
	if($multi_stats_html) 
		echo $multi_stats_html; 
?>
	</div>
	<div class="clearb"> </div>
	<div class="full"><?php 
	if($tabs_html)  
		echo $tabs_html; 
	?></div>
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
if(!$this->Html->getExt('txt'))
	echo $this->Js->writeBuffer();
