<?php
/* 
 * Allows an object to setup it's family using only parent_id as the key in the database table
 */

class FamilyBehavior extends ModelBehavior 
{
	public $settings = array();
	
	private $_defaults = array(
		/**
		 * How deep can the ancestry go? 
		 * 0 = single, yay no kids, but i'm an orphan, or a miracle
		 * 1 = parent, children only, not grandparents or grandchildren
		 * 2 = inlcudes the grand parents/children
		 * 3... = you get the idea
		 */
		'depth' => 1, 
		/**
		 * Used to determin who is my daddy/mommy
		 * it's here incase maybe you're already unsing this field name, and need to use another
		 */
		'relationshipKey' => 'parent_id',
		
	);
	
	// Used to map the finder methods below to the Model, instead of calling them directly
	private $findMethods = array(
		'AllParents' => true,
		'AllParentsList' => true,
		'AllChildren' => true,
		'AllChildrenList' => true,
		'NoFamily' => true,
		'NoFamilyList' => true,
		'MyChildren' => true,
		'MyChildrenList' => true,
		'MyParent' => true,
	);
	
	public $mapMethods = array('/_find(\w+)/' => 'finders');
	
	public function setup(Model $Model, $config = array()) 
	{
	/*
	 * Set everything up
	 */
		// merge the default settings with the model specific settings
		$this->settings[$Model->alias] = array_merge($this->_defaults, $config);
		
		$findMethods = $Model->findMethods;
		if($findMethods)
		{
			$this->findMethods = array_merge($this->findMethods, $findMethods);
		}
		$Model->findMethods = $this->findMethods;
		
		// this adds the associations of parent and child together
		
		$Model->belongsTo[$Model->name.'Parent'] = array(
			'className' => $Model->name,
			'foreignKey' => $this->settings[$Model->alias]['relationshipKey'],
			'conditions' => array(),
		);
		
		$Model->hasMany[$Model->name.'Child'] = array(
			'className' => $Model->name,
			'foreignKey' => $this->settings[$Model->alias]['relationshipKey'],
			'dependent' => false,
			'conditions' => array(),
		);
	}
	
	// able to map the finder methods to the below actual finder methods
	public function finders(Model $Model, $method, $arg1 = false, $arg2= false, $arg3 = false, $arg4 = false) 
	{
		if(!method_exists($this, $method))
		return false;
		
		$args = func_get_args();
		unset($args[1]); // the method
		return call_user_method_array($method, $this, $args);
	}
	
	/**
	 * Public finder methods.
	 * You should have the model's primaryKey id set using $Model->id, $this->id, or in the controller $this->{$model}->id
	 * Some of these methods will throw an exception if this isn't set
	 * unless obvious, these methods are basically wrappers around the Model::find() method
	 */

// Listing methods that DON'T need the $Model->id set
	public function _findAllParents(Model $Model, $state = false, $external_query = array(), $results = array()) 
	{
		if ($state !== 'before') 
		{
			return $this->_fixResults($Model, $state, $external_query, $results);
        }
			
		$internal_query = array(
			'conditions' => array(
				'OR' => array(
					$Model->alias.'.'. $this->settings[$Model->alias]['relationshipKey'] => NULL,
					$Model->alias.'.'. $this->settings[$Model->alias]['relationshipKey']. ' = ' => 0,
				),
			),
		);
		
		$query = $this->_fixQuery($Model, $external_query, $internal_query);
		return $query;
	}
	
	public function _findAllChildren(Model $Model, $state = false, $external_query = array(), $results = array()) 
	{
		if ($state !== 'before') 
		{
			return $this->_fixResults($Model, $state, $external_query, $results);
        }
			
		$internal_query = array(
			'conditions' => array(
				$Model->alias.'.'. $this->settings[$Model->alias]['relationshipKey']. ' NOT' => NULL,
				$Model->alias.'.'. $this->settings[$Model->alias]['relationshipKey']. ' >' => 0,
			),
		);
		
		$query = $this->_fixQuery($Model, $external_query, $internal_query);
		return $query;
	}
	
