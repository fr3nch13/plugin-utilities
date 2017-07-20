<?php
echo $this->element('Utilities.page_dashboard', array(
	'page_title' => __('Dashboard: %s', __('My Overview')),
	'page_options_html' => $this->element('dashboard_options'),
	'dashboard_blocks' => $dbMyblocks,
	'dashboard_tabs' => $dbMytabs,
));