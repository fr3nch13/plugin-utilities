<?php
$title = $this->get('dashboard_options_title');
$items = $this->get('dashboard_options_items');
?>
<div class="dashboard-options qtip-menu">
<span><?= $this->Html->link('<i class="fa fa-th fa-fw"></i> '. $title. '<i class="fa fa-caret-down fa-fw"></i>', '#', array('escape' => false));?></span>
<ul class="qtip-menu-list">
	<?php foreach($items as $item): ?>
	<li><?= $item ?></li>
	<?php endforeach; ?>
</ul>
</div>