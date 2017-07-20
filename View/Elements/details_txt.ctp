<?php
/**
 * File: /app/View/Elements/details_txt.ctp
 * 
 * Use: provides a standard for displaying a list of details.
 *
 * Usage: echo $this->element('details_txt', array([details]));
 */
$title = (isset($title)?$title:__('Details'));
$details = (isset($details)?$details:array());
$tab = (isset($tab)?$tab:"\t");
$sep_long = (isset($sep_long)?$sep_long:str_repeat('-', 72));
$sep_short = (isset($sep_short)?$sep_short:str_repeat('-', 30));

$out = array();

$out[] = '';
$out[] = $title;
$out[] = $sep_short;

$details_formatted = array();
foreach ($details as $detail)
{
	$value = strip_tags($detail['value']);
	$details_formatted[$detail['name']] = $value;
}
$out = array_merge($out, $this->Wrap->makeList($details_formatted));
$out[] = $sep_long;
echo implode("\n", $out);