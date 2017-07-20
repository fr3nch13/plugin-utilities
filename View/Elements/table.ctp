<?php

$table_id = (isset($table_id)?$table_id:'table_listings_'. Inflector::slug($this->Html->urlBase(true)).'_'.rand(0,1000));

$before_inner_table = ((isset($before_inner_table) and $before_inner_table)?$before_inner_table:false);
$after_inner_table = ((isset($after_inner_table) and $after_inner_table)?$after_inner_table:false);

$use_float_head = (isset($use_float_head)?$use_float_head:true);
$use_collapsible_columns = (isset($use_collapsible_columns)?$use_collapsible_columns:true);
$use_js_exporting = (isset($use_js_exporting)?$use_js_exporting:true);
$js_exporting_title = (isset($js_exporting_title)?$js_exporting_title:__('Export table to Excel'));
$export_name = (isset($export_name)?$export_name:Inflector::slug($this->Html->urlBase(true)));
$auto_load_ajax = (isset($auto_load_ajax)?$auto_load_ajax:true);
$auto_load_ajax_title_long = (isset($auto_load_ajax_title_long)?$auto_load_ajax_title_long:__('Load/Reload Dynamic Counts/Content'));
$auto_load_ajax_title_short = (isset($auto_load_ajax_title_short)?$auto_load_ajax_title_short:__('Counts'));

// include the search form
$use_search = (isset($use_search)?$use_search:true);

// javascript for table sorting that don't use pagination
$use_js_search = (isset($use_js_search)?$use_js_search:!$use_search);

// Model to use in the search
// the search plugin will figure it out if left false
$search_model = (isset($search_model)?$search_model:false);

// Term to use in the search placeholder
// the search plugin will figure it out if left false
$search_placeholder = (isset($search_placeholder)?$search_placeholder:false);

// include the filter form
$use_filter = (isset($use_filter)?$use_filter:false);

$filter_placeholder = (isset($filter_placeholder)?$filter_placeholder:$search_placeholder);

// include the filter plugin for filtering attributes
$filter_plugin = (isset($filter_plugin)?$filter_plugin:true);

// multi select for selecting multiple objects
$use_multiselect = (isset($use_multiselect)?$use_multiselect:true);

// the path to submit the multiselect
$multiselect_path = (isset($multiselect_path)?$multiselect_path:false);

// the path to redirect to after the multiselect is finished
$multiselect_referer = (isset($multiselect_referer)?$multiselect_referer:$this->Html->urlBase());

// the options on what to do with the multiselect items
$multiselect_options = (isset($multiselect_options)?$multiselect_options:array());

// the hidden ids we need (example, the category_id for Categories/Vectors)
$multiselect_ids = (isset($multiselect_ids)?$multiselect_ids:array());

// have multiselect be the first (true), or last (false)
$multiselect_first = (isset($multiselect_first)?$multiselect_first:true);

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
$use_jsordering = (isset($use_jsordering)?$use_jsordering:true);

$use_dragtable = (isset($use_dragtable)?$use_dragtable:true);


// hold the array of table headers
// format: $th['column_key'] = content;
$th = (isset($th)?$th:array()); 

// holds the data
// format: $td[i++]['column_key'] = content
// for full format, 
// see: http://book.cakephp.org/2.0/en/core-libraries/helpers/html.html#HtmlHelper::tableCells
$td = (isset($td)?$td:array()); 

$no_records_default = __('No records were found.');

if(isset($this->passedArgs['q']) and trim($this->passedArgs['q']))
{
	$no_records_default = __('No records were found with the search term: \'%s\'.', trim($this->passedArgs['q']));
}

$no_records = (isset($no_records)?$no_records:$no_records_default);
$table_caption = ((isset($table_caption) and $table_caption)?$table_caption:false);

// use pagination
$use_pagination = (isset($use_pagination)?$use_pagination:true);
$use_show_all = (isset($use_show_all)?$use_show_all:true);

// assumes we're using ajax pagination
// this is the id of the dom object to paginate into
$pagination_id = ((isset($pagination_id) and $pagination_id)?$pagination_id:false);

// highlight the owner in the table if needed
$possible_owner = ((isset($possible_owner) and $possible_owner)?$possible_owner:false);

$table_widget_options = ((isset($table_widget_options) and $table_widget_options)?$table_widget_options:array());

$table_stripped = (isset($table_stripped)?$table_stripped:$this->Html->getExt(['csv', 'pub']));
if(isset($isSubscription) and $isSubscription) 
	$table_stripped = true;

if($table_stripped)
{
	$before_inner_table = false;
	$after_inner_table = false;
	$use_float_head = false;
	$use_search = false;
	$use_js_search = false;
	$use_filter = false;
	$filter_plugin = false;
	$use_multiselect = false;
	$show_refresh_table = false;
	$use_gridedit = false;
	$use_gridadd = false;
	$use_griddelete = false;
	$use_row_highlighting = false;
	$use_jsordering = false;
	$use_dragtable = false;
	$use_pagination = false;
	$use_collapsible_columns = false;
	$use_js_exporting = false;
	$use_show_all = false;
	$auto_load_ajax = false;
}

