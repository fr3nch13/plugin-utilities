<?php
/* 
 * Used to hold common functions across all apps
 */

App::uses('Hash', 'Core');
App::uses('CakeEmail', 'Network/Email');
App::uses('CookieComponent', 'Controller/Component');
class CommonBehavior extends ModelBehavior 
{
	public $settings = array();
	
	private $_defaults = array();
	
	public $Cookie = false;
	public $cookie_expire = '+1 day';
	
	// placeholder incase there was a validation error
	// we can load and document a validation error
	public $ValidationError = false;
	
	///// Between //~~~ and //~~~// these are defaults if they're not set in the Model
	// don't reference these, reference the ones in the Model
	
	// options when you are modifying multiple objects
	public $multiselectOptions = array();

	// tracks where to send the user after the multiselect process is complete
	public $multiselectReferer = false;
	
	// fields that are boolean and can be toggled
	public $toggleFields = array();
	
	// fields that are boolean and marks the record as the default
	// e.g. only one record can be 1, the rest are 0
	public $defaultFields = array();
	
	// if we should include the counts
	// format: array([associatedModel] => array(['count_key'] => array([find conditions]) ) )
	public $getCounts = false;
	
	public $emptyOption = '[ Select ]';
	
	// set whenever an error occurs and the methods in a model need to return a false
	// the default error when none gets set
	public $modelError = 'Unable to save/update the record.';
	
	// the final results message when needed.
	public $modelResults = false;
	
	// set when a zip file is processed
	public $zipDir = false;
	
	public $zipMimeTypes = array();
	
	// used to map column names to readable states
	public $mappedFields = false;
	
	// tracks any errors that happens when a temp item is being reviewed to go active
	public $reviewError = false;
	
	// final results when a method is called that is meant to run from the shell
	public $final_results = false;
	public $final_result_count = false;
	
	// have common behavior manage the uploading and deleting of a model's files.
	// set this to the field, from a form
	// format: array('form_field' => [field_name], 'db_field' => [field in the database that holds the filename])
	public $manageUploads = false;
	public $delete_file_path = false;
	
	// conditions for the typeFormList()
	public $typeFormListConditions = false;
	public $typeFormListOrder = false;
	
	// used to help update host information for an object
	public $hostLookupFields = array(
		'ipaddress' => false,
		'hostname' => false,
	);
	
	public $CachedCounts_key = false;
	public $CachedCounts_cache = array();
	public $CachedCounts_config = 'CachedCounts';
	
	
	//~~~//
	
	public function setup(Model $Model, $config = array()) 
	{
	/*
	 * Set everything up
	 */
		// merge the default settings with the model specific settings
		$this->settings[$Model->alias] = array_merge($this->_defaults, $config);
		
		// set -1 as the Model's default recursion
		$Model->recursive = -1;
		
		$this->_checkVariable($Model, 'multiselectOptions');
		$this->_checkVariable($Model, 'multiselectReferer');
		$this->_checkVariable($Model, 'toggleFields');
		$this->_checkVariable($Model, 'defaultFields');
		$this->_checkVariable($Model, 'getCounts');
		$this->_checkVariable($Model, 'emptyOption');
		
		$this->modelError = __('Unable to save/update the %s.', Inflector::humanize($Model->alias));
		$this->_checkVariable($Model, 'modelError');
		$this->_checkVariable($Model, 'modelResults');
		$this->_checkVariable($Model, 'zipDir');
		$this->_checkVariable($Model, 'zipMimeTypes');
		$this->_checkVariable($Model, 'mappedFields');
		$this->_checkVariable($Model, 'reviewError');
		$this->_checkVariable($Model, 'final_results');
		$this->_checkVariable($Model, 'final_result_count');
		$this->_checkVariable($Model, 'manageUploads');
		$this->_checkVariable($Model, 'typeFormListConditions');
		$this->_checkVariable($Model, 'typeFormListOrder');
		$this->_checkVariable($Model, 'hostLookupFields');
		$this->_checkVariable($Model, 'CachedCounts_key');
		
		// load the Filter Plugin's Behavior
		if(CakePlugin::loaded('Filter'))
		{
			if(!$Model->Behaviors->loaded('Filter'))
			{
				$Model->Behaviors->load('Filter.Filter');
			}
		}
	}
	
	public function _checkVariable(Model $Model, $variable_name)
	{
		if(!$variable_name) return false;
		
		if(!isset($Model->{$variable_name}))
		{
			$Model->{$variable_name} = $this->{$variable_name};
		}
	}
///////////////////// Callbacks

	public function afterValidate(Model $Model)
	{
		// track the validation errors in the database
		if($Model->validationErrors)
		{
			if(!$this->ValidationError)
			{
				App::import('Utilities.Model', 'ValidationError');
				$this->ValidationError = new ValidationError();
			}
			
			$this->ValidationError->saveErrors($Model);
		}

		return parent::afterValidate($Model);
	}
	
	public function beforeSave(Model $Model, $options = array())
	{
		// auto track the orginization groups
		if($Model->hasField('org_group_id'))
		{
			if(isset($Model->data[$Model->alias]['org_group_id']) and !$Model->data[$Model->alias]['org_group_id'])
			{
				$Model->data[$Model->alias]['org_group_id'] = 0;
			}
		}
		
		// if we're allowing common behavior to manage the model's files, do so.
		if(!$this->checkUploadedFile($Model))
		{
			return false;
		}
		
		if(isset($Model->data[$Model->alias]) and is_array($Model->data[$Model->alias]))
		{
			foreach($Model->data[$Model->alias] as $field => $value)
			{
				// make sure xref fields are properly set
				if(preg_match('/_id$/', $field) and !$value)
				{
					$Model->data[$Model->alias][$field] = 0;
				}
				
				// double check dates. 
				if(preg_match('/_false$/', $field))
				{
					$actualField = str_replace('_false', '', $field);
					// If they were set with the date picker, verify them
					// If the user cleared the date, but the datepicker didn't catch it, go with the user input
					if(isset($Model->data[$Model->alias][$actualField]))
					{
						if(!$Model->data[$Model->alias][$field])
							$Model->data[$Model->alias][$actualField] = false;
					}
					
					// get rid of those '000-00-00 00:00:00', they should be null/false
					if($Model->data[$Model->alias][$actualField] == '000-00-00 00:00:00')
						$Model->data[$Model->alias][$actualField] = false;
					if($Model->data[$Model->alias][$field] == '-0001-11-30 00:00:00')
						$Model->data[$Model->alias][$actualField] = false;
						
				}
			}
		}
		
		return parent::beforeSave($Model, $options);
	}

	public function afterSave(Model $Model, $created = false, $options = array())
	{
		// clear the lists caches
		$this->clearCachedCounts($Model);
		
		// if we're allowing common behavior to manage the model's files, do so.
		if(!$this->processUploadedFile($Model))
		{
			return false;
		}
		
		return parent::afterSave($Model, $created, $options);
	}
	
	public function beforeFind(Model $Model, $query = [])
	{
		return parent::beforeFind($Model, $query);
	}
	
