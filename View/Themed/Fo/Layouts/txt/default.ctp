<?php 
$content_for_layout = strip_tags($content_for_layout);
$content_for_layout = str_ireplace('&nbsp;', ' ', $content_for_layout);
echo $content_for_layout;

/*
$debug = Configure::read('debug');
if(Cache::read('ProctimeInternal.debug_level') !== false)
{
	$debug = Cache::read('ProctimeInternal.debug_level');
}
if($debug)
{
	echo $this->element('Utilities.sql_dump'); 
}
*/
echo $this->element('Utilities.app_errors'); 