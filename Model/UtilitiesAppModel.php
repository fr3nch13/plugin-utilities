<?php

App::uses('Model', 'Model');
App::uses('AppModel', 'Model');

// load for all models
App::uses('AuthComponent', 'Controller/Component');

/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package       app.Model
 */
class UtilitiesAppModel extends AppModel 
{
//	public $useTable = false;

	public $actsAs = array(
		'Containable', 
		'Utilities.Common', 
		'Utilities.Extractor', 
		'Utilities.Foapi', 
		'Utilities.Rules', 
		'Utilities.Shell', 
		'Search.Searchable',
    );
	
	public function stats()
	{
	/*
	 * Default placeholder if no stats function is available for a Model
	 */
		return array();
	}
	
	public function objectToArray($obj) 
	{
		$arrObj = is_object($obj) ? get_object_vars($obj) : $obj;
		$arr = '';
		if($arrObj)
		{
			foreach ($arrObj as $key => $val) 
			{
				$val = (is_array($val) || is_object($val)) ? $this->Common_objectToArray($val) : $val;
				$arr[$key] = $val;
			}
		}
		return $arr;
	}
}
