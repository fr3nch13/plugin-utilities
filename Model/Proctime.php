<?php

App::uses('UtilitiesAppModel', 'Utilities.Model');
App::uses('CakeSession', 'Model/Datasource');

class Proctime extends UtilitiesAppModel 
{
	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
		),
	);
	
	public $hasMany = array(
		'ProctimeSqlStat' => array(
			'className' => 'Utilities.ProctimeSqlStat',
			'foreignKey' => 'proctime_id',
			'dependent' => true,
		),
		'ProctimeQuery' => array(
			'className' => 'Utilities.ProctimeQuery',
			'foreignKey' => 'proctime_id',
			'dependent' => true,
		),
	);

	public function debugData()
	{
		
		return CakeSession::read('CommonComponent.sql_info');
	}
}