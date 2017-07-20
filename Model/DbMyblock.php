<?php

App::uses('UtilitiesAppModel', 'Utilities.Model');
class DbMyblock extends UtilitiesAppModel 
{
	public $manageUploads = false;
	
	public function beforeSave($options = array())
	{
		if(isset($this->data[$this->alias]['uri']) and is_array($this->data[$this->alias]['uri']))
		{
			$this->data[$this->alias]['uri'] = json_encode($this->data[$this->alias]['uri']);
		}
		return parent::beforeSave($options);
	}
	
	public function afterFind($results = [], $primary = false) 
	{
		foreach($results as $i => $result)
		{
			if(isset($result[$this->alias]['uri']))
			{
				$results[$i][$this->alias]['uri'] = $this->objectToArray(json_decode($results[$i][$this->alias]['uri']));
				if(isset($results[$i][$this->alias]['uri']['base']))
					unset($results[$i][$this->alias]['uri']['base']);
				
				if(isset($results[$i][$this->alias]['uri']['prefix']))
				{
					if($results[$i][$this->alias]['uri']['prefix'])
						$results[$i][$this->alias]['uri'][$results[$i][$this->alias]['uri']['prefix']] = true;
					unset($results[$i][$this->alias]['uri']['prefix']);
				}
				
				if(isset($results[$i][$this->alias]['uri']['passedArgs']))
				{
					$results[$i][$this->alias]['uri'] = array_merge($results[$i][$this->alias]['uri'], $results[$i][$this->alias]['uri']['passedArgs']);
					unset($results[$i][$this->alias]['uri']['passedArgs']);
				}
				
				foreach($results[$i][$this->alias]['uri'] as $k => $v)
				{
					if($v ==  'false')
						$results[$i][$this->alias]['uri'][$k] = false;
				}
			}
		}
		return parent::afterFind($results, $primary);
	}
}