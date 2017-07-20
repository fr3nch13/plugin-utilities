<?php 
/**
 * File: /app/View/Elements/page_index.ctp
 * 
 * Use: provide a consistant layout for index pages.
 *
 * Usage: echo $this->element('page_index', array([options]));
 */

/////// Default settings.
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


$sep = (isset($sep)?$sep:str_repeat('-', 80));

?>
<?php echo $page_title; ?>
<?php if($page_subtitle): ?>
    
<?php echo $page_subtitle; ?>
    
<?php echo $sep; ?>
    
<?php endif; ?>
<?php if($instructions): ?>
    
<?php echo __('Instructions'); ?>
    
<?php echo $instructions; ?>
    
<?php echo $sep; ?>
    
<?php endif; ?>
    
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
if($before_table) echo $before_table;

echo $this->element('Utilities.email_text_table', array(
	'th' => $th,
	'td' => $td,
)); 

if($after_table) echo $after_table;
?>
    