	public function afterFind(Model $Model, $results = array(), $primary = false)
	{
		// using file management

		if($Model->manageUploads !== false)
		{
			foreach($results as $i => $result)
			{
				if(isset($result[$Model->alias]))
					$results[$i] = $this->attachFilePaths($Model, $result);
			}
		}
		
		// get the defined counts for this record of it's associated records based on a passed condition
		$results =  $this->getCounts($Model, $results);
		
		foreach($results as $i => $result)
		{
			/// fix the color stuff
			if(isset($results[$i][$Model->alias]['color_code_hex']))
			{
				if(!$results[$i][$Model->alias]['color_code_hex']) 
				{
					$salt = false;
					if(isset($result[$Model->alias][$Model->displayField]))
						$salt = $result[$Model->alias][$Model->displayField];
					elseif($result[$Model->alias][$Model->primaryKey])
						$salt = $result[$Model->alias][$Model->primaryKey];
					$results[$i][$Model->alias]['color_code_hex']  = $this->makeColorCode($Model, $salt);
				}
				
				$results[$i][$Model->alias]['color_code_rgb'] = $this->makeRGBfromHex($Model, $results[$i][$Model->alias]['color_code_hex']);
				$results[$i][$Model->alias]['color_code_border'] = $this->makeBorderColor($Model, $results[$i][$Model->alias]['color_code_hex']);
			}
		}
		
		return $results;
	}
	
	public function beforeDelete(Model $Model, $cascade = false)
	{
		// if we're allowing common behavior to manage the model's files, do so.
		$this->checkDeletingFile($Model);
		
		return parent::beforeDelete($Model, $cascade);
	}
	
	public function afterDelete(Model $Model)
	{
		$this->clearCachedCounts($Model);
		
		// if we're allowing common behavior to manage the model's files, do so.
		$this->processDeletingFile($Model);
		
		return parent::afterDelete($Model);
	}
	
	public function field(Model $Model, $name, $conditions = null, $order = null)
	{
/*
		$cache_settings = false;
		$auto_setting = false;
		if($Model->Behaviors->loaded('Cache'))
		{
			$cache_settings = $Model->Behaviors->dispatchMethod($Model, 'settings');
			$auto_setting = (isset($cache_settings['auto'])?$cache_settings['auto']:false);
			if(!$auto_setting)
			{
				$Model->Behaviors->load('Cache', array('auto' => true));
			}
		}
*/
		
		$results = $Model->field($name, $conditions, $order);
		
/*
		if($Model->Behaviors->loaded('Cache'))
		{
			$Model->Behaviors->load('Cache', array('auto' => $auto_setting));
		}
*/
		return $results;
	}
	
	public function loadModel(Model $Model, $modelName, $options = array()) {
        if (is_string($options)) $options = array('alias' => $options);
        $options = array_merge(array(
            'datasource'  => 'default',
            'alias'       => false,
            'id'          => false,
        ), $options);
        list($plugin, $className) = pluginSplit($modelName, true, null);
        if (empty($options['alias'])) $options['alias'] = $className;
        if (!isset($Model->{$options['alias']}) || $Model->{$options['alias']}->name !== $className) {
            if (!class_exists($className)) {
                if ($plugin) $plugin = "{$plugin}.";
                App::import('Model', "{$plugin}{$modelClass}");
            }
            $table = Inflector::tableize($className);
            if (PHP5) {
                $Model->{$options['alias']} = new $className($options['id'], $table, $options['datasource']);
            } else {
                $Model->{$options['alias']} =& new $className($options['id'], $table, $options['datasource']);
            }
            if (!$Model->{$options['alias']}) {
                return $Model->cakeError('missingModel', array(array(
                    'className' => $className, 'code' => 500
                )));
            }
            $Model->{$options['alias']}->alias = $options['alias'];
        }
        $Model->{$options['alias']}->create();
        return true;
    }
	
	
/////////////////////
	public function isOwnedBy(Model $Model, $id, $user_id) 
	{
		return $this->field($Model, 'id', array('id' => $id, 'user_id' => $user_id)) === $id;
	}
	
	public function isActive(Model $Model, $id) 
	{
		return $this->field($Model, 'active', array('id' => $id));
	}
	
	public function isPublic(Model $Model, $id) 
	{
		return $this->field($Model, 'public', array('id' => $id));
	}
	
	public function isSameOrgGroup(Model $Model, $id = false, $user_org_group_id = 0) 
	{
	/*
	 * Checks if this object is active
	 */
		if(!$user_org_group_id) $user_org_group_id = 0;
		
		$org_group_id = $this->field($Model, 'org_group_id', array('id' => $id));
		if(!$org_group_id) $org_group_id = 0;
		if($user_org_group_id === $org_group_id)
		{
			return true;
		}
		
		return false;
	}
	
	public function Common_readGlobalObject(Model $Model)
	{
		$db = $Model->getDataSource();
		$fields = $db->describe($Model->useTable);
		$out = array();
		foreach($fields as $name => $attr)
		{
			$value = false;
			if($attr['type'] == 'integer') $value = 0;
			if($attr['type'] == 'datetime') $value = 0;
			if($attr['type'] == 'string')
			{
				if($name == 'name') $value = __('Global');
			}
			
			$out[$Model->alias][$name] = $value;
		}
		return $out;
	}
	
	public function userRoles(Model $Model, $nice = true)
	{
		$roles = Configure::read('Routing.prefixes');
		
		$out = array();
		foreach($roles as $role)
		{
			$role_k = $role;
			$role_v = $role;
			if($nice)
			{
				$role_v = Inflector::humanize($role);
			}
			$out[$role_k] = $role_v;
		}
		
		return $out;
	}
	
	public function roleCheck(Model $Model, $roles = false, $user_role = false)
	{
		if(!$roles)
		{
			$roles = $this->userRoles($Model, false);
		}
		
		if(!$user_role)
		{
			$user_role = AuthComponent::user('role');
			if(!$user_role) return false;
		}
		
		if(!is_array($roles))
		{
			$roles = array($roles);
		}
		
		if(in_array($user_role, $roles)) 
		{
			return true;
		}
		return false;
	}
	
	public function relatedIds(Model $Model, $string = false, $relation = false, $field = false)
	{
	/*
	 * Used to get the list of related ids based on a string given
	 */
		if(!$string) return false;
		if(!$relation) return false;
		
		if(!$field) $field = $Model->displayField;
		
		// fix the relation to get the 
		$foreignKey = $Model->hasAndBelongsToMany[$relation]['foreignKey'];
		$associationForeignKey = $Model->hasAndBelongsToMany[$relation]['associationForeignKey'];
		$xrefModel = $Model->hasAndBelongsToMany[$relation]['with'];
		
		$relatedIds = $Model->{$xrefModel}->find('list', array(
			'recursive' => 0,
			'conditions' => array(
				$Model->alias.'.'.$field => strtolower($string),
			),
			'fields' => array(
				$xrefModel.'.'.$foreignKey,
			)
		));
		return $relatedIds;
	}
	
