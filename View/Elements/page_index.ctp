<?php 
/**
 * File: /app/View/Elements/page_index.ctp
 * 
 * Use: provide a consistant layout for index pages.
 *
 * Usage: echo $this->element('page_index', array([options]));
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
$th = (isset($th)?$th:array());
$td = (isset($td)?$td:array());
$use_search = (isset($use_search)?$use_search:true);
$use_filter = (isset($use_filter)?$use_filter:false);
$use_export = (isset($use_export)?$use_export:(count($td)?true:false));
$search_title_query = (isset($search_title_query)?$search_title_query:false);
$search_title_fields = (isset($search_title_fields)?$search_title_fields:false);
$subscribable = (isset($subscribable)?$subscribable:false);

// Model to use in the search
// the search plugin will figure it out if left false
$search_model = (isset($search_model)?$search_model:Inflector::singularize(Inflector::camelize($this->params->controller)));

// Term to use in the search placeholder
// the search plugin will figure it out if left false
$search_placeholder = (isset($search_placeholder)?$search_placeholder:$this->params->controller);

// multi select for selecting multiple objects
$use_multiselect = (isset($use_multiselect)?$use_multiselect:false);

// the path to submit the multiselect
$multiselect_path = (isset($multiselect_path)?$multiselect_path:$this->Html->urlModify(array('action' => 'multiselect')));

$multiselect_referer = (isset($multiselect_referer)?$multiselect_referer:$this->Html->urlBase());

// the options on what to do with the multiselect items
$multiselect_options = (isset($multiselect_options)?$multiselect_options:array());

// the hidden ids we need (example, the category_id for Categories/Vectors)
$multiselect_ids = (isset($multiselect_ids)?$multiselect_ids:array());

$show_refresh_table = (isset($show_refresh_table)?$show_refresh_table:true);

// grid edit for editing cells in the table objects
$use_gridedit = (isset($use_gridedit)?$use_gridedit:false);
$gridedit_path = (isset($gridedit_path)?$gridedit_path:$this->Html->url($this->Html->urlModify(array('action' => 'gridedit', 'ext' => 'json')))); // the path to submit the gridedit
$gridedit_referer = (isset($gridedit_referer)?$gridedit_referer:$this->Html->urlBase()); // possibly used later
$gridedit_options = (isset($gridedit_options)?$gridedit_options:array()); // the options on what to do with the gridedit items

// grid add for adding rows in the table objects
$use_gridadd = (isset($use_gridadd)?$use_gridadd:false);
$gridadd_path = (isset($gridadd_path)?$gridadd_path:$this->Html->url($this->Html->urlModify(array('action' => 'gridadd', 'ext' => 'json')))); // the path to submit the gridedit
$gridadd_referer = (isset($gridadd_referer)?$gridadd_referer:$this->Html->urlBase()); // possibly used later
$gridadd_options = (isset($gridadd_options)?$gridadd_options:array()); // the options on what to do with the gridedit items

// grid add for deleting rows in the table objects
$use_griddelete = (isset($use_griddelete)?$use_griddelete:false);
$griddelete_path = (isset($griddelete_path)?$griddelete_path:$this->Html->url($this->Html->urlModify(array('action' => 'griddelete', 'ext' => 'json')))); // the path to submit the gridedit
$griddelete_referer = (isset($griddelete_referer)?$griddelete_referer:$this->Html->urlBase()); // possibly used later
$griddelete_options = (isset($griddelete_options)?$griddelete_options:array()); // the options on what to do with the gridedit items

// sortable table
$sortable_options = (isset($sortable_options)?$sortable_options:array());

// if we should allow the row to be highlighted when clicked
$use_row_highlighting = (isset($use_row_highlighting)?$use_row_highlighting:true);

// javascript for table sorting that don't use pagination
$table_stripped = (isset($table_stripped)?$table_stripped:$this->Html->getExt('csv'));
$use_jsordering = (isset($use_jsordering)?$use_jsordering:true);
$use_float_head = (isset($use_float_head)?$use_float_head:true);
$use_js_search = (isset($use_js_search)?$use_js_search:true);
$use_collapsible_columns = (isset($use_collapsible_columns)?$use_collapsible_columns:true);
$use_js_exporting = (isset($use_js_exporting)?$use_js_exporting:true);
$table_export_name = (isset($table_export_name)?$table_export_name:false);
$auto_load_ajax = (isset($auto_load_ajax)?$auto_load_ajax:true);

// hold the array of table headers
// format: $th['column_key'] = content;
$th = (isset($th)?$th:array()); 

// holds the data
// format: $td[i++]['column_key'] = content
// for full format, 
// see: http://book.cakephp.org/2.0/en/core-libraries/helpers/html.html#HtmlHelper::tableCells
$td = (isset($td)?$td:array()); 

$no_records = (isset($no_records)?$no_records:__('No records were found.'));

// use pagination
// eventually upgrade to use Ajax
$use_pagination = (isset($use_pagination)?$use_pagination:true);

// assumes we're using ajax pagination
// this is the id of the dom object to paginate into
$pagination_id = ((isset($pagination_id) and $pagination_id)?$pagination_id:false);

// highlight the owner in the table if needed
$possible_owner = ((isset($possible_owner) and $possible_owner)?$possible_owner:false);

$before_table = ((isset($before_table) and $before_table)?$before_table:false);
$after_table = ((isset($after_table) and $after_table)?$after_table:false);

$before_inner_table = ((isset($before_inner_table) and $before_inner_table)?$before_inner_table:false);
$after_inner_table = ((isset($after_inner_table) and $after_inner_table)?$after_inner_table:false);
$table_caption = ((isset($table_caption) and $table_caption)?$table_caption:false);
$table_widget_options = ((isset($table_widget_options) and $table_widget_options)?$table_widget_options:array());

////////////////////////////////////////

$tableElement = $this->element('Utilities.table', array(
	'th' => $th,
	'td' => $td,
	'before_inner_table' => $before_inner_table,
	'after_inner_table' => $after_inner_table,
	'table_caption' => $table_caption,
	'no_records' => $no_records,
	'use_filter' => $use_filter,
	'use_search' => $use_search,
	'search_model' => $search_model,
	'search_placeholder' => $search_placeholder,
	'use_pagination' => $use_pagination,
	'pagination_id' => $pagination_id,
	'possible_owner' => $possible_owner,
	// multiselect options
	'use_multiselect' => $use_multiselect,
	'multiselect_path' => $multiselect_path,
	'multiselect_referer' => $multiselect_referer,
	'multiselect_options' => $multiselect_options,
	'multiselect_ids' => $multiselect_ids,
	// gridedit options
	'use_gridedit' => $use_gridedit,
	'gridedit_path' => $gridedit_path,
	'gridedit_referer' => $gridedit_referer,
	'gridedit_options' => $gridedit_options,
	// gridadd options
	'use_gridadd' => $use_gridadd,
	'gridadd_path' => $gridadd_path,
	'gridadd_referer' => $gridadd_referer,
	'gridadd_options' => $gridadd_options,
	// griddelete options
	'use_griddelete' => $use_griddelete,
	'griddelete_path' => $griddelete_path,
	'griddelete_referer' => $griddelete_referer,
	'griddelete_options' => $griddelete_options,
	'show_refresh_table' => $show_refresh_table,
	'sortable_options' => $sortable_options,
	'use_row_highlighting' => $use_row_highlighting,
	'use_jsordering' => $use_jsordering,
	'table_widget_options' => $table_widget_options,
	'table_stripped' => $table_stripped,
	'use_float_head' => $use_float_head,
	'use_collapsible_columns' => $use_collapsible_columns,
	'use_js_exporting' => $use_js_exporting,
	'export_name' => $table_export_name,
	'use_js_search' => $use_js_search,
	'auto_load_ajax' => $auto_load_ajax,
));

if($table_stripped)
{
	echo $tableElement;
	return;
}
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
<div class="center"><?php 
if($before_table) echo $before_table;
echo trim($tableElement); 
if($after_table) echo $after_table;
?></div>
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