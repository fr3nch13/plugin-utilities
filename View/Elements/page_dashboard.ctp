<?php 
/**
 * File: /app/View/Elements/page_dashboard.ctp
 * 
 * Use: provide a consistant layout for dashboard pages.
 *
 * Usage: echo $this->element('page_dashboard', array([options]));
 */

/////// Default settings.
$this->set('trackReferer', true);

// main title of the page
$page_title = (isset($page_title)?$page_title:'');
$page_subtitle = (isset($page_subtitle)?$page_subtitle:'');
$page_subtitle2 = (isset($page_subtitle2)?$page_subtitle2:false);
$page_options_title = (isset($page_options_title)?$page_options_title:__('Options'));
$page_options = (isset($page_options)?$page_options:[]);
$page_options_title2 = (isset($page_options_title2)?$page_options_title2:__('More Options'));
$page_options2 = (isset($page_options2)?$page_options2:[]);
$page_options_html = (isset($page_options_html)?$page_options_html:[]);
$page_description = (isset($page_description)?$page_description:false);
$use_search = (isset($use_search)?$use_search:true);
$use_filter = (isset($use_filter)?$use_filter:false);
$use_export = (isset($use_export)?$use_export:false);
$search_title_query = (isset($search_title_query)?$search_title_query:false);
$search_title_fields = (isset($search_title_fields)?$search_title_fields:false);
$subscribable = (isset($subscribable)?$subscribable:false);


$dashboard_id = (isset($dashboard_id)?$dashboard_id:'object-dashboard-'. rand(1,1000));

// the individual blocks that will be loaded
$dashboard_blocks = (isset($dashboard_blocks)?$dashboard_blocks:[]);
$dashboard_options = (isset($dashboard_options)?$dashboard_options:[]);
$dashboard_tabs = (isset($dashboard_tabs)?$dashboard_tabs:[]);

// stats to be displayed
$stats = (isset($stats)?$stats:[]);

// hold extra options for the stats element
$stats_options = (isset($stats_options)?$stats_options:false);

// hold the array of tabs on this object
// format: $tabs[] = array('key' => 'key', 'name' => __('Name'), 'content' => [content], 'url' => [url to content for ajax])), // value should be a number
$tabs = (isset($tabs)?$tabs:false);

// hold extra options for the tabs element
$tabs_options = (isset($tabs_options)?$tabs_options:false);

////////////////////////////////////////


echo $this->element('Utilities.object_top', [
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
]);
?>

<div class="center dashboard" id="<?= $dashboard_id ?>">
	<?php 
	// create the load order so the js buffer is in a different order.
	// e.g. tabs first
	$tabs_html = $stats_html = $dashboard_html = '';
	
	if($dashboard_blocks and is_array($dashboard_blocks))
	{
		$dashboard_html = $this->element('Utilities.object_dashboard', [
			'dashboard_id' => $dashboard_id,
			'dashboard_blocks' => $dashboard_blocks,
			'options' => $dashboard_options,
		]); 
	}
	
	if($stats and is_array($stats)) 
	{
		// see if we're dealing with stats from the Usage plugin, if so, use that element
		$stats_current = current($stats);
		if(isset($stats_current['UsageEntity']))
		{
			$stats_html = $this->Html->tag('h2', __('Usage Stats'), ['class' => 'dashboard_section_title']);
			$stats = $this->Usage->transformStats($stats);
			foreach($stats as $stat)
			{
				$stats_html .= $this->element('Utilities.stats', $stat);
				
				$tabs[] = [
					'title' => $stat['title'],
					'key' => $stat['key'],
					'content' => false,
				];
			}
		}
		else
		{
			$stats_html = $this->element('Utilities.stats', [
				'stats' => $stats,
				'options' => $stats_options,
			]);
		}
	}
	
	if($tabs and is_array($tabs))
	{
		$tabs_html = $this->element('Utilities.object_tabs', [
			'tabs' => $tabs,
			'options' => $tabs_options,
		]);
	}
	
	if($dashboard_tabs and is_array($dashboard_tabs))
	{
		$dashboard_tabs_html = $this->element('Utilities.object_tabs', [
			'tabs' => $dashboard_tabs,
			'options' => $tabs_options,
		]);
	}
	?>
	
	<?php if($stats and is_array($stats)): ?>
	<div class="full"><?= $stats_html; ?></div>
	<div class="clearb"> </div>
	<?php endif; ?>
	
	<?php if($dashboard_blocks and is_array($dashboard_blocks)): ?>
	<div class="full"><?= $dashboard_html; ?></div>
	<div class="clearb"> </div>
	<?php endif; ?>
	
	<?php if($dashboard_tabs and is_array($dashboard_tabs)): ?>
	<div class="full"><?= $dashboard_tabs_html; ?></div>
	<div class="clearb"> </div>
	<?php endif; ?>
	
	<?php if($tabs and is_array($tabs)): ?>
	<div class="full"><?= $tabs_html; ?></div>
	<div class="clearb"> </div>
	<?php endif; ?>
	
</div>

	
<script type="text/javascript">
//<![CDATA[
$(document).ready(function ()
{
	var dashboardOptions = {
		bookmarkerUrl: '<?= $this->Html->url($this->Html->urlModify(array("action" => "db_myblock"))) ?>',
	};
	
	$('div#<?php echo $dashboard_id; ?>').objectDashboard(dashboardOptions);
});
//]]>
</script>