	public function toggleRecord(Model $Model, $id = null, $field = false, $extra_info = array())
	{
	/*
	 * Toggles a boolean record in the database
	 */
		// make sure this field can be modified
		// (set in the specific models)
		if(!$field or !in_array($field, $Model->toggleFields))
		{
			$Model->modelError = __('Invalid field.');
			return false;
		}
		
		if(!$id)
		{
			$Model->modelError = __('Unknown id.');
			return false;
		}
		
		// set the id
		$Model->id = $id;
		
		// make sure the record exists
		if(!$Model->exists())
		{
			$Model->modelError = __('The record doesn\'t exist.');
			return false;
		}
		
		$value = (int)$Model->field($field, array($Model->primaryKey => $id));
		
		$Model->toggleOldValue = $value;
		$value = ($value == 0?1:0);
		$Model->toggleNewValue = $value;
		
		// update the record
		$Model->data = array();
		$Model->id = $id;
		$Model->set($field, $value);
		
		// see if there is a related user_id, and a related _date field
		$model_schema = $Model->schema();
		
		if(isset($model_schema[$field. '_user_id']) and AuthComponent::user('id'))
		{
			$Model->set($field. '_user_id', AuthComponent::user('id'));
		}
		
		if(isset($model_schema[$field. '_date']))
		{
			$Model->set($field. '_date', date('Y-m-d H:i:s'));
		}
		
		if($extra_info)
		{
			foreach($extra_info as $ufield => $uvalue)
			{
				$Model->set($ufield, $uvalue);
			}
		}
		
		return $Model->save();
	}
	
	public function transferRecords(Model $Model, $current_id = false, $data = false)
	{
		if(!$current_id)
		{
			$Model->modelError = __('Unknown Current %s', __($Model->alias));
			return false;
		}
		
		if(!$data)
		{
			$Model->modelError = __('Unknown Current %s', __($Model->alias));
			return false;
		}
		
		$associations = $Model->getAssociated();
		$allowed_associations = array('hasOne', 'hasMany', 'hasAndBelongsToMany');
		foreach($associations as $model => $association)
		{
			// only deal with relationships that this object owns
			if(!in_array($association, $allowed_associations)) 
			{ 
				unset($associations[$model]); 
				continue; 
			}
			
			// try to load the associated model, if it isn't
			
			// if it's an habtm with a 'with', bind the with as a has many
			if($association == 'hasAndBelongsToMany' and isset($associations[$model]['with']))
			{
				$bind_conditions = $associations[$model]['conditions'];
				$bind_model = $associations[$model]['with'];
				if(stripos($bind_model, '.'))
					list($bind_plugin, $bind_model) = explode('.', $associations[$model]['with']);
					
				$Model->bindModel(array(
					'hasMany' => array(
						$associations[$model]['with'] => array(
							'className' =>$bind_model,
							$bind_conditions
						)
					)
        		));
        	}
			elseif(!isset($Model->{$model}))
			{
				$this->loadModel($Model, $model);
			}
			
			// get the details of the relationship
			if(isset($Model->{$association}[$model]) and $Model->{$model})
			{
				$associations[$model] = $Model->{$association}[$model];
				$associations[$model]['association'] = $association;
			}
		}
		
		$updated = 1;
		// transfer the associated objects from the one being deleted to the new one
		foreach($data as $associationModelName => $details)
		{
			// find the relationship
			if(!isset($associations[$associationModelName])) continue;
			
			if(!isset($details['new_id']) or !$details['new_id']) continue;
			
			/// if habtm, update the xref instead of the others
			if($associations[$associationModelName]['association'] == 'hasAndBelongsToMany' and isset($associations[$associationModelName]['with']))
			{
			
				$with_model_alias = $associations[$associationModelName]['with'];
				if(stripos($with_model_alias, '.'))
				{
					list($with_plugin, $with_model_alias) = explode('.', $with_model_alias);
				}
			
				if(!isset($Model->{$with_model_alias}))
				{
					$Model->loadModel($associations[$associationModelName]['with']);
				}
			
				$foreignKey = $associations[$associationModelName]['foreignKey'];
				// generate the conditions to update the related model
				$fields = array(
					$with_model_alias. '.'. $foreignKey => $details['new_id'],
				);
				$conditions = array(
					$with_model_alias. '.'. $foreignKey => $current_id,
				);
				if(isset($associations[$associationModelName]['conditions']) and is_array($associations[$associationModelName]['conditions']))
				{
					$conditions = array_merge($associations[$associationModelName]['conditions'], $conditions);
				}
				
				if($Model->{$with_model_alias}->updateAll($fields, $conditions)){ $updated++; }
			}
			else
			{
				$foreignKey = $associations[$associationModelName]['foreignKey'];
				
				// generate the conditions to update the related model
				$fields = array(
					$associationModelName. '.'. $foreignKey => $details['new_id'],
				);
				$conditions = array(
					$associationModelName. '.'. $foreignKey => $current_id,
				);
				if(isset($associations[$associationModelName]['conditions']) and is_array($associations[$associationModelName]['conditions']))
				{
					$conditions = array_merge($associations[$associationModelName]['conditions'], $conditions);
				}
				if($Model->{$associationModelName}->updateAll($fields, $conditions)){ $updated++; }
			}
		}
		
		return $updated;
	}
	
	public function defaultRecord(Model $Model, $id = null, $field = false)
	{
		// make sure this field can be modified
		// (set in the specific models)
		if(!$field or !in_array($field, $Model->defaultFields))
		{
			$Model->modelError = __('Invalid field.');
			return false;
		}
		
		if(!$id)
		{
			$Model->modelError = __('Unknown id.');
			return false;
		}
		
		// set the id
		$Model->id = $id;
		
		// make sure the record exists
		if(!$Model->exists())
		{
			$Model->modelError = __('The record doesn\'t exist.');
			return false;
		}
		
		$conditions = array($Model->alias. '.'. $field => 1);
		if($Model->hasField('org_group_id'))
		{
			$org_group_id = $Model->field('org_group_id');
			if(!$org_group_id) $org_group_id = 0;
			$conditions[$Model->alias. '.org_group_id'] = $org_group_id;
		}
		
		// mark all as 0
		$Model->updateAll(
			array($Model->alias. '.'. $field => 0),
			$conditions
		);
		
		// mark this record as 1
		return $Model->saveField($field, 1);
	}
	
	public function defaultId(Model $Model, $org_group_id = 0, $field = false)
	{
		$conditions = array();
		if($Model->hasField('org_group_id'))
		{
			$conditions = array($Model->alias.'.org_group_id' => $org_group_id);
		}
		if($Model->defaultFields)
		{
			if(!is_array($Model->defaultFields)) $Model->defaultFields = array($Model->defaultFields);
			
			if($field and in_array($field, $Model->defaultFields))
			{
				$Model->defaultFields = array($field);
			}
			
			foreach($Model->defaultFields as $field)
			{
				$conditions[$Model->alias.'.'.$field] = 1;
			}
		}
		if($id = $Model->field($Model->alias.'.id', $conditions))
		{
			return $id;
		}
		return false;
	}
	
