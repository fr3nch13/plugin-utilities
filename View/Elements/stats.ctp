<?php
/**
 * File: /Plugin/Utilities/View/Elements/stats.ctp
 * 
 * Use: provides a standard for displaying stats about an object.
 *
 * Usage: echo $this->element('Utilities.stats', array([details]));
 */
$stats_id = (isset($stats_id)?$stats_id: rand(0, 100));
$title = (isset($title)?$title:__('Stats'));
$class = (isset($options['class'])?$options['class']:'object-stats');
$stats = (isset($stats)?$stats:array());
$multi_split = (isset($multi_split)?$multi_split:false);

$tab = (isset($tab)?$tab:"\t");
$sep_long = (isset($sep_long)?$sep_long:str_repeat('-', 72));
$sep_short = (isset($sep_short)?$sep_short:str_repeat('-', 30));

$outStats = array();

if($this->Html->getExt('txt'))
{
	$outStats[] = ' ';
	$outStats[] = ' ';
	$outStats[] = $title;
	$outStats[] = $sep_short;
}
else
{
}
$statsHtml = array();
foreach ($stats as $stat)
{
	$stat_id = (isset($stat['id'])?$stat['id']: rand(0, 100));
	
	$li_options = array('class' => 'stat');
	
	if(!isset($li_options['id']))
	{
		$li_options['id'] = 'stat_'. $stat_id;
	}
	
	if(isset($stat['tip']))
	{
		$li_options['title'] = $stat['tip'];
	}
	
	$tab_id = false;
	if(isset($stat['tab']))
	{
		$tab_id = $stat['tab'][0]. '-'. $stat['tab'][1];
	}
	
	$name = (isset($stat['name'])?$stat['name']:'&nbsp;');
	$value = (isset($stat['value'])?$stat['value']:0);
	
	if(isset($stat['data-href']))
	{
		if(is_array($stat['data-href']))
			$stat['data-href'] = $this->Html->url($stat['data-href']);
		$li_options['data-href'] = $stat['data-href'];
	}
	
	$nameOptions = array('class' => 'name');
	$valueOptions = array('class' => 'value');
	
	$ajaxCountUrls = array();
	
	if(isset($stat['ajax_count_url']) and !isset($stat['ajax_url']))
		$stat['ajax_url'] = $stat['ajax_count_url'];
	
	if(isset($stat['ajax_count_urls']) and !isset($stat['ajax_urls']))
		$stat['ajax_urls'] = $stat['ajax_count_urls'];
	
	if(isset($stat['ajax_url']))
	{
		$value = '...';
		$ajaxCountUrls[] = $stat['ajax_url'];
	}
	elseif(isset($stat['ajax_urls']))
	{
		$ajaxCountUrls = $stat['ajax_urls'];
	}
	
	if($ajaxCountUrls)
	{
		$value = array();
		foreach($ajaxCountUrls as $i => $ajaxCountUrl)
		{
			$ajaxCountOptions = array(
				'class' => 'ajax-count',
				'id' => 'stat_value_'. $stat_id.'_'. $i,
				'aria-controls' => 'tab-'. $stat_id,
			);
			if(isset($tab_id))
			{
				$ajaxCountOptions['data-tab-id'] = $tab_id;
			}
			if(isset($ajaxCountUrl['url']))
			{
				if(isset($ajaxCountUrl['options']))
					$ajaxCountOptions = array_merge($ajaxCountOptions, $ajaxCountUrl['options']);
				$ajaxCountUrl = $ajaxCountUrl['url'];
			}
			
			$ajaxCountOptions['data-count-url'] = $this->Html->url(array_merge($ajaxCountUrl, array('getcount' => true)));
			
			if($this->Html->getExt('txt'))
			{
				$value[] = $this->requestAction(array_merge($ajaxCountUrl, array('getcount' => true)), array('return'));
			}
			else
				$value[] = $this->Html->link('...', $ajaxCountUrl, $ajaxCountOptions);
		}
		$value = implode('/', $value);
	}
	
	if($this->Html->getExt('txt'))
	{
		$outStats[] = __('%s : %s', $name, strip_tags($value));
	}
	else
	{
		$name = $this->Html->tag('span', $name, $nameOptions);	
		$value = $this->Html->tag('span', $value, $valueOptions);
		$stat_content = $value.$name;
		$statsHtml[] = $this->Html->tag('li', $stat_content, $li_options);
	}
}

if($this->Html->getExt('txt'))
{
	echo implode("\n", $outStats);
}
else
{
	if($title)
		$title = $this->Html->tag('h3', $title);
	$statsContent = $this->Html->tag('ul', implode("\n", $statsHtml));
	
	echo $this->Html->tag('div', $title.$statsContent, array(
		'class' => array('stats', $class),
		'id' => 'object-stats-'. $stats_id,
	));
}

if($multi_split and !$this->Html->getExt('txt'))
	echo $this->Html->divClear();

if(!$this->Html->getExt('txt'))
{
	echo $this->Js->writeBuffer(); ?>

<?php if(!isset($isSubscription) or !$isSubscription) : ?>
<script type="text/javascript">
//<![CDATA[
$(document).ready(function ()
{
	$('div#object-stats-<?php echo $stats_id; ?>').objectStats();
});
//]]>
</script>
<?php endif; ?>
<?php } // if($this->Html->getExt('txt'))