<?php 
/**
 * File: /app/View/Elements/object_tabs.ctp
 * 
 * Use: Allows the use of tabs on the page
 * Supports Ajax loading of tabs
 *
 */

$tabs = (isset($tabs)?$tabs:array());

$tabs_id = (isset($tabs_id)?$tabs_id:'tabs_'. rand(0, 1000));

$options = (isset($options)?$options:array());
$class = (isset($options['class'])?$options['class']:'');

$tabOptions = (isset($tabOptions)?$tabOptions:array(
	'useUiTabs' => false,
));

$sep_long = (isset($sep_long)?$sep_long:str_repeat('-', 72));
$sep_short = (isset($sep_short)?$sep_short:str_repeat('-', 30));

// try to match the stats info to the tab
$stat_defaults = array(
	'id' => false,
	'name' => false,
	'value' => false,
	'ajax_count_url' => array(),
	'tab' => array(),
	'content' => false,
	'tip' => false,
	'ajax_count' => array(false, false),
);

$tab_options_default = array(
	'escape' => false,
	'role' => 'tab',
	'aria-selected' => "false",
	'aria-controls' => "false",
	'class' => array('tab'),
);

$panel_options_default = array(
	'escape' => false,
	'role' => 'panel',
	'aria-labelledby' => false,
	'aria-hidden' => "true",
	'class' => array('panel', 'hidden'),
);

$out = array();
if($this->Html->getExt('txt'))
{
	$out[] = '';
	
	foreach($tabs as $tab)
	{
		if(isset($tab['key']) and !isset($tab['id']))
			$tab['id'] = $tab['key'];
		$tab_key = 'tab-'. (isset($tab['id'])?$tab['id']:$i);
		$panel_key = 'panel-'. (isset($tab['id'])?$tab['id']:$i);
		$tab_options = (isset($tab['tab_options'])?$tab['tab_options']:array());
		$tab_options = array_merge($tab_options_default, $tab_options);
		
		$tab_options['aria-controls'] = $panel_key;
		$tab_options['id'] = (isset($tab_options['id'])?$tab_options['id']:$tab_key);
		
		
		if(!isset($tab['name']) and isset($tab['title']) )
			$tab['name'] = $tab['title'];
		$name = (isset($tab['name'])?$tab['name']:Inflector::humanize($tab['id']));
		
		if(!isset($tab['ajax_url']) and isset($tab['url']) )
			$tab['ajax_url'] = $tab['url'];
		
		// load the first one as default
		if(!isset($tab['ajax_url']) and isset($tab['ajax_urls']))
		{
			$tab['ajax_url'] = array_shift($tab['ajax_urls']);
			if(isset($tab['ajax_url']['url']))
				$tab['ajax_url'] = $tab['ajax_url']['url'];
		}
		
		$url = (isset($tab['ajax_url'])?$tab['ajax_url']:false);
		
		$out[] = '';
		$out[] = __('-- %s', $tab['name']);
			
		$out[] = $sep_short;
		if($url)
		{
			$url['ext'] = 'csv';
			$out[] = trim($this->requestAction($url, array('return')));
		}
		if(!$url)
			$url = '#';
		
		if(isset($tab['content']))
		{
			$out[] = $tab['content'];
		}
		$out[] = $sep_long;
	}
	
	echo trim(implode("\n", $out));
}
else
{
?>
<div class="object-tabs <?=$class ?>" id="object-tabs-<?=$tabs_id ?>">
	<nav class="tabs <?php if(isset($isSubscription) and $isSubscription){ echo "no-print"; } ?>" role="tablist">
	<?php 
	foreach($tabs as $i => $tab)
	{
		if(isset($tab['key']) and !isset($tab['id']))
			$tab['id'] = $tab['key'];
		$tab_key = 'tab-'. (isset($tab['id'])?$tab['id']:$i);
		$panel_key = 'panel-'. (isset($tab['id'])?$tab['id']:$i);
		$tab_options = (isset($tab['tab_options'])?$tab['tab_options']:array());
		$tab_options = array_merge($tab_options_default, $tab_options);
		
		$tab_options['aria-controls'] = $panel_key;
		$tab_options['id'] = (isset($tab_options['id'])?$tab_options['id']:$tab_key);
		
		
		if(!isset($tab['name']) and isset($tab['title']) )
			$tab['name'] = $tab['title'];
		$name = (isset($tab['name'])?$tab['name']:Inflector::humanize($tab['id']));
		
		if(!isset($tab['ajax_url']) and isset($tab['url']) )
			$tab['ajax_url'] = $tab['url'];
		
		// load the first one as default
		if(!isset($tab['ajax_url']) and isset($tab['ajax_urls']))
		{
			$tab['ajax_url'] = array_shift($tab['ajax_urls']);
			if(isset($tab['ajax_url']['url']))
				$tab['ajax_url'] = $tab['ajax_url']['url'];
		}
		
		$url = (isset($tab['ajax_url'])?$tab['ajax_url']:false);
		if(!$url)
			$url = '#';
		
		echo $this->Html->link($name, $url, $tab_options);
	}
	?>
	</nav>
	<div class="panels" role="panellist">
	<?php if(!isset($isSubscription) or !$isSubscription): ?>
	<div class="panel-loading"><i class="fa fa-spinner fa-spin"></i></div>
	<?php endif; ?>
	<?php 
	foreach($tabs as $i => $tab)
	{
		if(isset($tab['key']) and !isset($tab['id']))
			$tab['id'] = $tab['key'];
		$tab_key = 'tab-'. (isset($tab['id'])?$tab['id']:$i);
		$panel_key = 'panel-'. (isset($tab['id'])?$tab['id']:$i);
		$panel_options = (isset($tab['panel_options'])?$tab['panel_options']:array());
		$panel_options = array_merge($panel_options_default, $panel_options);
		
		$panel_options['aria-labelledby'] = $tab_key;
		$panel_options['id'] = (isset($panel_options['id'])?$panel_options['id']:$panel_key);
		
		$content = (isset($tab['content'])?$tab['content']:false);
		
		if(!isset($tab['ajax_url']) and isset($tab['url']) )
			$tab['ajax_url'] = $tab['url'];
		
		// load the first one as default
		if(!isset($tab['ajax_url']) and isset($tab['ajax_urls']))
		{
			$tab['ajax_url'] = array_shift($tab['ajax_urls']);
			if(isset($tab['ajax_url']['url']))
				$tab['ajax_url'] = $tab['ajax_url']['url'];
		}
		
		$url = (isset($tab['ajax_url'])?$tab['ajax_url']:false);
		
		if($url)
		{
			$panel_options['data-current-url'] = $this->Html->url($url);
		}
		
		echo $this->Html->tag('section', $content, $panel_options);
	}
	?>
	</div>
</div>
<?php if(!isset($isSubscription) or !$isSubscription) : ?>
<script type="text/javascript">
//<![CDATA[
$(document).ready(function ()
{
	var tabOptions = <?= json_encode($tabOptions); ?>;
	$('div#object-tabs-<?php echo $tabs_id; ?>').objectTabs(tabOptions);
});
//]]>
</script>
<?php endif; ?>
<?php
} // if($this->Html->getExt('txt'))