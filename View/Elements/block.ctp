<?php 
/**
 * File: /app/View/Elements/block.ctp
 * 
 * Use: Builds blocks for pages like a dashboard
 *
 * Usage: echo $this->element('block', array('title' => [block title], 'items' => [items]));
 */

// title of the block
$title = (isset($title)?$title:'');

// items to list in the block
$items = (isset($items)?$items:array());

$style = (isset($style)?$style:'float:left;');

?>
<div class="block" style="<?php echo $style; ?>">
	<div class="block_title">
		<?php echo $title; ?>
	</div>
	<div class="block_items">
		<ul>
			<?php foreach($items as $item): ?>
			<li><?php echo $item; ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>