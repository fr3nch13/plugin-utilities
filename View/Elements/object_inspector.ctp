<?php

$this->set('trackReferer', true);

// main title of the page
$page_title = (isset($page_title)?$page_title:'');
$page_subtitle = (isset($page_subtitle)?$page_subtitle:'');
$page_subtitle2 = (isset($page_subtitle2)?$page_subtitle2:false);
$page_options_title = (isset($page_options_title)?$page_options_title:__('Options'));
$page_options = (isset($page_options)?$page_options:array());
$page_options_title2 = (isset($page_options_title2)?$page_options_title2:__('More Options'));
$page_options2 = (isset($page_options2)?$page_options2:array());
$page_options_html = (isset($page_options_html)?$page_options_html:array()); 
$page_description = (isset($page_description)?$page_description:false);
$th = (isset($th)?$th:array());
$td = (isset($td)?$td:array());

$use_filter = (isset($use_filter)?$use_filter:false);
$use_export = (isset($use_export)?$use_export:(count($td)?true:false));


$no_records = (isset($no_records)?$no_records:__('No records were found.'));

$fields = (isset($fields)?$fields:array());

$rows  = (isset($rows)?$rows:array());
