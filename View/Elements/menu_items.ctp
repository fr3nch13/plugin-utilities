<?php
// ability to dynamically add menu items
// takes the url to the list of items to be used in the 'requestAction'
// items format is array( array( 'menu name' => 'url'))

//// review the options

// to use the <ul></ul> tags
$use_ul = (isset($use_ul)?$use_ul:true);

// url to the items to use in the list
// must return a speficif format
// items format is array( array( 'title' => '[title]', 'url' => 'url'))
$request_url = (isset($request_url)?$request_url:false);
?>

<?php  if($request_url): ?>
	<?php $items = $this->requestAction($request_url); ?>
	<?php if($use_ul):?><ul><?php endif; ?>
		<?php if($items): ?>
		<?php foreach($items as $item): ?>
			<li><?php echo $this->Html->link(($item['title']?trim($item['title']):'&nbsp;'), $item['url']); ?></li>
		<?php endforeach; ?>
		<?php endif; ?>
	<?php if($use_ul):?></ul><?php endif; ?>
<?php  endif;?>