if($use_pagination and $pagination_id)
{
	// dynamically generate a pagination id
	if($pagination_id == true)
	{
		$pagination_id = 'body_content';
	}
	
	$this->Paginator->options(array(
		'update' => '#'. $pagination_id,
		'evalScripts' => true,
		'before' => $this->Js->get('#loading')->effect('fadeIn', array('buffer' => false)),
		'complete' => $this->Js->get('#loading')->effect('fadeOut', array('buffer' => false)),
	));
}
//////////////////// block objects to try to keep this dry

$this->assign('paginate-objects', '');
$this->start('paginate-objects'); ?>
	<?php if($use_pagination and count($td)): ?>
	<div class="paginate-objects">
		<div class="paginate_counter"><div class="counter"><?php echo $this->Paginator->counter( __('Page <span class="paginate_counter-page-current">%s</span> of <span class="paginate_counter-page-last">%s</span>, total: <span class="paginate_counter-total">%s</span>', '{:page}', '{:pages}', '{:count}')); ?></div></div>
		<div class="paginate_show_all"><?php 
			$pagingParams = $this->Paginator->params();
			if(isset($passedArgs['showall']) and $passedArgs['showall'])
			{
				echo $this->Html->link(__('Show Paginated'), $this->Html->urlModify(array('showall' => 0)), array('class' => 'tab-hijack')); 
			}
			elseif($pagingParams['pageCount'] > 1)
			{
				echo $this->Html->link(__('Show All'), $this->Html->urlModify(array('showall' => true)), array('class' => 'tab-hijack')); 
			}
		?></div>
		<div class="paginate_numbers"><?php 
			$numbers = $this->Paginator->numbers(array('separator' => '', 'first' => __('First'), 'last' => __('Last'), 'class' => 'paging_link', 'currentTag' => 'i'));
			if($numbers)
			{
				echo $this->Paginator->prev('<i class="fa fa-backward" aria-hidden="true"></i>', array('class' => 'paging_link prev', 'escape' => false), null, array('class' => 'paging_link prev disabled')); 
				echo $numbers;
				echo $this->Paginator->next('<i class="fa fa-forward" aria-hidden="true"></i>', array('class' => 'paging_link', 'escape' => false), null, array('class' => 'paging_link prev disabled'));
			}
		?></div>
	</div>
	<?php endif;?>
<?php $this->end(); // paginate-objects

$this->assign('listings_table_wrapper_scrollers', '');
$this->start('listings_table_wrapper_scrollers'); ?>
	<?php if(!$table_stripped): ?>
	<div class="listings_table_wrapper_scrollers">
		<?php echo $this->fetch('paginate-objects'); ?>
		<div class="table-options">
			<a class="js-auto_load_ajax no-print" href="#" title="<?=$auto_load_ajax_title_long?>"><i class="fa fa-refresh reload-icon"></i><span><?=$auto_load_ajax_title_short?></span></a>
			<?php if($use_js_exporting): ?><a class="js-export" href="#" title="<?=$js_exporting_title?>" data-export_name="<?php echo $export_name; ?>"><i class="export-icon"></i><span><?=$js_exporting_title?></span></a><?php endif; ?>
		</div>
		<?= $this->Html->divClear(); ?>
	</div>
	<?php endif; // table stripped  ?>
<?php $this->end(); // listings_table_wrapper_scrollers

$this->assign('Utilities-search', '');
$this->start('Utilities-search'); ?>
<?php 
if($use_search)
{
	if($search_model) 
		echo $this->element('Utilities.search', array(
			'model' => $search_model,
			'placeholder' => $search_placeholder,
		));
	else
		echo $this->element('Utilities.search', array(
			'placeholder' => $search_placeholder,
		));
}
?>
<div class="clearb"> </div>
<?php $this->end(); // Utilities-search 

$this->assign('Utilities-filter', '');
$this->start('Utilities-filter'); ?>
<?php 
if($use_filter)
{
	if($search_model) 
		echo $this->element('Utilities.filter', array(
			'table_id' => $table_id,
			'placeholder' => $filter_placeholder,
		));
	else
		echo $this->element('Utilities.filter', array(
			'placeholder' => $filter_placeholder,
		));
}
?>
<?php $this->end(); // Utilities-filter 

$this->assign('possible_owner', '');
$this->start('possible_owner'); ?>
	<?php if($possible_owner): ?>
	<div class="possible_owner">
		<span class="owner">&nbsp;</span> = Owner
	</div>
	<?php endif; ?>
<?php $this->end(); // possible_owner 

$this->assign('Filter-object_form', '');
$this->start('Filter-object_form'); 
// the filter plugin
if($filter_plugin and CakePlugin::loaded('Filter'))
	echo $this->element('Filter.object_form');

$this->end(); // Filter-object_form 

//////////////////// Beginning of the actual HTML


if($this->Html->getExt('csv'))
{
	if(!count($td))
		return;
}

$csvOut = array();

