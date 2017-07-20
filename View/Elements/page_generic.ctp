<?php 
/**
 * File: /app/View/Elements/page_generic.ctp
 * 
 * Use: provides a template for displaying generic stuff like a static page
 *
 */

/////// Default settings.
$this->set('trackReferer', true);

// main title of the page
$page_title = (isset($page_title)?$page_title:$this->get('page_title', ''));
$page_subtitle = (isset($page_subtitle)?$page_subtitle:$this->get('page_subtitle', ''));
$page_subtitle2 = (isset($page_subtitle2)?$page_subtitle2:$this->get('page_subtitle2', false));
$page_options_title = (isset($page_options_title)?$page_options_title:$this->get('page_options_title', __('Options')));
$page_options = (isset($page_options)?$page_options:$this->get('page_options', array()));
$page_options_title2 = (isset($page_options_title2)?$page_options_title2:$this->get('page_options_title2', __('More Options')));
$page_options2 = (isset($page_options2)?$page_options2:$this->get('page_options2', array()));
$page_options_html = (isset($page_options_html)?$page_options_html:$this->get('page_options_html', ''));
$page_description = (isset($page_description)?$page_description:$this->get('page_description', false));
$use_search = (isset($use_search)?$use_search:$this->get('use_search', true));
$use_filter = (isset($use_filter)?$use_filter:$this->get('use_filter', false));
$use_export = (isset($use_export)?$use_export:$this->get('use_export', false));
$search_title_query = (isset($search_title_query)?$search_title_query:$this->get('search_title_query', false));
$search_title_fields = (isset($search_title_fields)?$search_title_fields:$this->get('search_title_fields', false));
$subscribable = (isset($subscribable)?$subscribable:false);

/// displaying a status bar
$status_steps = (isset($status_steps)?$status_steps:$this->get('status_steps', array()));

// content for this page
$page_content = (isset($page_content)?$page_content:$this->get('page_content', false));

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
	
	<?php if($page_description): ?>
	<div class="page_description"><?php echo $page_description; ?></div>
	<?php endif; ?>
	
	<?php if($status_steps): ?>
	<div class="status_steps"><?php echo $this->element('Utilities.status_bar', array('steps' => $status_steps)); ?></div>
	<?php endif; ?>
	
	<?php echo $page_content; ?>
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