<?php

App::uses('UtilitiesAppController', 'Utilities.Controller');

class MainController extends UtilitiesAppController 
{	
	public function versions()
	{
		if(!$versions = $this->Main->versions())
		{
		}
		
		$this->set(compact('versions'));
		$this->set('_serialize', array('versions'));
	}
	/**
	 * The Main admin page
	 */
	public function admin_index() 
	{
	}
	
	/**
	 * Shows the version of all of the software installed
	 */
	public function admin_versions()
	{
		if(!$versions = $this->Main->versions())
		{
		}
		
		$this->set(compact('versions'));
		$this->set('_serialize', array('versions'));
	}
}