	public function getCounts(Model $Model, $results = array())
	{
		// get the defined counts for this record of it's associated records based on a passed condition
		if(isset($Model->getCounts) and $Model->getCounts !== false and $associations = $Model->getAssociated())
		{
			$allowed_associations = array('hasOne', 'hasMany', 'hasAndBelongsToMany');
			
			foreach($results as $i => $result)
			{
				// bypass the pagination counts
				if(!isset($results[$i][$Model->alias])) continue;
				
				$_counts = array();
				foreach($Model->getCounts as $alias => $counts)
				{
					foreach($counts as $count_key => $conditions)
					{
						$count_key = $alias.'.'.$count_key;
						$count = 0;
						
						// force the cache to look for cached results
						// only works if Cacher.cache plugin is loaded
						if($Model->Behaviors->loaded('Cache'))
						{
							$conditions['cacher'] = true;
						}
						
						// load the model
						if(!is_object($Model->{$alias}))
						{
							App::uses($alias, 'Model');
							if(class_exists($alias)) $Model->{$alias} = new $alias;
						}
						
						if(is_object($Model->{$alias}))
						{
							$count = 0;
							if(isset($conditions['conditions']))
							{
								$count = $Model->{$alias}->getCachedCounts('count', $conditions);
							}
							// make up our own assuming it is a many, and they want the total count
							// hey, not defined conditions
							else
							{
								$association = false;
								$foreignKey = false;
								// find out their relationship
								if(isset($associations[$alias]))
								{
									$association = $associations[$alias];
								}
								if($association and isset($Model->{$association}[$alias]['foreignKey']))
								{
									$foreignKey = $Model->{$association}[$alias]['foreignKey'];
								}
								
								if($foreignKey)
								{
									$conditions['conditions'] = array(
										$alias.'.'. $foreignKey => $result[$Model->alias][$Model->primaryKey],
									);
									$count = $Model->{$alias}->getCachedCounts('count', $conditions);
								}
							}
							$count = ($count?$count:0);
						}
						$_counts[$count_key] = $count;
					}
				}
				$results[$i][$Model->alias]['counts'] = $_counts;
			}
		}
		return $results;
	}
	
	public function getCachedCounts(Model $Model, $findType = 'all', $query = array())
	{
		if(isset($query['getcount']) and isset($query['limit']))
			unset($query['limit']);
		if(isset($query['getcount']) and isset($query['maxLimit']))
			unset($query['maxLimit']);
		
		$this->CachedCount_config($Model);
		
		$key = $this->CachedCounts_key($Model, $findType, $query);

		if(array_key_exists($Model->name, $this->CachedCounts_cache) and array_key_exists($key, $this->CachedCounts_cache[$Model->name]))
		{
			return $this->CachedCounts_cache[$Model->name][$key];
		}
		
		$results = Cache::read($key, $this->CachedCounts_config);
		
		if($results === false)
		{
			$results = $Model->find($findType, $query);
			$this->saveCachedCounts($Model, $results, $findType, $query);
			
			if(!isset($this->CachedCounts_cache[$Model->name]))
				$this->CachedCounts_cache[$Model->name] = array();
			
			$this->CachedCounts_cache[$Model->name][$key] = $results;
		}
		
		return $results;
	}
	
	public function saveCachedCounts(Model $Model, $results = null, $findType = 'all', $query = array())
	{
		$this->CachedCount_config($Model);
		
		$key = $this->CachedCounts_key($Model, $findType, $query);
		
		if(!$results)
			$results = null;
		
		Cache::write($key, $results, $this->CachedCounts_config);
	}
	
	public function clearCachedCounts(Model $Model)
	{
		$this->CachedCount_config($Model);
		Cache::clear(false, $this->CachedCounts_config);
	}
	
	public function CachedCounts_key(Model $Model, $findType = 'all', $criteria = array())
	{
		$this->CachedCount_config($Model);
		
		$cachedKey = [
			$findType
		];
		
		if($Model->id)
			$cachedKey[] = $Model->id;
		
		$cachedKey[] = md5(json_encode($criteria));
		
		$cachedKey = implode('.', $cachedKey);
		$this->CachedCounts_key = $Model->CachedCounts_key = $cachedKey;
		
		return $cachedKey;
	}
	
	public function CachedCount_config(Model $Model)
	{
		$settings = Cache::settings($this->CachedCounts_config);
		$settings['path'] = CACHE.'CachedCounts'.DS.$Model->name.DS;
		if(!is_dir($settings['path']))
		{
			umask(0);
			mkdir($settings['path'], 0777, true);
		}
		
		// kill, and reinitialize the Cache
		Cache::drop($this->CachedCounts_config);
		Cache::config($this->CachedCounts_config, $settings);
	}
	
	public function typeFormList(Model $Model, $org_group_id = 0, $include_global = true)
	{
	/*
	 * Provides a list to be used in the dropdown forms
	 * Specific to object types like category_types
	 */
		// default settings
		$conditions = ($Model->typeFormListConditions?$Model->typeFormListConditions:array());
		$fields = array($Model->alias. '.'. $Model->primaryKey, $Model->alias. '.'. $Model->displayField);
		$recursive = -1;
		$order = ($Model->typeFormListOrder?$Model->typeFormListOrder:array());
		
		if($Model->hasField('holder'))
		{
			$order[$Model->alias. '.holder'] = 'desc';
		}
		
		if($Model->hasField('name'))
		{
			$order[$Model->alias. '.name'] = 'asc';
		}
		
		if($Model->hasField('active'))
		{
			$conditions[$Model->alias. '.active'] = 1;
		}
		
		if($Model->hasField('org_group_id'))
		{
			if($include_global)
			{
				$recursive = 0;
				$fields[] = 'OrgGroup.id';
				$fields[] = 'OrgGroup.name';
				array_unshift($order, 'OrgGroup.name');
				$org_group_id = array($org_group_id, 0);
			}
			$conditions[$Model->alias. '.org_group_id'] = $org_group_id;
		}
		
		$query = array(
			'conditions' => $conditions,
			'recursive' => $recursive,
			'order' => $order,
			'fields' => $fields,
		);
		
		$types = $Model->getCachedCounts('all', $query);
		
		// format the types into a list
		$types_formatted = array();
		if($types)
		{
			foreach($types as $type)
			{
				$id = $type[$Model->alias][$Model->primaryKey];
				$name = $type[$Model->alias][$Model->displayField];
				if($include_global and isset($type['OrgGroup']['name']) and $Model->alias != 'OrgGroup')
				{
					$name = '['.$type['OrgGroup']['name'].'] '. $name;
				}
				$types_formatted[$id] = $name;
			}
		}
		return $types_formatted;
	}
	
	public function typeFormListBlank(Model $Model, $org_group_id = 0, $include_global = true)
	{
		$types_formatted = $this->typeFormList($Model, $org_group_id = 0, $include_global = true);
		$types_formatted = ['[ Select ]'] + $types_formatted;
		return $types_formatted;
	}
	
	public function typeFormListAppend(Model $Model, $append = array(), $org_group_id = 0, $include_global = true)
	{
		$types_formatted = $this->typeFormList($Model, $org_group_id = 0, $include_global = true);
		return array_replace($types_formatted, $append);
	}
	
	public function gridAdd(Model $Model, $data = array())
	{
		if(!$data)
		{
			return false;
		}
		
		if(!is_array($data))
		{
			return false;
		}
		
		if(isset($data['multiple']))
			unset($data['multiple']);
		
		if(isset($data[$Model->alias][$Model->primaryKey]))
			unset($data[$Model->alias][$Model->primaryKey]);
		
		foreach($data as $model_alias => $values)
		{
			if(is_object($Model->{$model_alias}))
			{
				$primaryKey = $Model->{$model_alias}->primaryKey;
				if(isset($values[$primaryKey]))
					unset($data[$model_alias][$primaryKey]);
			}
		}
		
		$Model->create();
		$Model->data = $data;
		
		$saved = false;
		$dataCount = count($Model->data);
		if($dataCount == 1)
		{
			if($Model->save($Model->data))
			{
				$data['saveMethod'] = 'save';
				$saved = true;
			}
		}
		else
		{
			if($Model->saveAssociated($Model->data))
			{
				$data['saveMethod'] = 'saveAssociated';
				$saved = true;
			}
		}
		
		if($saved)
		{
			$recursive = -1;
			if($dataCount > 1)
				$resursive = 1;
			
			$record = $Model->find('first', array(
				'recursive' => ($dataCount > 1?1:-1),
				'conditions' => array($Model->alias.'.'.$Model->primaryKey => $Model->id)
			));
			return $record;
			
		}
		return false;
	}
	
