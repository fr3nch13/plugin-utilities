<?php 
// File: app/View/ProctimeQueries/admin_proctime.ctp


$page_options = array();

// content
$th = array(
	'ProctimeSqlStat.sql_source' => array('content' => __('Source'), 'options' => array('sort' => 'ProctimeSqlStat.sql_source')),
	'ProctimeSqlStat.sql_count' => array('content' => __('# Queries'), 'options' => array('sort' => 'ProctimeSqlStat.sql_count')),
	'ProctimeSqlStat.sql_time' => array('content' => __('Query Time'), 'options' => array('sort' => 'ProctimeSqlStat.sql_time')),
	'ProctimeSqlStat.created' => array('content' => __('Created'), 'options' => array('sort' => 'ProctimeSqlStat.created')),
);
$td = array();
foreach ($proctime_sql_stats as $i => $proctime_sql_stat)
{
	$td[$i] = array(
		$proctime_sql_stat['ProctimeSqlStat']['sql_source'],
		$proctime_sql_stat['ProctimeSqlStat']['sql_count'],
		$proctime_sql_stat['ProctimeSqlStat']['sql_time'],
		$this->Wrap->niceTime($proctime_sql_stat['ProctimeSqlStat']['created']),
	);
}

$use_multiselect = false;

echo $this->element('Utilities.page_index', array(
	'page_title' => __('Related %s', __('Sql Stats')),
	'page_options' => $page_options,
	'th' => $th,
	'td' => $td,
));
?>