	public function _findNoFamily(Model $Model, $state = false, $external_query = array(), $results = array()) 
	{
		if ($state !== 'before') 
		{
			return $this->_fixResults($Model, $state, $external_query, $results);
        }
		
		$internal_query = $this->_findAllParents($Model, $state, array(), $results);		
		// find all of the things that are considered parents, then check to see if they have children
		
		$internal_query = array(
			'fields' => array(
				$Model->alias.'.'. $Model->primaryKey,
				$Model->alias.'.'. $Model->primaryKey,
			),
			'conditions' => $internal_query['conditions'],
			'recursive' => -1,
		);
		
		$parent_ids = $Model->find('list', $internal_query);
		
		foreach($parent_ids as $parent_id)
		{
			if($Model->find('count', array(
				'conditions' => array(
					$Model->alias.'.'. $this->settings[$Model->alias]['relationshipKey'] => $parent_id,
				)
			)))
			{
				unset($parent_ids[$parent_id]);
			}
		}
		
		$internal_query = array(
			'conditions' => array(
				$Model->alias.'.'. $Model->primaryKey => $parent_ids
			),
		);
		
		unset($parent_ids);
		
		$query = $this->_fixQuery($Model, $external_query, $internal_query);
		return $query;
	}
	
// Listing methods that DO need the $Model->id set
	public function _findMyParent(Model $Model, $state = false, $external_query = array(), $results = array()) 
	{
		$this->_checkId($Model);
		
		if ($state !== 'before') 
		{
			return $this->_fixResults($Model, $state, $external_query, $results);
        }
        
        $parent_id = $Model->field($this->settings[$Model->alias]['relationshipKey']);
			
		$internal_query = array(
			'conditions' => array(
				$Model->alias.'.'. $Model->primaryKey => $parent_id,
			),
		);
		
		$query = $this->_fixQuery($Model, $external_query, $internal_query);
		return $query;
	}
	
	public function _findMyChildren(Model $Model, $state = false, $external_query = array(), $results = array()) 
	{
		$this->_checkId($Model);
		
		if ($state !== 'before') 
		{
			return $this->_fixResults($Model, $state, $external_query, $results);
        }
			
		$internal_query = array(
			'conditions' => array(
				$Model->alias.'.'. $this->settings[$Model->alias]['relationshipKey'] => $Model->id,
			),
		);
		
		$query = $this->_fixQuery($Model, $external_query, $internal_query);
		return $query;
	}
	
// Internal methods
	protected function _checkId(Model $Model)
	{
		if(!$Model->id)
		{
			throw new InternalErrorException(__('Unknown Family Member (1).'));
		}
	}
	
	protected function _fixQuery(Model $Model, $external_query = array(), $internal_query = array())
	{
		// see if we're doing a list
		if(isset($internal_query['type']) and $internal_query['type'] == 'list')
		{
			if(!isset($internal_query['fields']))
				$internal_query['fields'] = NULL;
			
			if(!$internal_query['fields'])
			{
				$internal_query['fields'] = array(
					$Model->alias.'.'. $Model->primaryKey,
					$Model->alias.'.'. $Model->displayField,
				);
				$internal_query['recursive'] = -1;
			}
		}
		
		// our criteria override any criteria they provide
		$query = array_replace_recursive($external_query, $internal_query);
		return $query;
	}
	
	protected function _fixResults(Model $Model, $state = false, $external_query = array(), $results = array())
	{
		if ($state !== 'after') 
			return $results;
        
        if(!$results)
        	return $results;
        
        if(!isset($external_query['type']))
        	return $results;
        
        // 'all' isnt in the list, because the incoming results should already be in that format
        if(!in_array($external_query['type'], array('count', 'list')))
        	return $results;
        
        if($external_query['type'] == 'count')
        {
        	$result_count = count($results);
        	unset($results);
        	return $result_count;
        }
        
        if(!isset($external_query['fields']))
        {
			$external_query['fields'] = array(
				$Model->alias.'.'. $Model->primaryKey,
				$Model->alias.'.'. $Model->displayField,
			);
        }
        
        // all that is left is to make a list
        
        if(count($external_query['fields']) != 2)
        	return $results;
        
        $out = array();
        foreach($results as $i => $result)
        {
			$key = false;
			$value = false;
        	$result = Hash::flatten($result);
			
			if(isset($result[$external_query['fields'][0]]))
				$key = $result[$external_query['fields'][0]];
			if(isset($result[$external_query['fields'][1]]))
				$value = $result[$external_query['fields'][1]];
			
			$out[$key] = $value;
			unset($results[$i]);
        }
        
		return $out;
	}
}