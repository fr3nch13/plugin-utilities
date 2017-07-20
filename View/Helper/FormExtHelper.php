<?php

// plugins/utilities/View/Helper/FormExtHelper.php
App::uses('FormHelper', 'View/Helper');
App::uses('UtilitiesAppHelper', 'Utilities.View/Helper');

/*
 * Used as a wrapper for the form helper
 * Used to extend the form helper and add more functionality
 * see http://www.wufoo.com/html5/ for html5 form elements
 */
class FormExtHelper extends FormHelper 
{
	public $helpers = array(
		'Html', 'Ajax', 'Time', 'Js' => array('JqueryUi'),
		'Common' => array('className' => 'Utilities.Common'),
	);
	
	// override how errors work
	public $formErrors = false;
	
	public function input($field, $options = array())
	{
		$this->setEntity($field);
		$options = $this->_parseOptions($options);
		$options['data-field'] = implode('.', $this->entity());
		$options = $this->_initInputField($field, $options);
		
		$options = $this->fixOptions($options);
		
		if(isset($options['type']))
		{
			$options['class']['input-'. $options['type']] = 'input-'. $options['type'];
		}
		
		if(isset($options['description']))
		{
			if(isset($options['id']))
			{
				if(!isset($options['after']))
					$options['after'] = '';
				
				$options['aria-describedby'] = $options['id']. '-tip';
				
				$options['after'] .= $this->Html->tag('div', $options['description'], array(
					'id' => $options['aria-describedby'],
					'role' => 'form-tooltip',
				));
			}
			unset($options['description']);
		}
		
		if(!isset($options['autocomplete']) and isset($options['type']) and in_array($options['type'], array('color', 'date', 'datetime', 'datetime-local', 'email', 'hidden', 'month', 'number', 'password', 'range', 'search', 'tel', 'text', 'time', 'url', 'week')))
		{
			$options['autocomplete'] = 'off';
		}
		
		if(isset($options['type']) and in_array($options['type'], array('api_key')))
		{
			return $this->apiKey($field, $options);
		}
				
		// find all of the datetime options
		if(isset($options['type']) and in_array($options['type'], array('clear', 'clearb')))
		{
			if(isset($options['div']['class']) and is_array($options['div']['class']))
				$options['div']['class'] = implode(' ', $options['div']['class']);
				
			return $this->Html->divClear($options);
		}
		
		if(isset($options['type']) and in_array($options['type'], array('legend')))
		{
			$text = false;
			if(isset($options['text']))
			{
				$text = $options['text'];
				unset($options['text']);
			}
			elseif(isset($options['value']))
			{
				$text = $options['value'];
				unset($options['value']);
			}
			if(isset($options['div']))
			{
				unset($options['div']);
			}
			if(isset($options['div']['class']) and is_array($options['div']['class']))
				$options['div']['class'] = implode(' ', $options['div']['class']);
				
			return $this->Html->tag('h3', $text, $options);
		}
		
		if(isset($options['type']) and in_array($options['type'], array('paragraph', 'para', 'p')))
		{
			$text = false;
			if(isset($options['text']))
			{
				$text = $options['text'];
				unset($options['text']);
			}
			elseif(isset($options['value']))
			{
				$text = $options['value'];
				unset($options['value']);
			}
			if(isset($options['div']))
			{
				unset($options['div']);
			}
			
			return $this->Html->tag('p', $text, $options);
		}
		
		if(isset($options['type']) and in_array($options['type'], array('raw')))
		{
			$text = false;
			if(isset($options['text']))
			{
				$text = $options['text'];
				unset($options['text']);
			}
			elseif(isset($options['value']))
			{
				$text = $options['value'];
				unset($options['value']);
			}
			
			return $text;
		}
		
		if(isset($options['type']) and in_array($options['type'], array('datetime', 'date', 'time')))
		{
			return $this->datePicker($field, $options);
		}
		
		if(isset($options['type']) and in_array($options['type'], array('daterange', 'timerange', 'datetimerange')))
		{
			return $this->dateRangePicker($options);
		}
		
		// searchable dropdown
		if(isset($options['type']) and in_array($options['type'], array('select')) and isset($options['searchable']) and $options['searchable'])
		{
			return $this->searchableDropdown($field, $options);
		}
		
		// find all of the color picker options
		if(isset($options['type']) and in_array($options['type'], array('color')))
		{
			return $this->colorPicker($field, $options);
		}
		
		// find all of the file inputs and modify them
		if(isset($options['type']) and in_array($options['type'], array('file')))
		{
			return $this->fileInput($field, $options);
		}
		
		// find all of the price inputs and modify them
		if(isset($options['type']) and in_array($options['type'], array('price')))
		{
			return $this->priceInput($field, $options);
		}
		
		// find all of the boolean inputs and modify them
		if(isset($options['type']) and in_array($options['type'], array('boolean')))
		{
			return $this->booleanInput($field, $options);
		}
		
		// find all of the boolean inputs and modify them
		if(isset($options['type']) and in_array($options['type'], array('toggle', 'switch')))
		{
			return $this->toggleInput($field, $options);
		}
		
		// find all of the number inputs and modify them
		if(isset($options['type']) and in_array($options['type'], array('number', 'integer')))
		{
			return $this->numberInput($field, $options);
		}
		
		// find all of the number inputs and modify them
		if(isset($options['type']) and in_array($options['type'], array('autocomplete')))
		{
			return $this->autocompleteInput($field, $options);
		}
		
		$input = $this->getInput($field, $options);

		return $input;
	}
	