	public function gridEdit(Model $Model, $data = array())
	{
		$Model->modelError = false;
		
		if(!$data)
		{
			$Model->modelError = __('(1) Not data was given');
			return false;
		}
		
		if(!is_array($data))
		{
			$Model->modelError = __('(2) No data was given');
			return false;
		}
		
		if(isset($data['multiple']))
			unset($data['multiple']);
		
		// changing how gridedit works
		$returnData = [];
		foreach($data as $modelAlias => $modelData)
		{
			if($Model->alias == $modelAlias)
			{
				$Model->id = false;
				if(isset($modelData[$Model->primaryKey]))
					$Model->id = $modelData[$Model->primaryKey];
		
				if(!$Model->id)
				{
					$Model->modelError = __('Unknown ID for %s', __($Model->alias));
					continue;
				}
		
				$Model->data = [$modelAlias => $modelData];
				
				if($Model->save($Model->data))
				{
					$modelData = $Model->read(null, $Model->id);
					$returnData[$modelAlias] = $modelData[$Model->alias];
					$returnData['saveMethod'] = 'save';
					$returnData['message'] = __('This %s was updated successfully (ID: %s)', __($Model->alias), $Model->id);
				}
				else
				{
					$Model->modelError = __('(1) Unable to update the %s - Validation Errors: %s', __($Model->alias), implode(', ', $Model->validationErrors));
				}
			}
			else
			{
				if(!isset($Model->{$modelAlias}))
				{
					$Model->{$modelAlias} = ClassRegistry::init(['class' => $modelAlias]);
				}
				// unable to load for some reason
				if(!is_object($Model->{$modelAlias}))
					continue;
				
				$Model->{$modelAlias}->id = false;
				if(isset($modelData[$Model->{$modelAlias}->primaryKey]))
				{
					if($modelData[$Model->{$modelAlias}->primaryKey])
						$Model->{$modelAlias}->id = $modelData[$Model->{$modelAlias}->primaryKey];
					else
						unset($modelData[$Model->{$modelAlias}->primaryKey]);
				}
				
				// it's an associated record that maybe needs to be created
				if(!$Model->{$modelAlias}->id)
				{
					$Model->{$modelAlias}->create();
				}
				
				$Model->{$modelAlias}->data = [$modelAlias => $modelData];
				
				if($Model->{$modelAlias}->save($Model->{$modelAlias}->data))
				{
					$modelData = $Model->{$modelAlias}->read(null, $Model->{$modelAlias}->id);
					$returnData[$modelAlias] = $modelData[$Model->{$modelAlias}->alias];
					$returnData['saveMethod'] = 'save';
					$returnData['message'] = __('This %s was updated successfully (ID: %s)', __($Model->{$modelAlias}->alias), $Model->{$modelAlias}->id);
				}
				else
				{
					$Model->modelError = __('(1) Unable to update the %s - Validation Errors: %s', __($Model->{$modelAlias}->alias), implode(', ', $Model->{$modelAlias}->validationErrors));
				}
			}
		}
		
		if($returnData)
			return $returnData;
		
		return false;
	}
	
	public function Common_gridEdit(Model $Model, $data = array())
	{
	// wrapper for the above method to explicitly call it in case the Model has a method of the same name
	// see the Contacts.ContactsAppModel.php for an example of how this is used
		return $this->gridEdit($Model, $data);
	}
	
	public function gridDelete(Model $Model, $data = array())
	{
		if(!$data)
		{
			return false;
		}
		
		if(!is_array($data))
		{
			return false;
		}
		
		if(isset($data['multiple']))
			unset($data['multiple']);
		
		$alias = $Model->alias;
		if(!isset($data[$alias]))
		{
			return false;
		}
		
		$primaryKey = $Model->primaryKey;
		if(!isset($data[$alias][$primaryKey]))
		{
			return false;
		}
		
		$Model->id = $data[$alias][$primaryKey];
		if($Model->delete($Model->id))
		{
			return true;
		}
		return false;
	}
	
	public function slugify(Model $Model, $string = false)
	{
		return strtolower(Inflector::slug(trim($string)));
	}
	
	public function getChanges(Model $Model, $old = array(), $new = array(), $validKeys = array())
	{
		// old should be the record from the database before a change
		// new should be the record after changes have been made, either the $this->data, or from the database
		$changes = array();
		foreach($old as $field => $value)
		{
			if(!in_array($field, $validKeys))
				continue;
			if(!isset($new[$field]))
				continue;
			if($value != $new[$field])
			{
				$changes[$field] = array('old' => $value, 'new' => $new[$field]);
			}
		}
		return $changes;
	}
	
	// an alias for below
	public function Common_multiselect(Model $Model, $data = false, $multiselect_value = false)
	{
		return $this->multiselect($Model, $data, $multiselect_value);
	}
	
	public function multiselect(Model $Model, $data = false, $multiselect_value = false)
	{
	/*
	 * Used to modify multiple items 
	 */
		// make sure all of the ids are there
		if(!isset($data['multiple'])) return false;
		
		// see if we can figure out where to send the user after the update
		$Model->multiselectReferer = array();
		if($data[$Model->alias]['multiselect_referer'])
		{
			$Model->multiselectReferer = unserialize($data[$Model->alias]['multiselect_referer']);
		}
			
		// get the ids of just the items we want to affect
		if(isset($data['multiple']))
		{
			$ids = $this->multiselectIds($Model, $data['multiple']);
		
			if(!$ids) return false;
			
			// make sure we have an option
			if(!isset($data[$Model->alias]['multiselect_option'])) return false;
			
			// make sure the option is valid
			if(!in_array($data[$Model->alias]['multiselect_option'], $Model->multiselectOptions)) return false;
			
			// find the option
			$multiselect_option = $data[$Model->alias]['multiselect_option'];
			
			// find what the value should be
			$multiselect_value = (isset($multiselect_value)?$multiselect_value:(isset($data[$Model->alias]['multiselect_value'])?$data[$Model->alias]['multiselect_value']:0));
			
			if(!$multiselect_value) $multiselect_value = 0;
			
			$success = false;
			switch ($multiselect_option)
			{
				case 'delete':
					$success = $Model->deleteAll(array(
						$Model->alias.'.id' => $ids,
					), true, true);
					// reset the page count
					$Model->multiselectReferer['page'] = 1;
					break;
				case 'inactive':
					$success = $Model->updateAll(
					array($Model->alias.'.active' => '0'),
					array($Model->alias.'.id' => $ids)
					);
					break;
				case 'active':
					$success = $Model->updateAll(
					array($Model->alias.'.active' => '1'),
					array($Model->alias.'.id' => $ids)
					);
					break;
				case 'bad':
					$success = $Model->updateAll(
						array($Model->alias.'.bad' => '1'),
						array($Model->alias.'.id' => $ids)
					);
					break;
				case 'notbad':
					$success = $Model->updateAll(
						array($Model->alias.'.bad' => '0'),
						array($Model->alias.'.id' => $ids)
					);
					break;
				case 'type':
					$success = $Model->updateAll(
						array($Model->alias.'.vector_type_id' => $multiselect_value),
						array($Model->alias.'.id' => $ids)
					);
				break;
				case 'multitype':
					$success = 0;
					foreach($multiselect_value as $this_data)
					{
						// turn falses/blanks into 0's
						foreach($this_data as $k => $v)
						{
							if(!$v) $this_data[$k] = 0;
						}
						if($this_data['id'])
						{
							$Model->id = $this_data['id'];
							$Model->data = $this_data;
							if($Model->save($Model->data)) $success++;
						}
					}
				break;
			}
			
			if(!$success)
			{
				// otherwise use the multiselect changes to apply
				$multiselect_value = Set::flatten($multiselect_value);
				$success = $Model->updateAll(
					$multiselect_value,
					array($Model->alias.'.'. $Model->primaryKey => $ids)
				);
			}
		}
			
		if($success)
		{
			// clear the cache
			$group = (isset($Model->alias)?$Model->alias:'appmodel');
			Cache::clearGroup('db_'. $group, (isset($Model->cacheQueryConfig)?$Model->cacheQueryConfig:'default'));
		}
		
		return $success;
	}
	
