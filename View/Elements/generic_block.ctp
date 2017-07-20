<?php 
$block_title = (isset($block_title)?$block_title:false);
$block_content = (isset($block_content)?$block_content:array());
?>
<div class="generic-block" style="word-wrap: break-word;">
	<h3 class="generic-block-title"><?php echo $block_title; ?></h3>
	<div class="generic-block-content"><?php echo $block_content; ?></div>
	
</div>