	public function label($fieldName = null, $text = null, $options = array())
	{
		$this->setEntity($fieldName);
		
		$entity = $this->entity();
		$model = $this->model();
		$validates = $this->_introspectModel($model, 'validates');
		$field = array_pop($entity);
		
		if ($text === null) 
		{
			if (strpos($fieldName, '.') !== false)
			{
				$fieldElements = explode('.', $fieldName);
				$text = array_pop($fieldElements);
			} 
			else 
			{
				$text = $fieldName;
			}
			if (substr($text, -3) === '_id') 
			{
				$text = substr($text, 0, -3);
			}
			$text = __(Inflector::humanize(Inflector::underscore($text)));
		}
		
		if(isset($validates[$field]))
		{
			if(!isset($options['class'])) $options['class'] = '';
			$options['class'] .= ' required';
			
			$text .= ' '. $this->Html->tag('span', __('(required)'), array('class' => 'required'));
		}
		
		return parent::label($fieldName, $text, $options);
	}
	
	public function divClear()
	{
	 	return $this->div('clearb', ' ');
	}
	
	public function tagIsInvalid() 
	{
	
		// allow the default function
		if($errors = parent::tagIsInvalid())
		{
			return $errors;
		}
		
		// 0.Model.field. finding errors for multiple records
		// this is where we are overwriting
		// get the errors array
		if(!$this->formErrors)
		{
			$this->formErrors = array();
			if(isset($this->validationErrors[$this->defaultModel]))
			{
				$this->formErrors = $this->validationErrors[$this->defaultModel];
			}
		}
		
		$entity = $this->entity();
		$model = array_shift($entity);
		
		if (empty($model) || is_numeric($model)) 
		{
			array_splice($entity, 1, 0, $model);
			$model = array_shift($entity);
			if($model != $this->defaultModel)
			{
				array_splice($entity, 1, 0, $model);
			}
		}
		
		$errors = Hash::get($this->formErrors, implode('.', $entity));
		
		return $errors === null ? false : $errors;
	}
	