	public function multiselectReferer(Model $Model)
	{
		return $Model->multiselectReferer;
	}
	
	public function multiselectIds(Model $Model, $multiselectIds = array())
	{
		$ids = array();
		foreach($multiselectIds as $id => $selected)
		{
			if($selected) $ids[$id] = $id;
		}
		
		return $ids;
	}
	
	public function downloadParams(Model $Model, $id = false)
	{
		// not using upload management
		if($Model->manageUploads === false)
		{
			$Model->modelError = __('The model %s isn\'t configured correctly. (1)', __($Model->alias));
			return false;
		}
		
		if(!is_array($Model->manageUploads))
		{
			$Model->manageUploads = array();
		}
		
		$form_field = (isset($Model->manageUploads['form_field'])?$Model->manageUploads['form_field']:'file');
		$db_field = (isset($Model->manageUploads['db_field'])?$Model->manageUploads['db_field']:'filename');
		$file_object_name = (isset($Model->manageUploads['file_object_name'])?$Model->manageUploads['file_object_name']:$Model->alias);
		
		if(!trim($id))
		{
			$Model->modelError = __('Unknown Id.');
			return false;
		}
		
		$Model->id = $id;
		if(!$object = $Model->read(null, $id)) 
		{
			$Model->modelError = __('Unable to find this %s.', Inflector::humanize(Inflector::underscore($Model->alias)));
			return false;
		}
		
		$object = $object[$file_object_name];
		
		$paths = $this->paths($Model, $id);
		
		$params = array(
			'id' => $object[$db_field],
			'download' => true,
			'path' => $paths['sys'],
		);
		
		if(stripos($object[$db_field], '.') !== false)
		{
			$fileparts = explode('.', $object[$db_field]);
			$params['extension'] = array_pop($fileparts);
			$params['name'] = implode('.', $fileparts);
		}
		return $params;
	}
	
	public function paths(Model $Model, $id = false, $create = false, $filename = false)
	{
		$paths = array('web' => false, 'sys' => false);
		
		if(!$id) return false;
		
		$paths['web'] = DS. 'files'. DS. strtolower(Inflector::pluralize($Model->alias)). DS;
		$paths['sys'] = WWW_ROOT. ltrim($paths['web'], DS);
		
		foreach(str_split($id) as $num)
		{
			$paths['sys'] .= $num. DS;
			$paths['web'] .= $num. DS;
		}
		
		if($create)
		{
			umask(0);
			if(!is_dir($paths['sys'])) mkdir($paths['sys'], 0777, true);
		}
		
		if($filename)
		{
			$paths['sys'] .= $filename;
			$paths['web'] .= $filename;
		}
		
		return $paths;
	}
	
	public function attachFilePaths(Model $Model, $object = false)
	{
		// not using upload management
		if($Model->manageUploads === false)
		{
			$Model->modelError = __('The model %s isn\'t configured correctly. (1)', __($Model->alias));
			return $object;
		}
		
		if(!is_array($Model->manageUploads))
		{
			$Model->manageUploads = array();
		}
		
		$form_field = (isset($Model->manageUploads['form_field'])?$Model->manageUploads['form_field']:'file');
		$db_field = (isset($Model->manageUploads['db_field'])?$Model->manageUploads['db_field']:'filename');
		$file_object_name = (isset($Model->manageUploads['file_object_name'])?$Model->manageUploads['file_object_name']:$Model->alias);
		
		if(isset($object[$Model->alias][$Model->primaryKey]) and isset($object[$Model->alias][$db_field]))
		{
			$object[$Model->alias]['paths'] = $this->paths($Model, $object[$Model->alias][$Model->primaryKey], false, $object[$Model->alias][$db_field]);
		}
		
		return $object;
	}
	
	public function checkUploadedFile(Model $Model)
	{
		// not using upload management
		if($Model->manageUploads === false)
		{
			return true;
		}
		
		if(!is_array($Model->manageUploads))
		{
			$Model->manageUploads = array();
		}
		
		$form_field = (isset($Model->manageUploads['form_field'])?$Model->manageUploads['form_field']:'file');
		$db_field = (isset($Model->manageUploads['db_field'])?$Model->manageUploads['db_field']:'filename');
		$file_object_name = (isset($Model->manageUploads['file_object_name'])?$Model->manageUploads['file_object_name']:$Model->alias);
		
		// possibly just editing the objects details in the database
		if(!isset($Model->data[$Model->alias][$form_field]))
		{
			$Model->modelError = __('No %s uploaded (1)', $file_object_name);
			return true;
		}
		
		if(isset($Model->data[$Model->alias][$form_field]['error']) and $Model->data[$Model->alias][$form_field]['error'] == 4)
		{
			$Model->modelError = __('No %s uploaded (2)', $file_object_name);
			return true;
		}
		
		if(isset($Model->data[$Model->alias][$form_field]['error']) and $Model->data[$Model->alias][$form_field]['error'] !== 0)
		{
			$this->modelError = __('Error uploading %s', $file_object_name);
			return false;
		}
		$Model->data[$Model->alias][$db_field] = $Model->data[$Model->alias][$form_field]['name'];
		
		return true;
	}
	
