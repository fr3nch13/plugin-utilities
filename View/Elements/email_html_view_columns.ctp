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

?>
<div class="top">
	<h1><?php echo $page_title; ?></h1>
	
	<?php if($page_subtitle): ?>
	<h2><?php echo $page_subtitle; ?></h2>
	<?php endif; ?>
	
	<?php if($page_description): ?>
	<div class="page_description">
		<p><?php echo $page_description; ?></p>
	</div>
	<div class="clearb"> </div>
	<?php endif; ?>
	
	<?php if($page_options and is_array($page_options)): ?>
		<div class="page_options">
			<ul>
			<?php foreach ($page_options as $page_option)
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
	    		echo $this->Html->tag('li', $content, $options);
	    	}
	    	?>
			</ul>
		</div>
	<?php endif; ?>
	<div class="clearb"> </div>
</div>
<div class="center">
	<?php 
	if($details_blocks and is_array($details_blocks))
	{
		$details_blocks_depth = $this->Common->arrayDepth($details_blocks);
	?>
	<table class="details_blocks" rowspan="0" colspan="0" >
		<tr>
	<?php 
		// only one column defined
		if($details_blocks_depth == 3)
		{
			if(is_array($details_blocks))
			{
				$block_html = $this->element('Utilities.email_html_details', array(
					'title' => (isset($details_blocks['title'])?$details_blocks['title']:false),
					'details' => (isset($details_blocks['details'])?$details_blocks['details']:array()),
					'options' => (isset($details_blocks['options'])?$details_blocks['options']:array()),
				));
			}
			else
			{
				$block_html = $details_block;
			}
			$block_div = $this->Html->tag('div', $block_html, array('class' => 'detail_block'));
			echo $this->Html->tag('td', $block_div, array('class' => 'detail_block'));
		}
		// one row of columns
		elseif($details_blocks_depth == 4)
		{
			foreach ($details_blocks as $details_i => $details_block)
			{
				if(is_array($details_block))
				{
					$block_html = $this->element('Utilities.details', array(
						'title' => (isset($details_block['title'])?$details_block['title']:false),
						'details' => (isset($details_block['details'])?$details_block['details']:array()),
						'options' => (isset($details_block['options'])?$details_block['options']:array()),
					));
				}
				else
				{
					$block_html = $details_block;
				}
				$block_div = $this->Html->tag('div', $block_html, array('class' => 'detail_block'));
				echo $this->Html->tag('td', $block_div, array('class' => 'detail_block_cell'));
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
					if(is_array($details_block))
					{
						$block_html = $this->element('Utilities.details', array(
							'title' => (isset($details_block['title'])?$details_block['title']:false),
							'details' => (isset($details_block['details'])?$details_block['details']:array()),
							'options' => (isset($details_block['options'])?$details_block['options']:array()),
						));
					}
					else
					{
						$block_html = $details_block;
					}
					$block_div = $this->Html->tag('div', $block_html, array('class' => 'detail_block'));
					$columns[] = $this->Html->tag('td', $block_div, array('class' => 'detail_block_cell'));
				}
				echo $this->Html->tag('tr', implode("\n", $columns), array('class' => 'detail_block_row'));
			}
		}
	?>
		</tr>
	</table>
	<?php 
	}
	?>
	<div class="clearb"> </div>
	<hr />
	<div class="">
	<?php 
	if($stats and is_array($stats)) 
		echo $this->element('Utilities.stats', array(
			'stats' => $stats,
			'options' => $stats_options,
		)); 
	?>
	</div>
	<div class="clearb"> </div>
	<div class="full">
		
	<?php 
	if($tabs and is_array($tabs)) 
		echo $this->element('Utilities.object_tabs', array(
			'tabs' => $tabs,
			'options' => $tabs_options,
		)); 
	?>
	</div>
</div>