	public function dateRangePicker($options = array())
	{
		if(!isset($options['start']) or !isset($options['end']))
		{
			return false;
		}
		
		$start = $options['start'];
		$end = $options['end'];
		
		unset($options['start'], $options['end']);
		
		$type = $options['type'];
		if($options['type'] == 'daterange')
		{
			$options['type'] = 'date';
		}
		elseif($options['type'] == 'timerange')
		{
			$options['type'] = 'time';
		}
		elseif($options['type'] == 'datetimerange')
		{
			$options['type'] = 'datetime';
		}
		
		$start_options = array();
		if(isset($options['start_options']))
		{
			$start_options = $options['start_options'];
			unset($options['start_options']);
		}
		
		$end_options = array();
		if(isset($options['end_options']))
		{
			$end_options = $options['end_options'];
			unset($options['end_options']);
		}
		
		// clear some of the options
		if(isset($options['data-field']))
			unset($options['data-field']);
		if(isset($options['name']))
			unset($options['name']);
		if(isset($options['id']))
			unset($options['id']);
		if(isset($options['value']))
			unset($options['value']);
		
		$start_options = array_merge($options, $start_options);
		$end_options = array_merge($options, $end_options);
		
		if(!isset($start_options['value']))
		{
			$start_options['value'] = date('Y-m-01');
		}
		
		if(!isset($end_options['value']))
		{
			$end_options['value'] = date('Y-m-t', strtotime($start_options['value']));
		}
		else
		{
			$end_options['value'] = date('Y-m-t', strtotime($end_options['value']));
		}
		
		if(isset($start_options['class']) and is_array($start_options['class']))
			$start_options['class'] = implode(' ', $start_options['class']);
			
		if(isset($end_options['class']) and is_array($end_options['class']))
			$end_options['class'] = implode(' ', $end_options['class']);
		
		$start_html = $this->input($start, $start_options);
		$end_html = $this->input($end, $end_options);
		
		
		return $start_html. $end_html;
	}
	
