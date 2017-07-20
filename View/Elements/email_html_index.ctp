<?php 
/**
 * File: /app/View/Elements/email_html_index.ctp
 * 
 * Use: provide a consistant layout for index pages.
 *
 * Usage: echo $this->element('page_index', array([options]));
 */

/////// Default settings
$instructions = (isset($instructions)?$instructions:'');

// main title of the page
$page_title = (isset($page_title)?$page_title:'');

$page_subtitle = (isset($page_subtitle)?$page_subtitle:'');

// description of the current page
$page_description = (isset($page_description)?$page_description:false);

// options specific to this page
$page_options = (isset($page_options)?$page_options:false); 

// hold the array of table headers
// format: $th['column_key'] = content;
$th = (isset($th)?$th:array()); 

// holds the data
// format: $td[i++]['column_key'] = content
// for full format, 
// see: http://book.cakephp.org/2.0/en/core-libraries/helpers/html.html#HtmlHelper::tableCells
$td = (isset($td)?$td:array()); 

$no_records = (isset($no_records)?$no_records:__('No records were found.'));

$before_table = ((isset($before_table) and $before_table)?$before_table:false);
$after_table = ((isset($after_table) and $after_table)?$after_table:false);

////////////////////////////////////////

if($page_title) $this->set('title_for_layout', $page_title);
?>
<div class="top">
	<h1><?php echo $page_title; ?></h1>
	
	<?php if($page_subtitle): ?>
	<h2><?php echo $page_subtitle; ?></h2>
	<?php endif; ?>
	
	<?php if($instructions): ?>
	<div class="page_description">
		<p><?php echo $instructions; ?></p>
	</div>
	<div class="clearb"> </div>
	<?php endif; ?>
	
	<?php if($page_description): ?>
	<div class="page_description">
		<p><?php echo $page_description; ?></p>
	</div>
	<div class="clearb"> </div>
	<?php endif; ?>

	<?php if($page_options and is_array($page_options)): ?>
		<div class="page_options">
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
			echo __('Options: %s', implode(' - ', $options_contents));
			?>
		</div>
	<?php endif; ?>
	<div class="clearb"> </div>
</div>

<div class="center">
	<?php 
if($before_table) echo $before_table;

echo $this->element('Utilities.table', array(
	'th' => $th,
	'td' => $td,
	'table_stripped' => true,
)); 

if($after_table) echo $after_table;
?>
</div>