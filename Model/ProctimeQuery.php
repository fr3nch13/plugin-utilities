<?php

App::uses('UtilitiesAppModel', 'Utilities.Model');
class ProctimeQuery extends UtilitiesAppModel 
{
	public $belongsTo = array(
		'ProctimeSqlStat' => array(
			'className' => 'Utilities.ProctimeSqlStat',
			'foreignKey' => 'proctime_sql_stat_id',
		),
		'Proctime' => array(
			'className' => 'Utilities.Proctime',
			'foreignKey' => 'proctime_id',
		),
	);
	
	public function beforeSave($options = array())
	{
		return parent::beforeSave($options);
	}
	
	public function afterSave($created = false, $options = array())
	{
		// check if we have the recacher plugin loaded
		// if not, try to load it
		// if loaded, send this to the cacher plugin for processing
		if(CakePlugin::loaded('Cacher'))
		{
//			$this->Cacher_track($this->data);
		}
		
		return parent::afterSave($created, $options);
	}
}