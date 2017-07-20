<?php
App::uses('PaginatorComponent', 'Controller/Component');

/*
 * Extends the Auth Component and includes things like the auth timeout
 *
 */

class PaginatorExtComponent extends PaginatorComponent
{
	public $components = array('Session','Auth');
}