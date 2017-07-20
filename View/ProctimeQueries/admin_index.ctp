<?php 
// File: app/View/ProctimeQueries/admin_proctime.ctp


$page_options = array();

// content
$th = array(
	'Proctime.pid' => array('content' => __('PID'), 'options' => array('sort' => 'Proctime.pid')),
	'ProctimeSqlStat.sql_source' => array('content' => __('Source'), 'options' => array('sort' => 'ProctimeSqlStat.sql_source')),
	'ProctimeQuery.sql_order' => array('content' => __('Order'), 'options' => array('sort' => 'ProctimeQuery.sql_order')),
	'ProctimeQuery.took_ms' => array('content' => __('Query Time'), 'options' => array('sort' => 'ProctimeQuery.took_ms')),
	'ProctimeQuery.num_rows' => array('content' => __('# Rows Found'), 'options' => array('sort' => 'ProctimeQuery.num_rows')),
	'ProctimeQuery.affected' => array('content' => __('# Rows Affected'), 'options' => array('sort' => 'ProctimeQuery.affected')),
	'ProctimeQuery.error' => array('content' => __('Error'), 'options' => array('sort' => 'ProctimeQuery.error')),
	'ProctimeQuery.created' => array('content' => __('Created'), 'options' => array('sort' => 'ProctimeQuery.created')),
//	'ProctimeQuery.query' => array('content' => __('Query'), 'options' => array('sort' => 'ProctimeQuery.query')),
);

$td = array();
$i = 0;
foreach ($proctime_queries as $proctime_query)
{
	$td[$i] = array(
		$proctime_query['Proctime']['pid'],
		$proctime_query['ProctimeSqlStat']['sql_source'],
		$proctime_query['ProctimeQuery']['sql_order'],
		$proctime_query['ProctimeQuery']['took_ms'],
		$proctime_query['ProctimeQuery']['num_rows'],
		$proctime_query['ProctimeQuery']['affected'],
		$proctime_query['ProctimeQuery']['error'],
		$this->Wrap->niceTime($proctime_query['ProctimeQuery']['created']),
//		$proctime_query['ProctimeQuery']['query'],
	);
	
	$i++;
	
	$td[$i] = array(
		'&nbsp;', 
		__('Query: '),
		$proctime_query['ProctimeQuery']['query'],
	);

	$i++;
}

echo $this->element('Utilities.page_index', array(
	'page_title' => __('All %s', __('Queries')),
	'page_options' => $page_options,
	'th' => $th,
	'td' => $td,
));
?>