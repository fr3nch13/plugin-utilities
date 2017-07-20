<?php

App::uses('AppController', 'Controller');

class UtilitiesAppController extends AppController 
{
	public $components = array(
		// Common functions we would like to have all apps available to them
		'Utilities.Common',
	);
}