<?php

App::uses('Controller', 'Controller');
App::uses('MainController', 'Utilities.Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('View', 'View');
App::uses('HtmlExtHelper', 'Utilities.View/Helper');

class HtmlExtHelperTest extends CakeTestCase 
{
	public $HtmlExt = null;
	public $Controller = null;
	public $View = null;
	public $CakeRequest = null;
	public $CakeResponse = null;
	
	public function setUp()
	{
		parent::setUp();
		$this->CakeRequest = new CakeRequest();
		$this->CakeResponse = new CakeResponse();
		$this->Controller = new MainController($this->CakeRequest, $this->CakeResponse);
		$this->View = new View($this->Controller);
		$this->Html = new HtmlExtHelper($this->View);
	}
	
	public function test_methodExists_getExt()
	{
		$result = get_class_methods($this->Html);
		$this->assertTrue(in_array('getExt', $result));
	}
	
	public function test_method_getAsText()
	{
		$result = $this->Html->getAsText();
		$this->assertTrue(($result === false));
		
		$this->Html->asText(true);
		$result = $this->Html->getAsText();
		$this->assertTrue(($result === true));
	}
	
	public function test_method_getFull()
	{
		$result = $this->Html->getFull();
		$this->assertTrue(($result === false));
		
		$this->Html->setFull(true);
		$result = $this->Html->getFull();
		$this->assertTrue(($result === true));
	}
	
	public function test_method_getExt()
	{
		$result = $this->Html->getExt();
		pr($result);
/*		
		$this->HtmlExt->setFull(true);
		$result = $this->HtmlExt->getFull();
		$this->assertTrue(($result === true));
*/
	}
	
	public function test_method_fixUrl()
	{
		$result = $this->Html->fixUrl(array('controller' => 'main', 'action' => 'versions', 'prefix' => 'admin', 'ext' => 'json'));
		debug($result);
		$this->assertTrue(array_key_exists('admin', $result));
		$this->assertTrue((array_key_exists('prefix', $result) == false));
		$this->assertTrue(isset($result['admin']));
		$this->assertTrue($result['admin'] == true);
		$this->assertTrue(isset($result['ext']));
		$this->assertTrue($result['ext'] == 'json');
	}
	
	public function test_method_url()
	{
		$this->Html->setFull(true);
		pr(Configure::read('Site.base_url'));
		
		$result = $this->Html->url(array('controller' => 'main', 'action' => 'versions', 'prefix' => 'admin', 'ext' => 'json'));
		pr($result);
		$this->assertTrue(strpos($result, Configure::read('Site.base_url')) !== false);
		
		$result = str_replace(Configure::read('Site.base_url'), '', $result);
		
		$this->assertTrue($result == '/admin/main/versions.json');
	}
	
	public function test_method_urlModify()
	{
		$result = $this->Html->urlModify(array('prefix' => 'admin'));
		$this->assertTrue(isset($result['admin']));
		$this->assertTrue($result['admin'] == true);
	}
	
	public function test_method_permaLink()
	{
		$result = $this->Html->permaLink();
		pr($result);
//		$this->assertTrue(isset($result['admin']));
//		$this->assertTrue($result['admin'] == true);
	}
	
}

