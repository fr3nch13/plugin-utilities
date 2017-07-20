<?php 
/**
 * File: /app/View/Elements/page_view.ctp
 * 
 * Use: provide a consistant layout for details pages.
 *
 * Usage: echo $this->element('page_view', array([options]));
 */

/////// Default settings.

// main title of the page
$page_title = (isset($page_title)?$page_title:'');

$page_subtitle = (isset($page_subtitle)?$page_subtitle:'');

// options specific to this page
$page_options = (isset($page_options)?$page_options:false); 

// holds the title for the details
// format: array('[id]' => array('title' => __('[block_title]'), 'options' => '[block_options]', 'details' => array('name' => __('Name'), 'value' => '[value]')))
$details_blocks = (isset($details_blocks)?$details_blocks:array());

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
	<div class="<?php echo $class_left; ?>">
	<?php 
	$details_blocks = array();
	
	// backwards compatible
	if($details and is_array($details)) 
	{
		$details_blocks[] = array(
			'title' => (isset($details_title)?$details_title:false),
			'details' => (isset($details)?$details:array()),
			'options' => (isset($details_options)?$details_options:array()),
		);
	}
	if($details_middle)
	{
		$details_blocks[] = array(
			'title' => (isset($details_middle_title)?$details_middle_title:false),
			'details' => (isset($details_middle)?$details_middle:array()),
			'options' => (isset($details_middle_options)?$details_middle_options:array()),
		);
	}
	
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
			$block_html = $this->element('Utilities.details', array(
				'title' => (isset($details_blocks['title'])?$details_blocks['title']:false),
				'details' => (isset($details_blocks['details'])?$details_blocks['details']:array()),
				'options' => (isset($details_blocks['options'])?$details_blocks['options']:array()),
			));
			$block_div = $this->Html->tag('div', $block_html, array('class' => 'detail_block'));
			echo $this->Html->tag('td', $block_div, array('class' => 'detail_block'));
		}
		// one row of columns
		elseif($details_blocks_depth == 4)
		{
			foreach ($details_blocks as $details_i => $details_block)
			{
				$block_html = $this->element('Utilities.details', array(
					'title' => (isset($details_block['title'])?$details_block['title']:false),
					'details' => (isset($details_block['details'])?$details_block['details']:array()),
					'options' => (isset($details_block['options'])?$details_block['options']:array()),
				));
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
					$block_html = $this->element('Utilities.details', array(
						'title' => (isset($details_block['title'])?$details_block['title']:false),
						'details' => (isset($details_block['details'])?$details_block['details']:array()),
						'options' => (isset($details_block['options'])?$details_block['options']:array()),
					));
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
	</div>
	
	<div class="<?php echo $class_right; ?>">
	
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