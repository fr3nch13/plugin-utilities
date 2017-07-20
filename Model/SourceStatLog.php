<?php

App::uses('AppModel', 'Model');
/**
 * SourceStatLog Model
 *
 */
class SourceStatLog extends AppModel 
{
	public $belongsTo = array(
		'SourceStat' => array(
			'className' => 'SourceStat',
			'foreignKey' => 'source_stat_id',
		),
	);
	
	public $actsAs = array(
		'Utilities.Common', 
		'Utilities.Shell',
	);
	
	// define the fields that can be searched
	public $searchFields = array(
		'SourceStatLog.host',
	);
}