<?php
/**
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.View.Layouts.Emails.text
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
$site_title = (isset($site_title)?$site_title:(Configure::read('Site.title')?Configure::read('Site.title'):' '));
$site_url = (isset($site_url)?$site_url:(Configure::read('Site.base_url')?Configure::read('Site.base_url'):Router::url('/', true)));

// instructions to go with this email
$instructions = (isset($instructions)?$instructions:false);

$sep = (isset($sep)?$sep:str_repeat('-', 80));

if($instructions)
{
	echo __('Instructions/Comments:');
	echo "\n";
	echo $instructions;
	echo "\n\n";
	echo $sep;
	echo "\n\n";
}
echo $content_for_layout;
echo "\n\n";
echo $sep;
echo "\n\n";
echo __('Sent from %s - %s', $site_title, $site_url);