	public function processUploadedFile(Model $Model)
	{
		// not using upload management
		if($Model->manageUploads === false)
		{
			return true;
		}
		
		if(!is_array($Model->manageUploads))
		{
			$Model->manageUploads = array();
		}
		
		$form_field = (isset($Model->manageUploads['form_field'])?$Model->manageUploads['form_field']:'file');
		$db_field = (isset($Model->manageUploads['db_field'])?$Model->manageUploads['db_field']:'filename');
		$file_object_name = (isset($Model->manageUploads['file_object_name'])?$Model->manageUploads['file_object_name']:$Model->alias);
		
		if(!$form_field)
		{
			$Model->modelError = __('The model %s isn\'t configured correctly,', __($Model->alias));
			return false;
		}
		
		if(!$db_field)
		{
			$Model->modelError = __('The model %s isn\'t configured correctly,', __($Model->alias));
			return false;
		}
		
		if(!isset($Model->data[$Model->alias][$form_field]))
		{
			$Model->modelError = __('No %s uploaded', $file_object_name);
			return false;
		}
		
		if(isset($Model->data[$Model->alias][$form_field]['error']) and $Model->data[$Model->alias][$form_field]['error'] !== 0)
		{
			$this->modelError = __('Error uploading %s', $file_object_name);
			return false;
		}
		
		// make sure the id is in the data array
		if(!isset($Model->data[$Model->alias][$Model->primaryKey]))
			$Model->data[$Model->alias][$Model->primaryKey] = $Model->id;
		
		$Model->data[$Model->alias]['paths'] = array();
		if($this->addFile($Model, $Model->data[$Model->alias][$Model->primaryKey], $Model->data[$Model->alias][$form_field]['name'], $Model->data[$Model->alias][$form_field]['tmp_name']))
		{
			$Model->data[$Model->alias]['paths'] = $this->paths($Model, $Model->data[$Model->alias][$Model->primaryKey], true, $Model->data[$Model->alias][$form_field]['name']);
		}
		return true;
	}
	
	public function checkDeletingFile(Model $Model)
	{
		// not using upload management
		if($Model->manageUploads === false)
		{
			return true;
		}
		
		if(!is_array($Model->manageUploads))
		{
			$Model->manageUploads = array();
		}
		
		$form_field = (isset($Model->manageUploads['form_field'])?$Model->manageUploads['form_field']:'file');
		$db_field = (isset($Model->manageUploads['db_field'])?$Model->manageUploads['db_field']:'filename');
		$file_object_name = (isset($Model->manageUploads['file_object_name'])?$Model->manageUploads['file_object_name']:$Model->alias);
		
		// find the info for deleting the file
		if($filename = $Model->field($db_field))
		{
			$paths = $this->paths($Model, $Model->id, false, $filename);
			$this->delete_file_path = $paths['sys'];
		}
		return true;
	}
	
	public function processDeletingFile(Model $Model)
	{
		// not using upload management
		if($Model->manageUploads === false)
		{
			return true;
		}
		
		if(!is_array($Model->manageUploads))
		{
			$Model->manageUploads = array();
		}
		
		$form_field = (isset($Model->manageUploads['form_field'])?$Model->manageUploads['form_field']:'file');
		$db_field = (isset($Model->manageUploads['db_field'])?$Model->manageUploads['db_field']:'filename');
		$file_object_name = (isset($Model->manageUploads['file_object_name'])?$Model->manageUploads['file_object_name']:$Model->alias);
		
		return $this->removeFile($Model, $this->delete_file_path);
	}
	
	public function addFile(Model $Model, $id = false, $filename = false, $tmpname = false)
	{
		$paths = $this->paths($Model, $id, true, $filename);
		if($paths['sys'])
		{
			umask(0);
			return rename($tmpname, $paths['sys']);
		}
		return false;
	}
	
	public function removeFile(Model $Model, $path = false)
	{
		// delete the file
		if(!$path)
		{
			$Model->modelError = __('Unknown Path for %s', __($Model->alias));
			return false;
		}
		
		// try to delete the file
		if(is_file($path) and is_writable($path))
		{
			if(!unlink($path))
			{
				$Model->modelError = __('Unable to delete the file at: %s', $path);
				return false;
			}
		}
			
		// should be the only file in this directory
		$path_parts = explode(DS, $path);
		
		// remove the filename from the aray
		array_pop($path_parts);
		while($path_parts)
		{
			$this_dir = array_pop($path_parts);
			if($this_dir == strtolower(Inflector::pluralize($Model->alias))) break;
			if(in_array($this_dir, range(0, 9)))
			{
				$dir = implode(DS, $path_parts). DS. $this_dir;
				$listing = glob ("$dir/*");
				
				// directory is empty
				if(empty($listing) and is_dir($dir)) rmdir($dir);
			}
		}
		return true;
	}
	
	public function processZipFile(Model $Model, $zip_path = false, $delete = false)
	{
	/*
	 * Takes a path to a zip file, unzips it, and returns a list of files with their full paths
	 */
	
		if(!$zip_path) return false;
		$Model->zipDir = $zip_contents_path = $zip_path. '_dir';
		
		// unzip the zip file
		$ret = false;
		@system('/usr/bin/unzip -o -j -d '. $zip_contents_path. ' '.$zip_path. ' > /dev/null', $ret);
		
		if($ret)
		{
			$Model->validationErrors['zipfile'] = __('Error extracting zip file.');
			return false;
		}
		
		// Load the file and folder classes to read the directories
		App::uses('Folder', 'Utility');
		App::uses('File', 'Utility');		
		
		$dir = new Folder($zip_contents_path);
		if(!$files = $dir->read(true, true)) return false;
		
		$out = array();
		
		foreach($files[1] as $file)
		{
			$out[] = $zip_contents_path. DS. $file;
		}
		
		// delete the zip file
		if($delete)
		{
			unlink($zip_path);
		}
		
		return $out;
	}
	
	public function removeZipDir(Model $Model, $path = false)
	{
		if(!$path)
		{
			$path = $Model->zipDir;
		}
		if(!$path) return false;
		@system('/bin/rm -rf '. $path);
		
		return true;
	}
	
	public function mergeSettings(Model $Model, $defaults = array(), $new = array())
	{
		$defaults = Hash::flatten($defaults);
		$new = Hash::flatten($new);
		$combined = array_merge($defaults, $new);
		return Hash::expand($combined);
	}
	
	public function compareStrings(Model $Model, $string1 = false, $string2 = false, $asPercent = false)
	{
		if(!$string1) return false;
		if(!$string2) return false;
		
		$string1 = trim($string1);
		$string2 = trim($string2);
		
		if($string1 === $string2)
		{
			if($asPercent)
			{
				return 100;
			}
			return strlen($string1);
		}
		
		if($asPercent)
		{
			$percent = 0;
			similar_text($string1, $string2, $percent);
			$percent = ceil($percent);
			return $percent;
		}
		
		return similar_text($string1, $string2);
	}
	
	public function findClosestMatch($Model, $string = false, $options = array(), $compare_option_index = false)
	{
		if(!$string) return false;
		if(!$options) return false;
		
		$string = trim($string);
		if(!$string) return false;
		
		if(!isset($this->closest_match_cache)) $this->closest_match_cache = array();
		
		if(isset($this->closest_match_cache[$string])) return $this->closest_match_cache[$string];
		
		if($compare_option_index)
		{
			if(isset($options[$string])) 
			{
				$this->closest_match_cache[$string] = $options[$string];
				return $options[$string];
			}
		}
		
		$results = array();
		foreach($options as $i => $option)
		{
			if(is_array($option)) continue;
			
			if($compare_option_index)
			{
				$result = $this->compareStrings($Model, $string, $i, true);
				$results[$i] = $result;
			}
			else
			{
				if($string == $option) return $option;
				$result = $this->compareStrings($Model, $string, $option, true);
				$results[$option] = $result;
			}
		}
		
		
		arsort($results);
		reset($results);
		$out = key($results);
		
		if($compare_option_index)
		{
			$this->closest_match_cache[$string] = $options[$out];
			if(isset($options[$out])) return $options[$out];
		}
		
		$this->closest_match_cache[$string] = $out;
		return $out;
	}
	
