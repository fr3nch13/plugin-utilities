<?php
/* 
 * Used to hold common rules for validation across all apps
 */

class RulesBehavior extends ModelBehavior 
{
	public $settings = array();
	
	private $_defaults = array();
	
	// allowed zip file mime types
	public $zipMimeTypes = array('application/zip', 'application/x-zip', 'application/x-zip-compressed', 'application/octet-stream', 'application/x-compress', 'application/x-compressed', 'multipart/x-zip');
	
	public function setup(Model $Model, $config = array()) 
	{
	/*
	 * Set everything up
	 */
		// merge the default settings with the model specific settings
		$this->settings[$Model->alias] = array_merge($this->_defaults, $config);
	}
	
	// Custom validation rules
	public function RuleMimeType(Model $Model, $check = false, $mimeTypes = array())
	{
		if(!is_array($check)) return false;
		$check = array_pop($check);
		if(!isset($check['type'])) return false;
		if(!in_array($check['type'], $mimeTypes)) return false;
		return true;
	}
	
	public function RuleZipMimeType(Model $Model, $check = false)
	{
		return $this->RuleMimeType($Model, $check, $this->zipMimeTypes);
	}
	
	public function RuleCompare(Model $Model, $check = false, $compare_field = false)
	{
		if(!is_array($check)) return false;
		$check = array_pop($check);
		
		$model = $Model->alias;
		if(stripos($compare_field, '.'))
		{
			list($model, $compare_field) = explode('.', $compare_field);
		}
		
		if(!isset($Model->data[$model][$compare_field])) return true;
		$compare = $Model->data[$model][$compare_field];
		if($check != $compare) return false;
		return true;
	}
	
	public function RuleRoleCheck(Model $Model, $check = false)
	{
		if(!is_array($check)) return false;
		$check = array_pop($check);
		
		$checked = $Model->roleCheck(false, $check);
		
		return $checked;
	}
}