	public function datePicker($field, $options = array())
	{
		$get_cal_options = false;
		if(isset($options['get_cal_options']))
		{
			$get_cal_options = true;
			unset($options['get_cal_options']);
		}
		
		$cal_options = array();
		if(isset($options['cal_options']))
		{
			$cal_options = $options['cal_options'];
			unset($options['cal_options']);
		}
		
		if(isset($options['name']))
			unset($options['name']);
		
		$this->setEntity($field);
		
		$options = $this->_parseOptions($options);
		$options['data-field'] = implode('.', $this->entity());
		$options = $this->_initInputField($field, $options);
		
		if(!is_array($options['class']))
			$options['class'] = explode(' ', $options['class']);
		$options['class']['input-date-picker'] = 'input-date-picker';
		
		$options = $this->_parseOptions($options);
		
		$rendered = array();
		
		// build the hidden field with the date formatted
		$hidden_options = $this->_initInputField($field, $options);
		$hidden_options['type'] = 'hidden';
		$fieldId_hidden = $hidden_options['id'];
		$this->_name($hidden_options, $field);
		$entity = $this->entity();
		$model = $this->model();
		
		// build the false text field with the nice date, this is what gets attached 
		$text_options = $this->_initInputField($field. '_false', $options);
		$text_options['type'] = 'text';
		$fieldId_text = $text_options['id'];
		
		if($fieldId_text == $fieldId_hidden)
		{
			$fieldId_text .='_false';
			$text_options['id'] .='_false';
		}
		
		// get the current settings from the request data
		$current_setting = false;
		if(isset($options['value']))
		{
			if($options['value'])
				$current_setting = date('Y-m-d H:i:s', strtotime($options['value']));
		}
		else
		{
			if(isset($this->request->data))
			{
				$formatted_field_name = implode('.', $entity);
				$data = Set::flatten($this->request->data);
				
				if(isset($data[$formatted_field_name]) and $data[$formatted_field_name])
				{
					$current_setting = $data[$formatted_field_name];
				}
			}
		}
		
		if(in_array($current_setting, array('0000-00-00 00:00:00', '0000:00:00')))
			$current_setting = false;
		
		$options['value'] = $current_setting;
		
		$value_time = false;
		$hidden_options['value'] = false;
		$text_options['value'] = false;
		if($options['value'])
		{
			$value_time = strtotime($options['value']);
		
			$hidden_options['value'] = ($value_time?date('Y-m-d H:i:s', $value_time):false);
			$text_options['value'] = ($value_time?date('F j, Y \a\t H:i', $value_time):false);
		}
		
		$hidden_option['data-input-type'] = $options['type'];
		$text_options['data-input-type'] = $options['type'];
		
		if($options['type'] == 'time') 
		{
			$cal_options['timeOnly'] = true;
			$cal_options['altFieldTimeOnly'] = true;
			$cal_options['dateFormat'] = 'M d, yy';
			$cal_options['timeFormat'] = 'HH:mm';
			$cal_options['altFormat'] = 'yy-mm-dd';
			$cal_options['altTimeFormat'] = 'HH:mm:ss';
			
			if($value_time)
			{
				$hidden_options['value'] = ($value_time?date('H:i:s', $value_time):false);
				$text_options['value'] = ($value_time?date('H:i', $value_time):false);
			}
		}
		elseif($options['type'] == 'date') 
		{
			$cal_options['showTimepicker'] = false;
			$cal_options['timeFormat'] = '';
			$cal_options['dateFormat'] = 'M d, yy';
			$cal_options['timeFormat'] = '';
			$cal_options['altFormat'] = 'yy-mm-dd';
			$cal_options['altTimeFormat'] = '';
			
			if($value_time)
			{
				$hidden_options['value'] = ($value_time?date('Y-m-d', $value_time):false);
				$text_options['value'] = ($value_time?date('M j, Y', $value_time):false);
			}
		}
		elseif($options['type'] == 'datetime') 
		{
			$cal_options['dateFormat'] = 'M d, yy';
			if($value_time)
			{
				$hidden_options['value'] = ($value_time?date('Y-m-d H:i:s', $value_time):false);
				$text_options['value'] = ($value_time?date('M j, Y, H:i', $value_time):false);
			}
		}
		
		if(isset($cal_options['minDate']))
		{
			$cal_options['minDate'] = date('M d, y', strtotime($cal_options['minDate']));
			$cal_options['wrapCallbacks'] = false;
		}
		
		if(isset($cal_options['maxDate']))
		{
			$cal_options['maxDate'] = date('M d, y', strtotime($cal_options['maxDate']));
			$cal_options['wrapCallbacks'] = false;
		}
		
		// calendar options 
		$cal_options = array_merge(array(
			'dateFormat' => 'M d, yy',
			'timeFormat' => 'HH:mm',
			'separator' => ', ',
			'textField' => '#'. $fieldId_text,
			'altField' => '#'. $fieldId_hidden,
			'altFieldTimeOnly' => false,
			'altFormat' => 'yy-mm-dd',
			'altTimeFormat' => 'HH:mm:ss',
			'altSeparator' => ' ',
			'stepMinute' => 10,
			'showSecond' => false,
			'showButtonPanel' => true,
		), $cal_options);
		
		if($get_cal_options)
		{
			return $cal_options;
		}
		
		$text_options['class']['clearable'] = 'clearable';
		$hidden_options['class']['clearable-hidden'] = 'clearable-hidden';
		
		$hidden_options['data-datepicker-options'] = json_encode($cal_options);
		
		if(isset($hidden_options['class']) and is_array($hidden_options['class']))
			$hidden_options['class'] = implode(' ', $hidden_options['class']);
		if(isset($hidden_options['div']['class']) and is_array($hidden_options['div']['class']))
			$hidden_options['div']['class'] = implode(' ', $hidden_options['div']['class']);
		$hidden = parent::input($field, $hidden_options);
		
		$this->_name($text_options, $field. '_false');
		$entity = $this->entity();
		$model = $this->model();
		
		if(!isset($text_options['after']))
			$text_options['after'] = '';
		$text_options['after'] .= $hidden;
		if(isset($text_options['class']) and is_array($text_options['class']))
			$text_options['class'] = implode(' ', $text_options['class']);
		if(isset($text_options['div']['class']) and is_array($text_options['div']['class']))
			$text_options['div']['class'] = implode(' ', $text_options['div']['class']);
		$rendered[] = parent::input($field, $text_options);
		
		return implode("\n", $rendered);
	}
	
