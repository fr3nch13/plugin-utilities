<?php

$dashboard_id = (isset($dashboard_id)?$dashboard_id:'dashboard_'. rand(1,1000));
$dashboard_blocks_id = ((isset($dashboard_blocks_id)?$dashboard_blocks_id:$dashboard_id.'_blocks'));
$dashboard_blocks = (isset($dashboard_blocks)?$dashboard_blocks:array());
$dashboard_options = (isset($dashboard_options)?$dashboard_options:array());
?>
<div class="dashboard-options no-print">
	<div class="dashboard-options-content">
		<div class="dashboard-block-toggler">
			<select>
				<option value=""><?= __('Show/Hide Blocks') ?></option>
			</select>
		</div>
	</div>
</div>
<div class="dashboard-blocks-wrapper">
	<ul id="<?= $dashboard_blocks_id?>" sortable="<?= $dashboard_blocks_id?>" class="dashboard-blocks" data-columns>
		<?php foreach($dashboard_blocks as $dashboard_block_id => $dashboard_block_url): ?>
		<li class="dashboard-block" id="<?=$dashboard_block_id ?>" href="<?= $this->Html->url($dashboard_block_url); ?>">
			<div class="dashboard-block-outer-wrapper">
				<div class="dashboard-block-loading"><i class="fa fa-spinner fa-spin"></i></div>
				<div class="dashboard-block-inside"></div>
			</div>
		</li>
		<?php endforeach; ?>
	</ul>
	<div class="clearb"></div>
</div>