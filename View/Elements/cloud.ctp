<?php 
$main_id = (isset($main_id)?$main_id:'tags');

// include the search form
$use_search = (isset($use_search)?$use_search:true);

// Model to use in the search
// the search plugin will figure it out if left false
$search_model = (isset($search_model)?$search_model:false);

// Term to use in the search placeholder
// the search plugin will figure it out if left false
$search_placeholder = (isset($search_placeholder)?$search_placeholder:false);

?>
	
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
	
	<div id="<?php echo $main_id; ?>">
	<?php 
	$default_item = array(
		'title' => '&nbsp;',
		'url' => '#',
		'options' => array('class' => 'tagname'),
		'class' => 'tag',
	);
	foreach($items as $item): 
		$item = array_merge($default_item, $item);
	?>
		<span class="<?php echo $item['class']; ?>">
			<?php 
			echo $this->Html->link($item['title'], $item['url'], $item['options']);
//			echo $this->Html->link($item['title'], array('action' => 'view', $tag['Tag']['keyname']), array('class' => 'tagname'));
			?>
		</span>
	<?php endforeach; ?>
	</div>