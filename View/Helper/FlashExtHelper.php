<?php

App::uses('FlashHelper', 'View/Helper');
App::uses('UtilitiesAppHelper', 'Utilities.View/Helper');

class FlashExtHelper extends FlashHelper 
{
	public function render($key = 'flash', $options = array()) 
	{
		$flash = CakeSession::read("Message.$key");
		
		// check to see if we're using the old way of setting the flash
		// if so, fix the element part
		if($flash and is_array($flash))
		{
			if(isset($flash['element']) and $flash['element'] == 'default')
				$flash['element'] = 'Utilities.Flash/default';
		
			$options = array_merge($flash, $options);
		}
		
		return parent::render($key, $flash);
	}
}