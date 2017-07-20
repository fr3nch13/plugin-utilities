<?php
/** 
 * Extent the Jquery Engine to add tabs
 * 
 */

App::uses('AppHelper', 'View/Helper');
App::uses('UtilitiesAppHelper', 'Utilities.View/Helper');
App::uses('JqueryEngineHelper', 'View/Helper');

class JqueryUiEngineHelper extends JqueryEngineHelper 
{
	public $_optionMap = array(
		'tabs' => array(
			'collapsible' => 'collapsible',
			'cookie' => 'cookie',
			'deselectable' => 'collapsible',
			'disabled' => 'disabled',
			'selected' => 'selected',
			'spinner' => 'spinner',
			'id_prefix' => 'idPrefix',
			'ajax_options' => 'ajaxOptions',
		),
		'timepicker' => array(
			'minDate' => 'minDate',
			'maxDate' => 'maxDate',
		),
		'timepickerrange' => array(
			'minDate' => 'minDate',
			'maxDate' => 'maxDate',
		),
		'datepicker' => array(
		),
		'datepickerrange' => array(
			'minDate' => 'minDate',
			'maxDate' => 'maxDate',
		),
		'datetimepicker' => array(
		),
		'datetimepickerrange' => array(
			'minDate' => 'minDate',
			'maxDate' => 'maxDate',
		),
		'spectrum' => array(),
	);
	
	public $_callbackArguments = array(
		'tabs' => array(
			'select' => 'event, ui',
			'load' => 'event, ui',
			'show' => 'event, ui',
			'add' => 'event, ui',
			'remove' => 'event, ui',
			'enable' => 'event, ui',
			'disable' => 'event, ui',
		),
		'timepicker' => array(
			'onClose' => 'dateText, inst',
		),
		'timepickerrange' => array(
			'onClose' => 'dateText, inst',
		),
		'datepicker' => array(
			'onClose' => 'dateText, inst',
		),
		'datepickerrange' => array(
			'onClose' => 'dateText, inst',
		),
		'datetimepicker' => array(
			'minDate' => 'minDate',
			'maxDate' => 'maxDate',
			'onClose' => 'dateText, inst',
		),
		'datetimepickerrange' => array(),
		'spectrum' => array(),
	);
	
	public $bufferedMethods = array('tabs', 'timepicker', 'datetimepicker', 'spectrum');
	
	public $templates = array(
			'tabs' => 'var $tabs = %s.tabs({%s});',
			'timepicker' => '%s.timepicker({%s});',
			'timepickerrange' => '
			$.timepicker.timeRange(
				$( "%s" ),
				$( "%s" ),
				{
					%s
				});',
			'datepicker' => '%s.datetimepicker({%s});',
			'datepickerrange' => '
			$.timepicker.dateRange(
				$( "%s" ),
				$( "%s" ),
				{
					%s
				});',
			'datetimepicker' => '%s.datetimepicker({%s});',
			'datetimepickerrange' => '
			$.timepicker.datetimeRange(
				$( "%s" ),
				$( "%s" ),
				{
					%s
				});',
			'spectrum' => '%s.spectrum({%s});',
	);
	
	public function tabs($options = array())
	{
		return $this->_methodTemplate('tabs', $this->templates['tabs'], $options);
	}
	
	public function timepicker($options = array())
	{
	// http://trentrichardson.com/examples/timepicker/
		return $this->_methodTemplate('timepicker', $this->templates['timepicker'], $options);
	}
	
	public function timepickerrange($start = false, $end = false, $options = array(), $start_options = array(), $end_options = array())
	{
	// http://trentrichardson.com/examples/timepicker/
		return $this->_methodTemplate('timepickerrange', $this->templates['timepickerrange'], $options);
	}
	
	public function datepicker($options = array())
	{
	// http://trentrichardson.com/examples/timepicker/
		return $this->_methodTemplate('datepicker', $this->templates['datepicker'], $options);
	}
	
	public function datepickerrange($start_id = false, $end_id = false, $options = array())
	{
	// http://trentrichardson.com/examples/timepicker/
		$options = $this->_mapOptions('datepickerrange', $options);
		$options = $this->_parseOptions($options, array());
		
		return sprintf($this->templates['datepickerrange'], $start_id, $end_id, $options);
	}
	
	public function datetimepicker($options = array())
	{
	// http://trentrichardson.com/examples/timepicker/
		return $this->_methodTemplate('datetimepicker', $this->templates['datetimepicker'], $options);
	}
	
	public function datetimepickerrange($start = false, $end = false, $options = array(), $start_options = array(), $end_options = array())
	{
	// http://trentrichardson.com/examples/timepicker/
		return $this->_methodTemplate('datetimepickerrange', $this->templates['datetimepickerrange'], $options);
	}
	
	public function spectrum($options = array())
	{
	// http://bgrins.github.io/spectrum/
		return $this->_methodTemplate('spectrum', $this->templates['spectrum'], $options);
	}
}