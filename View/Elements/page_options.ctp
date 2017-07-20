<?php

$page_options_title = (isset($page_options_title)?$page_options_title:__('Page Options'));
$page_options = (isset($page_options)?$page_options:array());

$tab = (isset($tab)?$tab:"\t");
$sep_long = (isset($sep_long)?$sep_long:str_repeat('-', 72));
$sep_short = (isset($sep_short)?$sep_short:str_repeat('-', 30));

$page_options_count = count($page_options);

if($this->Html->getExt('txt'))
{
	$out = array('');
	$out[] = $page_options_title;
	$out[] = $sep_short;
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
		if(!trim($content))
			continue;
		
		
		$matches = array();
		if($url = preg_match('/<a href="(.+)">/', $content, $matches))
		{
			$content = $this->Html->link(strip_tags($content), $matches[1], array('asText' => true));
		}
		
		$out[] = $content;
	}
	
	echo implode("\n", $out);
}
else
{

$use_menu = false;
if($page_options_count > 2)
	$use_menu = true;
?>

<div class="page_options <?php echo ($use_menu?'qtip-menu':'no-menu'); ?> no-print">
	<?php if($use_menu): ?>
	<span>
		<a href="#">
			<?= $page_options_title ?>
			<i class="fa fa-caret-down fa-fw"></i>
		</a>
	</span>
	<?php endif; ?>
	<ul>
	<?php foreach ($page_options as $page_option)
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
		if(!trim($content))
			continue;
		
		echo $this->Html->tag('li', $content, $options);
	}
	?>
	</ul>
</div>
<?php
}