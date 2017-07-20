<?php

App::uses('UtilitiesAppHelper', 'Utilities.View/Helper');

class SubscriptionsHelper extends UtilitiesAppHelper 
{
	public function isSubscription()
	{
		if($this->_View->get('isSubscription'))
			return true;
		return false;
	}
}