$tableObject = array();
$tableObject[] = $this->fetch('Utilities-search'); 
$tableObject[] = $this->fetch('Utilities-filter');
$tableObject[] = $this->fetch('possible_owner');
$tableObject[] = $this->fetch('Filter-object_form');

if(count($td))
{ 
	if(($use_multiselect and $multiselect_path) or $use_gridedit)
	{ 
		$tableObject[] = $this->Form->create($search_model, array(
			'url' => $multiselect_path,
			'class' => (($use_multiselect and $multiselect_path)?'multiselect_form':false),
		)); 
		if($multiselect_ids)
		{
			foreach($multiselect_ids as $field => $id)
			{
				$tableObject[] = $this->Form->input($field, array('value' => $id, 'type' => 'hidden'));
			}
		}
	}

	// move the multiselect to the first position of the th
	if(isset($th['multiselect']) and $multiselect_first and $use_multiselect)
	{
		$th_multiselect = $th['multiselect'];
		unset($th['multiselect']);
		$th = array_merge(array('multiselect' => $th_multiselect), $th);
	}
	
	$contents = array();
	$ths = array();
	$editable_map = array();
	$editable_index = 0;
	$column_map = array();
	
	// if inline/grid editable
	if($use_gridedit)
		$th['editable'] = array('content' => __('Inline Actions'), 'options' => array('class' => 'editable_actions'));
	
	foreach ($th as $key => $th_column)
	{
		$contents_cnt = 0;
		// the select all option
		if($key == 'multiselect')
		{
			$html = '';
			if($use_multiselect)
			{
				$multiselect_all_id = 'multiselect_all'. rand(0, 20);
				$html = $this->Form->checkbox('multiselect_all', array(
					'id' => $multiselect_all_id,
					'class' => 'multiselect_all',
				));
				
				$contents[$key][$contents_cnt]['content'] = $html;
			}
			else
			{
				continue;
			}
		}
		elseif(!is_array($th_column))
		{
			$contents[$key][$contents_cnt]['content'] = $th_column;
			continue;
		}
		else
		{
			if(isset($th_column['content']))
			{
				$contents[$key][$contents_cnt] = $th_column;
			
				if(isset($contents[$key][$contents_cnt]['options']['editable']))
				{
					$editable_map[$editable_index] = $contents[$key][$contents_cnt]['options']['editable'];
					$editable_map[$editable_index]['key'] = $key;
					$editable_map[$key] = $editable_map[$editable_index];
					unset($contents[$key][$contents_cnt]['options']['editable']);
				}
				$contents_cnt++;
			}
			elseif(isset($th_column['contents']))
			{
				$contents[$key] = $th_column['contents'];
				if(isset($th_column['options']))
				{
					$contents[$key]['options'] = $th_column['options'];
				}
			}
			
			if(isset($contents[$key]['options']['editable']))
			{
				$editable_map[$editable_index] = $contents[$key]['options']['editable'];
				$editable_map[$editable_index]['key'] = $key;
				$editable_map[$key] = $editable_map[$editable_index];
				unset($contents[$key]['options']['editable']);
			}
		
			$editable_index++;
		}
	}
	
	$column_map_i = 0;
	foreach ($contents as $key => $content)
	{
		$html = array();
		$delim = '/';
		$options = array();
		
		if(isset($content['options']))
		{
			$options = $content['options'];
			unset($content['options']);
		}
		
		foreach ($content as $v => $item)
		{
			// look for the multiselect column
			if($key == 'multiselect')
			{
				$html[] = $item['content'];
				continue;
			}
			
			if($use_pagination and isset($item['options']['sort']))
			{
				$sort = $item['options']['sort'];
				unset($item['options']['sort']);
				$compiled = $this->Paginator->sort($sort, $item['content'], $item['options']);
				$compiled = $this->Html->tag('span', $compiled, array('class' => 'paging_link'));
				$html[] = $compiled;
				continue;
			}
			
			$html[] = $item['content'];
		
		}
		
		if(!isset($options['id']))
		{
			$options['data-column-key'] = $key;
		}
		
		// html for the draging of the column
		$drag_handle = '';
		
		$column_map[$column_map_i] = $key;
		$column_map_i++;
		
		$html = $this->Html->tag('div', implode($delim, $html). $drag_handle, array('class' => 'cell-content'));
		
		$ths[$key] = $this->Html->tag('th', $html, $options);
	}
	
	/////// the data cells
	if($multiselect_first)
	{
		foreach($td as $row_num => $cells)
		{
			// move the multiselect to the first position of the th
			if(isset($cells['multiselect']))
			{
				$cell_multiselect = $cells['multiselect'];
				unset($cells['multiselect']);
				if($use_multiselect)
				{
					$td[$row_num] = array_merge(array('multiselect' => $cell_multiselect), $cells);
				}
				else
				{
					$td[$row_num] = $cells;
				}
			}
		}
	}
	
	// if inline/grid editable
	if($use_gridedit)
	{
		reset($td);
		
		if($use_gridadd)
		{
			// get a copy of the first item in the list
			$first = array_slice($td, 0, 1);
			$first = array_shift($first);
			
			foreach($first as $k => $v)
			{
				if(is_array($v))
				{
					if(isset($v[0])) $v[0] = false;
					$first[$k] = $v;
				}
				else
				{
					$first[$k] = false;
				}
			}
			
			$first['gridadd'] = true;
			$td[] = $first;
		}
	}
	
	$td_keys = array_keys($td);
	$last_td_key = end($td_keys);
	
	foreach($td as $row_num => $columns)
	{
		// edit in place/grid edit options
		$edit_id = $edit_ids = false;
		$gridadd = false;
		$highlight = false;
		
		$csvOut[$row_num] = array();
		
		$is_totals_row = false;
		if($last_td_key == $row_num)
		{
			$_columns_first = $columns;
			reset($_columns_first);
			$_columns_first = array_shift($_columns_first);
			if(is_array($_columns_first))
			{
				$_columns_first = array_shift($_columns_first);
			}
			if(is_string($_columns_first))
			{
				$_columns_first = strtolower($_columns_first);
				$_columns_first = Inflector::slug($_columns_first);
				if(preg_match('/^total/', $_columns_first))
				{
					$is_totals_row = true;
				}
			}
			
		}
		
		if(isset($columns['highlight']))
		{
			$highlight = ($columns['highlight']?$columns['highlight']:false);
			unset($columns['highlight']);
			unset($td[$row_num]['highlight']);
		}
		
		if(isset($columns['edit_id']) or isset($columns['gridadd']) )
		{
			if(!$use_gridedit)
			{
				unset($columns['edit_id']);
				unset($td[$row_num]['edit_id']);
			}
			else
			{
				$td[$row_num]['editable_actions'] = '';
				$edit_id = $td[$row_num]['edit_id'];
				if(isset($columns['gridadd']))
				{
					$gridadd = true;
					
					// remove any of the ids that aren't foreign keys
					if(is_array($edit_id))
					{
						if(isset($edit_id[$this->Form->defaultModel]))
							unset($edit_id[$this->Form->defaultModel]);
						foreach($edit_id as $ek => $ev)
							if(stripos($ek, '.') === false)
								unset($edit_id[$ek]);
					}
					unset($columns['gridadd']);
				}
				
				if(isset($columns['edit_id']))
				{	
					if(isset($columns['gridadd']))
						$edit_id = $columns['edit_id'];
					unset($columns['edit_id']);
				}
				
				if(!is_array($edit_id))
				{
					$edit_id = array( $this->Form->defaultModel => $edit_id );
				}
				
				$edit_ids = $edit_id;
				if(isset($edit_ids[$this->Form->defaultModel]))
				{
					$edit_id = $edit_ids[$this->Form->defaultModel];
				}
				else
				{
					$edit_id = reset($edit_ids);
				}
				
				$editable_input_ids = array();
				foreach($edit_ids as $line_model => $line_id)
				{
					if(!substr_count($line_model, '.'))
					{
						$line_model .= '.id';
					}
					
					$editable_input_ids[] = $this->Form->input($line_model, array(
						'type' => 'hidden',
						'value' => $line_id,
						'class' => 'editable_id',
						'id' => $line_model.'.'.$row_num,
					));
				}
				
				$editable_button_cancel = $this->Html->link(__('Cancel'), '#', array('class' => 'editable-button-cancel'));
				
				if($gridadd)
				{
					$editable_button_save = $this->Html->link(__('Add'), '#', array('class' => 'editable-button-add'));
					$editable_button_edit = $this->Html->link(__('Add New Item'), '#', array('class' => 'editable-button-edit'));
					
					$editable_actions_off = $this->Html->tag('div', $editable_button_edit, array('class' => 'editable-actions editable-actions-off'));
					
					$editable_actions_on = $this->Html->tag('div', $editable_button_save. $editable_button_cancel. implode("\n", $editable_input_ids), array('class' => 'editable-actions editable-actions-on'));
				}	
				else
				{
					$editable_button_save = $this->Html->link(__('Save'), '#', array('class' => 'editable-button-save'));
					
					$editable_button_delete = '';
					if($use_griddelete) $editable_button_delete = $this->Html->link(__('Delete'), '#', array('class' => 'editable-button-delete'));
					
					$editable_button_edit = $this->Html->link(__('Inline Edit'), '#', array('class' => 'editable-button-edit'));
					
					$editable_actions_off = $this->Html->tag('div', $editable_button_edit, array('class' => 'editable-actions editable-actions-off'));
					$editable_actions_on = $this->Html->tag('div', $editable_button_save. $editable_button_delete. $editable_button_cancel. implode("", $editable_input_ids), array('class' => 'editable-actions editable-actions-on'));
				}
				$td[$row_num] = array_merge($columns, array('editable_actions' => array($editable_actions_off. $editable_actions_on, array('class' => 'actions')) ));
				$columns = $td[$row_num];
			}
		}
		
		$column_i = 0;
		foreach($columns as $column_num => $column)
		{
			if($column_num === 'multiselect')
			{
				if($use_multiselect and $column)
				{
					$td[$row_num][$column_num] = $this->Form->checkbox('multiple.'. $column, array('class' => 'multiselect_item'));
				}
				else
				{
					$td[$row_num][$column_num] = '';
				}
			}
			
			if(!is_array($td[$row_num][$column_num]))
			{
				$td[$row_num][$column_num] = array($td[$row_num][$column_num], array('class' => array()));
			}
			
			// 0 index is the content, 1 index is the options
			if(isset($td[$row_num][$column_num][1]['class']))
			{
				if(!is_array($td[$row_num][$column_num][1]['class']))
					$td[$row_num][$column_num][1]['class'] = preg_split('/\s+/', $td[$row_num][$column_num][1]['class']);
			}
			else
			{
				$td[$row_num][$column_num][1]['class'] = [];
			}
			
			if(is_array($td[$row_num][$column_num][1]['class']) and in_array('actions', $td[$row_num][$column_num][1]['class']))
			{
				$td[$row_num][$column_num][0] = str_replace("\n", "", $td[$row_num][$column_num][0]);
			}
			
			if($highlight)
			{
				$td[$row_num][$column_num][1]['class'][] = 'highlight';
			}
			
			if($is_totals_row)
			{
				$td[$row_num][$column_num][1]['class'][] = 'totals_row';
			}
			
			$csvColKey = $column_num;
			if(isset($column_map[$column_i]))
			{
				$csvColKey = $column_map[$column_i];
				$td[$row_num][$column_num][1]['data-column-key'] = $column_map[$column_i];
			}
			
			if(isset($td[$row_num][$column_num][1]['ajax_count_url']))
			{
				if(!isset($td[$row_num][$column_num][1]['ajax_count_urls']))
					$td[$row_num][$column_num][1]['ajax_count_urls'] = array();
				$td[$row_num][$column_num][1]['ajax_count_urls'][] = array(
					'ajax_count_url' => $td[$row_num][$column_num][1]['ajax_count_url'],
					'url' => (isset($td[$row_num][$column_num][1]['url'])?$td[$row_num][$column_num][1]['url']:false),
				);
				
				unset($td[$row_num][$column_num][1]['ajax_count_url']);
			}
			
			if(isset($td[$row_num][$column_num][1]['ajax_count_urls']))
			{
				$ajax_count_urls = array();
				foreach($td[$row_num][$column_num][1]['ajax_count_urls'] as $aci => $ac_url)
				{
					$ajax_count_url = $ajax_count_url_count = $ac_url['ajax_count_url'];
					
					if(is_array($ajax_count_url_count))
					{
						$ajax_count_url_count['getcount'] = true;
					}
					else
					{
						$ajax_count_url_count .= '/getcount:1';
					}
					
					if(isset($ac_url['url']) and $ac_url['url'])
					{
						if($this->Html->getExt('csv'))
						{
							$ajax_count_urls[$aci] = $this->requestAction($ajax_count_url_count, array('return'));
						}
						else
						{
							if(is_bool($ac_url['url']) and $ac_url['url'])
								$ajax_count_urls[$aci] = $this->Html->link($td[$row_num][$column_num][0], $ajax_count_url, array('class' => 'ajax-count-link', 'escape' => false, 'data-ajax_loaded' => 0, 'data-ajax_count_url' =>  $this->Html->url($ajax_count_url_count)));
							else
								$ajax_count_urls[$aci] = $this->Html->link($td[$row_num][$column_num][0], $ac_url['url'], array('class' => 'ajax-count-link', 'escape' => false, 'data-ajax_loaded' => 0, 'data-ajax_count_url' =>  $this->Html->url($ajax_count_url_count)));
						}
						unset($td[$row_num][$column_num][1]['ajax_count_urls'][$aci]['url']);
					}
					else
					{
						if($this->Html->getExt('csv'))
							$ajax_count_urls[$aci] = $this->requestAction($ajax_count_url_count, array('return'));
						else
							$ajax_count_urls[$aci] = $this->Html->tag('span', $td[$row_num][$column_num][0], array('class' => 'ajax-count-link', 'escape' => false, 'data-ajax_loaded' => 0, 'data-ajax_count_url' =>  $this->Html->url($ajax_count_url_count)));
					}
				
				}
				
				$td[$row_num][$column_num][0] = implode('/', $ajax_count_urls);
				
				$td[$row_num][$column_num][1]['class']['ajax-count'] = 'ajax-count';
				unset($td[$row_num][$column_num][1]['ajax_count_urls']);
			}
			
			if(isset($td[$row_num][$column_num][1]['ajax_content_url']))
			{
				$ajax_content_url = $td[$row_num][$column_num][1]['ajax_content_url'];
				
				if(is_array($ajax_content_url))
				{
					$ajax_content_url = $this->Html->url($ajax_content_url);
				}
				
				$td[$row_num][$column_num][1]['data-ajax_content_url'] = $ajax_content_url;
				$td[$row_num][$column_num][1]['data-ajax_loaded'] = 0;
				$td[$row_num][$column_num][1]['class'][] = 'ajax-content';
				unset($td[$row_num][$column_num][1]['ajax_content_url']);
			}
			
			if(isset($td[$row_num][$column_num][1]['url']))
			{
				unset($td[$row_num][$column_num][1]['url']);
			}
			
			$column_i++;
			
			if($use_gridedit and $edit_id !== false)
			{
				if($gridadd)
				{
					$edit_id = $table_id;
				}
				
				if(isset($editable_map[$column_num]))
				{
					// add data attributes to the td tag
					$td[$row_num][$column_num][1]['class'][] = 'editable';
					$td[$row_num][$column_num][1]['data-uniqueid'] = $row_num.'-'.$column_num;
					$td[$row_num][$column_num][1]['data-editable'] = 'editable';
					$td[$row_num][$column_num][1]['data-editable-id'] = $edit_id;
					$td[$row_num][$column_num][1]['data-editable-type'] = 'text';
					$td[$row_num][$column_num][1]['data-editable-required'] = false;
					if(isset($editable_map[$column_num]['type']))
					{
						$td[$row_num][$column_num][1]['data-editable-type'] = $editable_map[$column_num]['type'];
						$td[$row_num][$column_num][1]['class'][] = 'editable-'. $td[$row_num][$column_num][1]['data-editable-type'];
					}
					
					if(isset($editable_map[$column_num]['key']))
					{
						$td[$row_num][$column_num][1]['data-editable-key'] = $editable_map[$column_num]['key'];
					}
					
					$td_input_options = array(
						'type' => $td[$row_num][$column_num][1]['data-editable-type'],
						'label' => false,
						'div' => array('class' => 'editable-input'),
						'required' => false,
						'class' => array(),
						'data-editable-required' => $td[$row_num][$column_num][1]['data-editable-required'],
						'id' => $td[$row_num][$column_num][1]['data-uniqueid'],
						'value' => false,
					);
					
					if(isset($editable_map[$column_num]['rel']))
					{
						$td_input_options['rel'] = $editable_map[$column_num]['rel'];
					}
					
					if(isset($editable_map[$column_num]['highlight_toggle']))
					{
						$td_input_options['data-highlight-toggle'] = true;
					}
					
					if(isset($editable_map[$column_num]['required']))
					{
						$td_input_options['data-editable-required'] = $editable_map[$column_num]['required'];
						$td_input_options['after'] = $this->Html->tag('div', __('Required'), array('class' => 'required_message'));
					}
					
					if(isset($td[$row_num][$column_num][1]['value']))
					{
						if(in_array($td[$row_num][$column_num][1]['value'], array('0000-00-00 00:00:00', '0000-00-00')))
						{
							$td[$row_num][$column_num][1]['value'] = false;
						}
						$td_input_options['value'] = $td[$row_num][$column_num][1]['value'];
					}
					
					// possibly used for later, not being used now.
					// possible example for this:
					// http://10.0.1.202/initech/management_reports_report_items/review
					// selecting the reviewed checkbox to force a table refresh, and this item would be gone
					if(isset($editable_map[$column_num]['refresh']))
					{
						$td_input_options['data-refresh'] = true;
					}
					
					if($td[$row_num][$column_num][1]['data-editable-type'] == 'text')
					{
						$td[$row_num][$column_num][1]['class'][] = 'editable-text';
						
						if(!$td_input_options['value'])
						{
							$td_input_options['value'] = strip_tags($td[$row_num][$column_num][0]);
							if($gridadd and isset($editable_map[$column_num]['default']))
							{
								$td_input_options['value'] = $editable_map[$column_num]['default'];
							}
						}
					}
					elseif($td[$row_num][$column_num][1]['data-editable-type'] == 'textarea')
					{
						$td[$row_num][$column_num][1]['class'][] = 'editable-textarea';
						
						if(!$td_input_options['value'])
						{
							$td_input_options['value'] = strip_tags($td[$row_num][$column_num][0]);
							if($gridadd and isset($editable_map[$column_num]['default']))
							{
								$td_input_options['value'] = $editable_map[$column_num]['default'];
							}
						}
					}
					elseif($td[$row_num][$column_num][1]['data-editable-type'] == 'select')
					{
						$td[$row_num][$column_num][1]['class'][] = 'editable-select';
						
						$td_input_options['type'] = 'select';
						$td_input_options['options'] = (isset($editable_map[$column_num]['options'])?$editable_map[$column_num]['options']:array());
						if(isset($td[$row_num][$column_num][1]['options']))
						{
							$td_input_options['options'] = $td[$row_num][$column_num][1]['options'];
							unset($td[$row_num][$column_num][1]['options']);
						}	
						$td_input_options['empty'] = '[select]';
						
						if(isset($editable_map[$column_num]['searchable']))
						{
							$td_input_options['searchable'] = $editable_map[$column_num]['searchable'];
						}
						
						if($gridadd and isset($editable_map[$column_num]['default']))
						{
							$td_input_options['selected'] = $editable_map[$column_num]['default'];
						}
						else
						{
							// try to figure out what option is selected
							if(isset($td[$row_num][$column_num][1]['value']))
							{
								$td_input_options['selected'] = $td[$row_num][$column_num][1]['value'];
							}
							elseif(isset($editable_map[$column_num]['options']))
							{
								foreach($editable_map[$column_num]['options'] as $option_id => $option_name)
								{
									if($option_name == $td[$row_num][$column_num][0])
									{
										$td_input_options['selected'] = $option_id;
										break;
									}
								}
							}
						}
					}
					elseif($td[$row_num][$column_num][1]['data-editable-type'] == 'checkbox')
					{
						$td[$row_num][$column_num][1]['class'][] = 'editable-checkbox';
						
						$td_input_options['type'] = 'checkbox';
						$td_input_options['data-editable-checked-text'] = __('Yes');
						$td_input_options['data-editable-unchecked-text'] = __('No');
						
						if(isset($editable_map[$column_num]['options']))
						{
							$td_input_options['data-editable-unchecked-text'] = $editable_map[$column_num]['options'][0];
							$td_input_options['data-editable-checked-text'] = $editable_map[$column_num]['options'][1];
						}
						
						$checktest_value1 = Inflector::slug(strtolower($td[$row_num][$column_num][0]));
						$checktest_value2 = Inflector::slug(strtolower($td_input_options['data-editable-checked-text']));
						if($checktest_value1 == $checktest_value2)
						{
							$td_input_options['checked'] = 'checked';
						}
					}
					elseif(in_array($td[$row_num][$column_num][1]['data-editable-type'], array('date', 'datetime', 'time')))
					{
						if(!isset($td_input_options['value']))
						{
							$td_input_options['value'] = strip_tags($td[$row_num][$column_num][0]);
						}
						
						if($gridadd and isset($editable_map[$column_num]['default']))
						{
							$td_input_options['value'] = $editable_map[$column_num]['default'];
						}
						
						if(!$td_input_options['value'])
						{
							if(isset($editable_map[$column_num]['default']))
							{
								$td_input_options['value'] = $editable_map[$column_num]['default'];
							}
						}
						
						$td_input_options['id'] = 'datePicker_'.(isset($td[$row_num][$column_num][1]['data-editable-key'])?Inflector::slug($td[$row_num][$column_num][1]['data-editable-key']):rand(0, 1000)).'_'. $edit_id;	
						
						// because 'now' is removed, limit them to only go back 2 years.
						$td_input_options['cal_options']['showButtonPanel'] = true;
						//$td_input_options['cal_options']['onClose'] = "function() { $(this).parents('tr').find('a.editable-button-save').trigger( 'click' ); }";
						
						$this->request->data[$this->Form->defaultModel][$td[$row_num][$column_num][1]['data-editable-key']] = $td_input_options['value'];
					}
					
					// clear the value, we don't need it anymore
					if(isset($td[$row_num][$column_num][1]['value']))
						unset($td[$row_num][$column_num][1]['value']);
					
					if($td[$row_num][$column_num][1]['data-editable-key'])
					{
						// wrap the td content in a span
						$td[$row_num][$column_num][0] = $this->Html->tag('span', $td[$row_num][$column_num][0], array('class' => 'editable-content'));
						
						$td_input = $this->Form->input($td[$row_num][$column_num][1]['data-editable-key'], $td_input_options);
						
						$td[$row_num][$column_num][0] .= $td_input;
					}
				}
			}
			
			if(!in_array($csvColKey, array('actions')))
				$csvOut[$row_num][$csvColKey] = strip_tags($td[$row_num][$column_num][0]);
			$td[$row_num][$column_num][0] = $this->Html->tag('div', ($td[$row_num][$column_num][0]?$td[$row_num][$column_num][0]:'&nbsp;'), array('class' => 'cell-content'));
		}
		
		$th_count = count($ths);
		$col_count = count($ths);
		
		// incase the cells don't match up
		if($col_count < $th_count)
		{
			$last_cell = array_pop($td[$row_num]);
			if(!is_array($last_cell))
			{
				$last_cell = array($last_cell);
			}
			$last_cell[1]['colspan'] = $th_count - ($col_count -1);
			array_push($td[$row_num], $last_cell);
			$col_count++;
		}
		elseif($col_count > $th_count)
		{
			$ths[] = $this->Html->tag('th', '&nbsp;', array('colspan' => ($col_count -1) ));
			$th_count++;
		}
	}
	
	$table_cells = $this->Html->tableCells($td); 

	$tableCaption = array();

	if($use_gridedit)
		$tableCaption[] = $this->Html->tag('p', __('Hint: You can double click the rows to edit them inline.'));
	if($sortable_options)
		$tableCaption[] = $this->Html->tag('p', __('This tables is sortable, just click and drag a table row.'));
	if($table_caption)
		$tableCaption[] = $table_caption;

	$tableCaption = implode("\n", $tableCaption);
	if($tableCaption)
		$tableCaption = $this->Html->tag('caption', $tableCaption);

	$thead = false;
	if($ths)
	{
		$thead = implode("\n", $ths);
		$thead = $this->Html->tag('tr', $thead);
		$thead = $this->Html->tag('thead', $thead);
	}

	$tbody = false;
	if($table_cells)
		$tbody = $this->Html->tag('tbody', $table_cells);

	$tableContent = $tableCaption.$thead.$tbody;

	$tableClasses = array('listings', 'actual-table');
	if($sortable_options)
		$tableClasses[] = 'sorted_table';

	$table = $this->Html->tag('table', $tableContent, array(
		'id' => $table_id,
		'class' => $tableClasses,
	));

	if($before_inner_table)
		$tableObject[] = $this->Html->tag('div', $before_inner_table, array('before_inner_table'));
	$tableObject[] = $this->fetch('listings_table_wrapper_scrollers'); 
	$tableObject[] = $this->Html->tag('div', $table, array('class' => 'listings_table_wrapper'));
	$tableObject[] = $this->fetch('listings_table_wrapper_scrollers'); 
	if($after_inner_table)
		$tableObject[] = $this->Html->tag('div', $after_inner_table, array('after_inner_table')); 

	if($use_multiselect and $multiselect_path)
	{
		if($multiselect_referer and is_array($multiselect_referer))
		{
			$tableObject[] = $this->Form->input('multiselect_referer', array(
				'type' => 'hidden',
				'value' => serialize($multiselect_referer),
			));
		}
	
		if($multiselect_options)
		{
			$multiselect_options_content = $this->Form->input('multiselect_option', array(
				'options' => $multiselect_options,
				'label' => __('With Selected:'),
				'class' => 'not-chosen',
				'id' => 'multiselect_options',
			));
			$multiselect_options_content .= $this->Form->input('Multiselect.multiselect_option', array(
				'type' => 'hidden',
				'id' => 'multiselect_options_hidden',
			));
			$tableObject[] = $this->Html->div('multiselect_options', $multiselect_options_content);
		}
	
		$tableObject[] = $this->Form->end(array(
			'label' => __('Update Selected'),
			'div' => array(
				'class' => 'multiselect_submit submit',
			)
		));
	}
} 
else // if(count($td)): 
{ 
	$tableObject[] = $this->Html->tag('div', $no_records, array('class' => 'no_results'));
} // else: // if(count($td)): 

