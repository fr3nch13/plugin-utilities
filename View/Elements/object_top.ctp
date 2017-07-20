<?php

$page_title = (isset($page_title)?$page_title:false);
$page_subtitle = (isset($page_subtitle)?$page_subtitle:false);
$page_subtitle2 = (isset($page_subtitle2)?$page_subtitle2:false);
$page_description = (isset($page_description)?$page_description:false);
$page_options_title = (isset($page_options_title)?$page_options_title:__('Options'));
$page_options = (isset($page_options)?$page_options:array());
$page_options_title2 = (isset($page_options_title2)?$page_options_title2:__('More Options'));
$page_options2 = (isset($page_options2)?$page_options2:array());
$page_options_html = (isset($page_options_html)?$page_options_html:array());
$use_export = (isset($use_export)?$use_export:false);
$use_search = (isset($use_search)?$use_search:true);
$search_title_query = (isset($search_title_query)?$search_title_query:false);
$search_title_fields = (isset($search_title_fields)?$search_title_fields:false);

$tab = (isset($tab)?$tab:"\t");
$sep_long = (isset($sep_long)?$sep_long:str_repeat('-', 72));
$sep_short = (isset($sep_short)?$sep_short:str_repeat('-', 30));

$subscribable = (isset($subscribable)?$subscribable:false);

if(isset($this->params['prefix']) and $page_title)
{
	$page_title_prefix = (isset($page_title_prefix)?$page_title_prefix:false);
	if(!$page_title_prefix)
		$page_title_prefix = Inflector::humanize($this->params['prefix']);
	$page_title = $page_title_prefix. ' - '. $page_title;
}

if(!$search_title_query and isset($this->passedArgs['q']) and trim($this->passedArgs['q']))
{
	$q = $this->passedArgs['q'];
//	if(stripos($q, "\n") !== false)
	if(preg_match('/(\n|\t)/', $q))
	{
//		$q_parts = explode("\n", $q);
		$q_parts = preg_split('/(\n|\t)/', $q);
		$q = array();
		foreach($q_parts as $q_part)
		{
			if(trim($q_part))
				$q[] = "'". trim($q_part). "'";
		}
		$qcount = count($q); 
		if($qcount > 30)
		{
			$qs = [];
			$i = 0;
			foreach($q as $v)
			{
				$i++;
				if($i > 20)
					break;
				$qs[] = $v;
			}
			$q = implode(', ', $qs). __(' and %s others', ($qcount - 30));
		}
		else
		{
			$q = implode(', ', $q);
		}
	}
	
	$search_title_query = __('Filtered - Including: %s', $q);
	
	if(isset($this->passedArgs['ex']) and (int)$this->passedArgs['ex'] > 0)
	{
		$search_title_query = __('Filtered - Excluding: %s', $q);
	}
}

if(!$search_title_fields and isset($this->passedArgs['f']) and trim($this->passedArgs['f']))
{
	$f = array();
	if(stripos($this->passedArgs['f'], '|') !== false)
	{
		$f = explode('|', $this->passedArgs['f']);
	}
	else
	{
		$f = array($this->passedArgs['f']);
	}
	
	$search_title_template = __('On the field: %s');
	if(count($f) > 1) $search_title_template = __('On the fields: %s');
	$field_titles = array();
	
	foreach($f as $search_field)
	{
		$field_parts = explode('.', $search_field);
		foreach($field_parts as $i => $field_part)
		{
			$field_parts[$i] = Inflector::humanize($field_parts[$i]);
			$field_parts[$i] = trim(implode(' ', preg_split('/(?=[A-Z])/',$field_parts[$i])));
			$field_parts[$i] = __('%s', $field_parts[$i]);
		}
		$field_titles[] = "'". implode(' -> ', $field_parts). "'";
	}
	
	$search_title_fields = __($search_title_template, implode(', ', $field_titles));
}


if($page_title) $this->set('title_for_layout', strip_tags($page_title)); 

if($page_title or $page_subtitle or $page_subtitle2 or $search_title_query or $search_title_fields or $page_description or $page_options or $page_options2 or $use_export):

if($this->Html->getExt('txt'))
{
	$out = array(
		$page_title,
	);
	if($page_subtitle)
		$out[] = strip_tags($page_subtitle);
	if($page_subtitle2)
		$out[] = strip_tags($page_subtitle2);
	if($search_title_query or $search_title_fields)
		$out[] = ' ';
	if($search_title_query)
		$out[] = $search_title_query;
	if($search_title_fields)
		$out[] = $search_title_fields;
	if($page_description)
		$out[] = $page_description;
	
	if($page_options and is_array($page_options))
	{
		$out[] = $this->element('Utilities.page_options', array('page_options' => $page_options, 'page_options_title' => $page_options_title));
	}
	if($page_options2 and is_array($page_options2))
	{
		$out[] = $this->element('Utilities.page_options', array('page_options' => $page_options2, 'page_options_title' => $page_options_title2));
	}
	if($page_options_html)
	{
		// is a cakephp url, load it with jquery/ajax
		if(is_array($page_options_html))
		{
		}
		else
		{
			$out[] = strip_tags($page_options_html);
		}
	}
	
	$out[] = ' ';
	$out[] = $sep_long;
	echo implode("\n", $out);
}
else
{
?>
<div class="top-holder"></div>
<div class="top">
	<div class="page-header">
		<?php if($subscribable)
		{
			echo $this->Html->link('<i class="fa fa-envelope-open fa-icon-only fa-fw"></i>', 
				['plugin' => 'utilities', 'prefix' => false, 'controller' => 'subscriptions', 'action' => 'subscribe'], 
				['escape' => false, 'title' => __('Subscribe to this page.'), 'class' => 'subscribable no-print', 
					'data-subscribable' => 1,
					'data-subscribeurl' => $this->Html->permaLink(),
					'data-subscribecheck' => $this->Html->url(["plugin" => "utilities", "prefix" => false, "controller" => "subscriptions", "action" => "check"]),
				]
			);
		}
		?>
		<h1 class="page-title"><?php echo $page_title; ?></h1>
		
		<?php if($page_subtitle): ?>
		<h2 class="page-subtitle"><?php echo $page_subtitle; ?></h2>
		<?php endif; ?>
		
		<?php if($page_subtitle2): ?>
		<h3 class="page-subtitle2"><?php echo $page_subtitle2; ?></h3>
		<?php endif; ?>
		
		<?php if($search_title_query): ?>
		<h3 class="search-title-query"><?php echo $search_title_query; ?></h3>
		<?php endif; ?>
		<?php if($search_title_fields): ?>
		<h3 class="search-title-fields"><?php echo $search_title_fields; ?></h3>
		<?php endif; ?>
		
		<?php if($page_description): ?>
		<div class="page_description"><?php echo $page_description; ?></div>
		<?php endif; ?>
	</div>
	<div class="page-options">
		<?php
		if($page_options and is_array($page_options))
		{
			echo $this->element('Utilities.page_options', array('page_options' => $page_options, 'page_options_title' => $page_options_title));
		}
		
		if($page_options2 and is_array($page_options2))
		{
			echo $this->element('Utilities.page_options', array('page_options' => $page_options2, 'page_options_title' => $page_options_title2));
		}
		if($page_options_html)
		{
			// is a cakephp url, load it with jquery/ajax
			if(is_array($page_options_html))
			{
			}
			else
			{
				echo $page_options_html;
			}
		}
/*
		 if($use_export) 
			echo $this->Wrap->exportButtons(); 
*/
		?>
	</div>
	<div class="clearb"> </div>
</div>
<?php
}
endif;