	public function colorPicker($field, $options = array())
	{
		$this->setEntity($field);
		$options['type'] = 'text';
		$options = $this->_parseOptions($options);
		$options = $this->_initInputField($field, $options);
		$fieldId = $options['id'];
		$this->_name($options, $field);
		$entity = $this->entity();
		$model = $this->model();
		
		$rendered = array();
		$options['class']['input-color-picker'] = 'input-color-picker';
		
		$rendered[] = $this->getInput($field, $options);
		
		// calendar options 
		$color_options = array(
			'preferredFormat' => 'hex',
			'showInput' => true,
			'clickoutFiresChange' => true,
			'showInitial' => true,
			'showPalette' => true,
			'showSelectionPalette' => true,
		);
		
		// attach the calendar to the false field
		$this->Js->get('#'. $fieldId)->spectrum($color_options);
		
		return implode("\n", $rendered);
	}
	
	public function searchableDropdown($field, $options = array())
	{
		$this->setEntity($field);
		$options['type'] = 'select';
		$options = $this->_parseOptions($options);
		$options = $this->_initInputField($field, $options);
		$fieldId = $options['id'];
		$this->_name($options, $field);
		$entity = $this->entity();
		$model = $this->model();
		
		$options['class']['searchable'] = 'searchable';
		$options['class']['input-searchable'] = 'input-searchable';
		
		$rendered = array();
		
		$rendered[] = $this->getInput($field, $options);
		return implode("\n", $rendered);
	}
	
	public function booleanInput($field, $options = array())
	{
		$this->setEntity($field);
		$options['type'] = 'select';
		
		if(!isset($options['options']))
			$options['options'] = $this->optionsBoolean();
		
		$options = $this->_parseOptions($options);
		$options = $this->_initInputField($field, $options);
		$fieldId = $options['id'];
		$this->_name($options, $field);
		$entity = $this->entity();
		$model = $this->model();
		
		$options['class']['input-boolean'] = 'input-boolean';
		
		return $this->getInput($field, $options);
	}
	
	public function optionsBoolean($extraOptions = array())
	{
	// works with WrapHelper::yesNoUnknown()
		$baseOptions = array(
			0 => __('Unknown'),
			1 => __('No'),
			2 => __('Yes'),
		);
		$options = array_merge($baseOptions, $extraOptions);
		return $options;
	}
	
	public function fileInput($field, $options = array())
	{
		$this->setEntity($field);
		$options['type'] = 'file';
		$options = $this->_parseOptions($options);
		$options = $this->_initInputField($field, $options);
		$fieldId = $options['id'];
		$this->_name($options, $field);
		$entity = $this->entity();
		$model = $this->model();
		
		$options['class']['input-file'] = 'input-file';
		
		if(!isset($options['aria-describedby']))
			$options['aria-describedby'] = false;
		
		$options['after'] = $this->Html->tag('div', __('(Max file size is %s).', $this->Common->maxFileSize()), array(
					'id' => $options['aria-describedby'],
					'role' => 'form-tooltip',
				));
		
		return $this->getInput($field, $options);
	}
	
	public function priceInput($field, $options = array())
	{
		$this->setEntity($field);
		$options['type'] = 'number';
		$options['step'] = '0.01';
		$options['min'] = '0';
		
		$options = $this->_parseOptions($options);
		$options = $this->_initInputField($field, $options);
		$fieldId = $options['id'];
		$this->_name($options, $field);
		$entity = $this->entity();
		$model = $this->model();
		
		$options['class']['numeric'] = 'numeric';
		$options['class']['price'] = 'price';
		$options['class']['input-numeric'] = 'input-numeric';
		$options['class']['input-price'] = 'input-price';
		
		return $this->getInput($field, $options);
	}
	
