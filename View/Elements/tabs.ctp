<?php 
/**
 * File: /app/View/Elements/tabs.ctp
 * 
 * Use: Allows the use of tabs on the page
 * Supports Ajax loading of tabs
 *
 */

// title of the block
$title = (isset($title)?$title:'');

// the list of tabs
/* format = array(
	'key' => 'key' // used as the unique id
	'title' => 'title' // the name that goes into the tab
	'content' => '', // content of the tab itself
	'url' => [cake_compatible_url], // the url that should be loaded into the tab
	)
*/
$tabs = (isset($tabs)?$tabs:array());

// The id of the div to turn into tabs
$tabs_id = (isset($tabs_id)?$tabs_id:'tabs_'. rand(0, 1000));

$options = (isset($options)?$options:array());
$class = (isset($options['class'])?$options['class']:'');

$tabOptions = (isset($tabOptions)?$tabOptions:array());

/*
$this->Js->get('#'. $tabs_id)->tabs(array(
	'load' => "
	
	// hijack the paging links
	\$(ui.panel).delegate('.paging_link a', 'click', function(event) {
		event.preventDefault(); 
		\$(ui.panel).load(this.href);
	});
	
	// hijack the search form
	\$(ui.panel).delegate('form.tabform, .tabs form.advanced_search', 'submit', function(event) {
		event.preventDefault();
		// submit the form
		if(\$(this).attr( 'method' ) == 'post')
		{
			\$.post($(this).attr( 'action' ), $(this).serialize(), function( data ) {
					\$(ui.panel).html(data);
			});
		}
		else
		{
			\$.get($(this).attr( 'action' ), $(this).serialize(), function( data ) {
					\$(ui.panel).html(data);
			});
		}
	});
	
	// hijack the search form's clear button
	\$(ui.panel).delegate('form.tabform a.button', 'click', function(event) {
		event.preventDefault(); 
		\$(ui.panel).load(this.href);
	});
	
	// hijack links that are marked for hijacking
	\$(ui.panel).delegate('.tab-hijack', 'click', function(event) {
		event.preventDefault(); 
		\$(ui.panel).load(this.href);
	});
	
	",
	'select' => "window.location.hash = ui.tab.hash;",
	'cache' => false,
));
*/
?>
<div class="tabs">
	<div id="<?php echo $tabs_id; ?>">
		<ul class="tabs-nav">
			<?php foreach($tabs as $i => $tab): ?>
			<?php 
			$url = '#'; 
			if(isset($tab['url']))
			{
				$url = $tab['url'];
			}
			else
			{
				$url = '#'. $tabs_id. '-'.$tab['key']; 
			}
			?>
			<li class="tabs-nav-item tab-<?php echo $tab['key']; ?>"><?php echo $this->Html->link($tab['title'], $url); ?></li>
			<?php endforeach; ?>
		</ul>
		<?php foreach($tabs as $i => $tab): ?>
		<div id="<?php echo $tabs_id. '-'.$tab['key']; ?>" class="tab">
			<?php if(isset($tab['content'])) echo $tab['content']; ?>
		</div>
		<?php endforeach; ?>
	</div>
</div>
<?php
//echo $this->Js->writeBuffer();
?>
<script type="text/javascript">
$(document).ready(function()
{
	var tabOptions = <?= json_encode($tabOptions); ?>;
	$('div#<?php echo $tabs_id; ?>').objectTabs(tabOptions);
});
</script>