	public function cookieSave(Model $Model, $key = false, $value = false)
	{
		$this->cookieLoad($Model);
		$key = $this->cookieKey($key);
	}
	
	public function cookieRead(Model $Model, $key = false)
	{
		$this->cookieLoad($Model);
		$key = $this->cookieKey($key);
	}
	
	public function cookieKey(Model $Model, $key = false)
	{
		$this->cookieLoad($Model);
		return $Model->alias.'.'. ($key?$key:'default');
	}
	
	public function cookieLoad(Model $Model)
	{
		if(!$this->Cookie)
		{
			$this->Cookie = new CookieComponent();
		}
		return true;
	}
	
	public function apiKey($user_id = false, $genKey = false)
	{
	}
	
	public function genKey($user_id = false)
	{
	}
	
	/////// support functions to update a record's ipaddress, or hostname
	public function Common_nslookup(Model $Model, $id = false, $field = false)
	{
		$results = false;
		$Model->modelError = false;
		//NS_localLookup
		if(!$id)
		{
			$Model->modelError = __('Unknown ID');
			return false;
		}
		if(!$Model->hostLookupFields['ipaddress'])
		{
			$Model->modelError = __('Unknown Field for IP Address');
			return false;
		}
		if(!$Model->hostLookupFields['hostname'])
		{
			$Model->modelError = __('Unknown Field for Hostname');
			return false;
		}
		
		if(!$Model->Behaviors->loaded('Nslookup'))
		{
			$Model->Behaviors->load('Utilities.Nslookup');
		}
		
		$object = $Model->read(null, $id);
		
		$hostname = trim($object[$Model->alias][$Model->hostLookupFields['hostname']]);
		$ipaddress = trim($object[$Model->alias][$Model->hostLookupFields['ipaddress']]);
		
		// make sure we have something to lookup
		$lookup = false;
		$type = false;
		if($field == $Model->hostLookupFields['hostname'])
		{
			if(!$ipaddress)
			{
				$Model->modelError = __('No IP Address to look up.');
				return false;
			}
			$lookup = $ipaddress;
			$type = 'ipaddress';
		}
		elseif($field == $Model->hostLookupFields['ipaddress'])
		{
			if(!$hostname)
			{
				$Model->modelError = __('No Hostname to look up.');
				return false;
			}
			$lookup = $hostname;
			$type = 'hostname';
		}
		elseif(!$hostname and !$ipaddress)
		{
			$Model->modelError = __('No Hostname or IP Address to look up.');
			return false;
		}
		elseif($ipaddress)
		{
			$lookup = $ipaddress;
			$type = 'ipaddress';
		}
		else
		{
			$lookup = $hostname;
			$type = 'hostname';
		}
		
		$results = $Model->NS_localLookup($lookup, $type);
		return $results;
	}
	
	
	////////////// config management
	public function configRead(Model $Model)
	{
	/*
	 * Reads and return the config information already set.
	 */
		$config_path = $this->configPath($Model);
		
		// incase it wasn't included
		include_once($config_path);
		
		return Configure::read('AppConfig');
	}
	
	public function configKeys(Model $Model)
	{
	/*
	 * Gets the keys and form info from the Config/core.php file
	 * Manages what gets placed in the config
	 */
		// located in Config/core.php 
		return Configure::read('AppConfigKeys');
	}
	
	public function configSave(Model $Model, $data = false)
	{
	/*
	 * Saves the config information to the app specific config file
	 */
		if(!$this->configCheck($Model)) return false;
		// the string to be written to the config file
		$string = array();
		$string[] = '<?php';
		$string[] = '// dynamically written. don\'t change anything if you don\'t know what your\'re doing.';
		$string[] = '';
		$config_template = 'Configure::write(\'AppConfig.%s\', %s);';
		
		if(!is_array($data)) $data = array($data);
		
		$data = Set::flatten($data);
		
		if(isset($data['Site.base_url']))
		{
			$parts = $data['Site.base_url'];
			$parts = parse_url($parts);
			
			$data['App.base'] = $parts['path'];
			unset($parts['path']);
			
			$fullBaseUrl = $parts['scheme']. '://'. $parts['host']. (isset($parts['port'])?':'.$parts['port']:'');
			$data['App.fullBaseUrl'] = $fullBaseUrl;
		}
		
		foreach($data as $key => $v)
		{
			$v = "'". $v. "'";
			$string[] = __($config_template, $key, $v);
		}
//		$string[] = ' ? >';
		return file_put_contents($this->configPath, implode("\n", $string));
	}
	
	public function configPath(Model $Model)
	{
		$app_config_path = APP. 'Config'. DS. 'app_config.php';
		$this->configPath = $app_config_path;
		
		return $app_config_path;
	}
	
	public function configCheck(Model $Model, $check_writable = false)
	{
		$app_config_path = $this->configPath($Model);
		
		if(!file_exists($app_config_path))
		{
			// try to make a blank
			if(!touch($app_config_path))
			{
				$this->configError = __('App Config File doesn\'t exist');
				return false;
			}
		}
		
		if(!is_readable($app_config_path))
		{
			// try to make a blank
			if(!touch($app_config_path))
			{
				$this->configError = __('App Config File isn\'t readable');
				return false;
			}
		}
		
		if($check_writable)
		{
			if(!is_writable($app_config_path))
			{
				$this->configError = __('App Config File isn\'t writable');
				return false;
			}
		}
		return true;
	}
	
	public function makeRGBfromHex(Model $Model, $color = false, $opacity = '0.4')
	{
		if(!$color)
		{
			return 'rgb(255, 255, 255)';
		}
		
		$color = str_replace('#', '', $color);
		
		$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
		$rgb =  array_map('hexdec', $hex);
		
		if($opacity)
		{
			if(abs($opacity) > 1)
        		$opacity = 1.0;
			
			$output = 'rgba('.implode(",",$rgb).','.$opacity.')';
		}
		else
		{
			$output = 'rgb('.implode(",",$rgb).')';
		}
		
		return $output;
	}
	
	public function makeColorCode(Model $Model, $string = false)
	{
		
		$string = md5($string);
		$color = strtolower(substr($string, 0, 6));
		
		return '#'. $color;
	}
	
	public function makeBorderColor(Model $Model, $color = false, $opacity = '1')
	{
		if(!$color)
		{
			return 'rgb(255, 255, 255)';
		}
		
		$color = str_replace('#', '', $color);
		
		$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
		$rgb =  array_map('hexdec', $hex);
		
		foreach($rgb as $i => $colorCode)
		{
			if($colorCode >= 50)
				$rgb[$i] = ($colorCode - 50);
		}
		
		if($opacity)
		{
			if(abs($opacity) > 1)
        		$opacity = 1.0;
			
			$output = 'rgba('.implode(",",$rgb).','.$opacity.')';
		}
		else
		{
			$output = 'rgb('.implode(",",$rgb).')';
		}
		return $output;
	}
	
/////////////////////
	public function Common_objectToArray(Model $Model, $obj) 
	{
		$arrObj = is_object($obj) ? get_object_vars($obj) : $obj;
		$arr = '';
		if($arrObj)
		{
			foreach ($arrObj as $key => $val) 
			{
				$val = (is_array($val) || is_object($val)) ? $this->Common_objectToArray($Model, $val) : $val;
				$arr[$key] = $val;
			}
		}
		return $arr;
	}
}