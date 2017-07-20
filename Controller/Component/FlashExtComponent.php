<?php
App::uses('FlashComponent', 'Controller/Component');


/*
 * Extends the Auth Component and includes things like the auth timeout
 *
 */

class FlashExtComponent extends FlashComponent
{
	public function set($message, $options = array())
	{
		if(!isset($options['element']))
		{
			$options['element'] = 'default';
		}
		
		if (!isset($options['plugin'])) 
		{
			$options['plugin'] = 'Utilities';
		}
		
		return parent::set($message, $options);
	}
	
	public function __call($name, $args)
	{
		$options = array('element' => Inflector::underscore($name));
		
		if (count($args) < 1) {
			throw new InternalErrorException('Flash message missing.');
		}
		if (!empty($args[1])) {
			$options += (array)$args[1];
		}
		
		return $this->set($args[0], $options);
	}
}