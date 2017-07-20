<?php
// http://simplehtmldom.sourceforge.net/manual.htm
require_once ROOT. DS. 'Vendor'. DS. 'nihfo-vendors/simplehtmldom/simple_html_dom.php';

class DomParserBehavior extends ModelBehavior 
{
	public $settings = array();
	
	protected $_defaults = array();
	
	public $Dom = false;
	
	public function setup(Model $Model, $settings = array())
	{
	}
	
	public function DP_parse(Model $Model, $html = false)
	{
		return str_get_html($html);
	}
}