	public function numberInput($field, $options = array())
	{
		$this->setEntity($field);
		$options['type'] = 'number';
		$options['step'] = '1';
		$options['min'] = '0';
		
		$options = $this->_parseOptions($options);
		$options = $this->_initInputField($field, $options);
		$fieldId = $options['id'];
		$this->_name($options, $field);
		$entity = $this->entity();
		$model = $this->model();
		
		$options['class']['numeric'] = 'numeric';
		$options['class']['number'] = 'number';
		$options['class']['input-numeric'] = 'input-numeric';
		$options['class']['input-number'] = 'input-number';
		
		if(isset($options['value']) and !$options['value'])
			$options['value'] = 0;
		
		return $this->getInput($field, $options);
	}
	
	public function autocompleteInput($field, $options = array())
	{
		$this->setEntity($field);
		$options['type'] = 'text';
		
		$options = $this->_parseOptions($options);
		$options = $this->_initInputField($field, $options);
		$fieldId = $options['id'];
		$this->_name($options, $field);
		$entity = $this->entity();
		$model = $this->model();
		
		$options['class']['input-autocomplete'] = 'input-autocomplete';
		
		if(!isset($options['rel']))
			$options['rel'] = $this->Html->url($this->Html->urlModify(array(
				'action' => 'autocomplete',
				'ext' => 'json',
			)));
		elseif(is_array($options['rel']) and !isset($options['rel']['ext']))
			$options['rel']['ext'] = 'json';
		
		if(is_array($options['rel']))
			$options['rel'] = $this->Html->url($options['rel']);
		
		return $this->getInput($field, $options);
	}
	
	public function toggleInput($field, $options = array())
	{
		$this->setEntity($field);
		$options = $this->fixOptions($options);
		
		$options['type'] = 'checkbox';
		
		$options['class']['input-toggle'] =  'input-toggle';
		$options['div']['class']['input-toggle'] = 'input-toggle';
		
		if(!isset($options['value']))
			$options['value'] = 1;
		
		$fa_class = 'fa-toggle-off';
		if(isset($options['checked']) and $options['checked'])
			$fa_class = 'fa-toggle-on';
		
		$icon = $this->Html->tag('i', '', array('aria-hidden' => true, 'class' => 'input-toggle fa fa-lg '. $fa_class));
		
		if(!isset($options['between']))
			$options['between'] = '';
		$options['between'] .= $icon;
		
		return $this->getInput($field, $options);
	}
	
	public function apikey($field, $options = [])
	{
		$divOptions = array(
			'class' => 'input-api_key'
		);
		if(isset($options['div']))
			$divOptions = array_merge($divOptions, $options['div']);
		
		$options['div'] = false;
		$options['label'] = false;
		
		$options['type'] = 'text';
		$textField = $this->input($field, $options);
		
		$options['type'] = 'submit';
		$buttonField = $this->submit(__('Regenerate Key'), $options);
		
		return $this->Html->tag('div', $textField.$buttonField, $divOptions);
	}
	
	public function fixOptions($options = [])
	{
		if(!is_array($options))
			return $options;
		
		// fix the class options
		if(!isset($options['class']))
			$options['class'] = [];
		elseif(!is_array($options['class']))
		{
			$classes = preg_split('/\s+/', $options['class']);
			$options['class'] = [];
			foreach($classes as $class)
				$options['class'][$class] = $class;
		}
		
		// fix the div options
		if(!array_key_exists('div', $options))
			$options['div'] = [];
		
		if(is_array($options['div']))
		{
			if(!array_key_exists('class', $options['div']))
				$options['div']['class'] = [];
			elseif(!is_array($options['div']['class']))
			{
				$classes = preg_split('/\s+/', $options['div']['class']);
				$options['div']['class'] = [];
				foreach($classes as $class)
					$options['div']['class'][$class] = $class;
			}
		}
		
		return $options;
	}
	
	public function getInput($field, $options = [])
	{
		$this->setEntity($field);
		$options = $this->fixOptions($options);
		
		if(isset($options['class']) and is_array($options['class']))
			$options['class'] = implode(' ', $options['class']);
		
		if(isset($options['div']['class']) and is_array($options['div']['class']))
			$options['div']['class'] = implode(' ', $options['div']['class']);
		
		return parent::input($field, $options);
	}
}