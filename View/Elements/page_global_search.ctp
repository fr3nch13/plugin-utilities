<?php 
/**
 * File: /app/View/Elements/page_global_search.ctp
 * 
 * Use: provide a consistant layout for search pages.
 *
 * Usage: echo $this->element('page_global_search', array([options]));
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
$subscribable = (isset($subscribable)?$subscribable:false);

$action = (isset($action)?$action:$this->params->action);
$field = (isset($field)?$field:'q');

$stats = (isset($stats)?$stats:array());
$stats_title = (isset($stats_title)?$stats_title:__('Stats'));
$stats_options = (isset($stats_options)?$stats_options:false);

$tabs = (isset($tabs)?$tabs:array());
$tabs_options = (isset($tabs_options)?$tabs_options:false);

$search_options = (isset($search_options)?$search_options:array());

$search_id = (isset($search_id)?$search_id:'object-global-search-'. rand(1,1000));

// arguments for the clear button
$clear_args = $this->passedArgs;
foreach($clear_args as $k => $v)
{
	if(preg_match('/^Filter\./i', $k))
		unset($clear_args[$k]);
}
$clear_args = array_merge(array('action' => $action), $clear_args, array('page' => 1, $field => '' , 'f' => '', 'ex' => 0, 'p' => 0));

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
	'subscribable' => $subscribable,
));

$searchtabs = [];
if(isset($this->passedArgs['items']) and $this->passedArgs['items'])
{
	$searchtabs = explode('|', $this->passedArgs['items']);
}
else
{
	$searchtabs = array_keys($search_options);
}

if(isset($this->passedArgs['q']) and $this->passedArgs['q'])
{
	foreach($searchtabs as $controller)
	{
		if(isset($search_options[$controller]))
		{
			$label = $search_options[$controller];
			$ajax_url = $this->Html->urlModify(array_merge(array(
				'controller' => $controller,
				'action' => 'search_results',
			), $this->passedArgs));
			
			$stats['search_'. $controller] = $tabs['search_'. $controller] = array(
				'id' => 'search_'. $controller,
				'name' => $label, 
				'ajax_url' => $ajax_url,
			);
		}
	}
}
else
{
	$tabs['search_results'] = array(
		'id' => 'search_results',
		'name' => __('Enter Search Term'), 
		'content' => $this->element('Utilities.page_index', array(
			'page_title' => __('Search'),
			'use_search' => false,
			'no_records' => __('Please enter a search term in the form above.'),
		)),
	);
}
?>

<div class="center global-search" id="<?= $search_id ?>">
	<div class="form">
		<?php echo $this->Form->create(array('class' => 'global-search'));?>
			<fieldset>
		    	<?php
					echo $this->Form->input('q', array(
						'placeholder' => __('Enter search term here.'),
						'label' => false,
						'type' => 'search',
						'class' => array('search-term'),
						'div' => array('class' => array('search-term')),
						'required' => true,
					));
					echo $this->Form->submit(__('Search'), array(
						'class' => array('search-button'),
						'div' => array('class' => array('search-button')),
					));
					echo $this->Form->submit(__('Clear'), array(
						'class' => array('clear-button'),
						'div' => array('class' => array('clear-button')),
						'data-url' => $this->Html->url($clear_args),
					));
					echo $this->Form->input('items', array(
						'label' => false,
						'options' => $search_options,
						'empty' => __('[Select Items to search]'),
						'searchable' => false,
						'class' => array('search-object'),
						'div' => array('class' => array('search-object')),
						'multiple' => true,
					));
				?>
			</fieldset>
		<?php echo $this->Form->end(); ?>
	</div>
	
	<?php 
	// create the load order so the js buffer is in a different order.
	// e.g. tabs first
	$tabs_html = $stats_html = '';
	if($tabs and is_array($tabs))
	{
		$tabs_html = $this->element('Utilities.object_tabs', array(
			'tabs' => $tabs,
			'options' => $tabs_options,
		));
	}
	if($stats and is_array($stats)) 
	{
		$stats_html = $this->element('Utilities.stats', array(
			'stats' => $stats,
			'title' => $stats_title,
			'options' => $stats_options,
		)); 
	}
	
	if($stats and is_array($stats)) 
		echo $stats_html; 
	
	echo $this->Html->clearb();
	
	if($tabs and is_array($tabs))  
		echo $tabs_html; 
	?>
	
</div>

<script type="text/javascript">
//<![CDATA[
$(document).ready(function ()
{
	var searchOptions = {
	};
	
	$('div#<?php echo $search_id; ?>').objectGlobalSearch(searchOptions);
});
//]]>
</script>