if($this->Html->getExt('csv'))
{
	echo $this->Exporter->view($csvOut, array('count' => count($csvOut)), 'csv', Inflector::camelize(Inflector::singularize($this->request->params['controller'])), false, false);
}
else
{
	echo $this->Html->tag('div', implode("\n", $tableObject), array('class' => 'object-table', 'id' => 'object-table-'. $table_id));
?>
<?php if(!isset($isSubscription) or !$isSubscription) : ?>
<script type="text/javascript">
//<![CDATA[
$(document).ready(function()
{
	var tableOptions = <?php echo json_encode($table_widget_options);?>;
	tableOptions['rowHighlighting'] = <?php echo ($use_row_highlighting?'true':'false'); ?>;
	tableOptions['floatHead'] = <?php echo (($use_float_head and !$use_filter)?'true':'false'); ?>;
	tableOptions['useJsOrdering'] = <?php echo (($use_jsordering and !$use_pagination)?'true':'false'); ?>;
	tableOptions['useJsSearch'] = <?php echo (($use_js_search and !$use_search)?'true':'false'); ?>;
	tableOptions['useDragtable'] = <?php echo (($use_dragtable and !$use_dragtable)?'true':'false'); ?>;
	tableOptions['useCollapsibleColumns'] = <?php echo ($use_collapsible_columns?'true':'false'); ?>;
	tableOptions['useJsExporting'] = <?php echo ($use_js_exporting?'true':'false'); ?>;
	tableOptions['autoLoadAjax'] = <?php echo ($auto_load_ajax?'true':'false'); ?>;
	
	<?php if($table_stripped): ?>
	tableOptions['useJsExporting'] = false;
	tableOptions['useCollapsibleColumns'] = false;
	tableOptions['autoLoadAjax'] = false;
	<?php endif; ?>
	
	<?php if($sortable_options): 
	
	$this->Html->script('jquery-sortable', array('inline' => false)); 
	$this->Html->css('jquery-sortable', null, array('inline' => false));
	
	if(!isset($sortable_options['sorted_url']))
		$sortable_options['sorted_url'] = $this->Html->url(array('action' => 'sorted', 'ext' => 'json'));
	
	?>
	tableOptions['sortable'] = true;
	tableOptions['sortableOptions'] = <?php echo json_encode($sortable_options); ?>;
	<?php endif; // if($sortable_options) ?>
	
	<?php if($use_gridedit): ?>
	/////// Inline editable stuff
	tableOptions['useGridedit'] = true;
	
	tableOptions['grideditOptions'] = {};
	tableOptions['grideditOptions']['addUri'] = "<?php echo $gridadd_path; ?>";
	tableOptions['grideditOptions']['editUri'] = "<?php echo $gridedit_path; ?>";
	tableOptions['grideditOptions']['deleteUri'] = "<?php echo $griddelete_path; ?>";
	
	<?php endif; // gridedit?>
	
	$('div#object-table-<?php echo $table_id; ?>').objectTable(tableOptions);
});
//]]>
</script>
<?php
	endif;
}