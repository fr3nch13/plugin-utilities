<?php
/**
 * File: /app/View/Elements/page_cloud.ctp
 * 
 * Use: provide a consistant layout for index pages.
 * It works similar to the page_index, only displays the defined name as a cloud.
 * Includes the ability to search
 *
 * Usage: echo $this->element('page_cloud', array([options]));
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

// Model to use in the search
// the search plugin will figure it out if left false
$search_model = (isset($search_model)?$search_model:Inflector::singularize(Inflector::camelize($this->params->controller)));

// Term to use in the search placeholder
// the search plugin will figure it out if left false
$search_placeholder = (isset($search_placeholder)?$search_placeholder:$this->params->controller);

// holds the data
// format: array[0] = array('url', 'title');
$items = (isset($items)?$items:array()); 

$no_records = (isset($no_records)?$no_records:__('No records were found.'));

// highlight the owner in the table if needed
$possible_owner = ((isset($possible_owner) and $possible_owner)?$possible_owner:false);

$before_cloud = ((isset($before_cloud) and $before_cloud)?$before_cloud:false);
$after_cloud = ((isset($after_cloud) and $after_cloud)?$after_cloud:false);

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
if($before_cloud) echo $before_cloud;

echo $this->element('Utilities.cloud', array(
	'items' => $items,
	'use_search' => $use_search,
	'search_model' => $search_model,
	'search_placeholder' => $search_placeholder,
	'possible_owner' => $possible_owner,
)); 

if($after_cloud) echo $after_cloud;
?>
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