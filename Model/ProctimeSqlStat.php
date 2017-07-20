<?php

App::uses('UtilitiesAppModel', 'Utilities.Model');

class ProctimeSqlStat extends UtilitiesAppModel 
{
	public $belongsTo = array(
		'Proctime' => array(
			'className' => 'Utilities.Proctime',
			'foreignKey' => 'proctime_id',
		),
	);
	
	public $hasMany = array(
		'ProctimeQuery' => array(
			'className' => 'Utilities.ProctimeQuery',
			'foreignKey' => 'proctime_sql_stat_id',
			'dependent' => true,
		),
	);
}