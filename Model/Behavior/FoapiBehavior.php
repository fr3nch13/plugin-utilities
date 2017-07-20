<?php
/* 
 * Used to hold api functions across all apps
 */

class FoapiBehavior extends ModelBehavior 
{
	public $settings = array();
	
	private $_defaults = array(
		'api_key_field' => 'api_key',
	);
	
	public function setup(Model $Model, $config = array()) 
	{
	/*
	 * Set everything up
	 */
		// merge the default settings with the model specific settings
		$this->settings[$Model->alias] = array_merge($this->_defaults, $config);
	}
	
	public function Foapi_authenticate(Model $Model, $api_key)
	{
	}
	
	public function Foapi_genApiKey(Model $Model, $user_id = false)
	{
		if(!$user_id) 
		{
			$Model->modelError = __('Unknown User (1)');
			return false;
		}
		
		if($Model->name !== 'User')
		{
			$Model->modelError = __('Unknown User (2)');
			return false;
		}
		
		App::uses('String', 'Utility');
		
		$Model->id = $user_id;
		$Model->set('api_key', String::uuid());
		return $Model->save();
	}
	
	public function Foapi_objectToArray($obj) 
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