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
 * @package       Cake.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
echo $content_for_layout; 

$debug = Configure::read('debug');
if(CakeSession::check('ProctimeInternal.debug_level'))
{
	$debug = CakeSession::read('ProctimeInternal.debug_level');
}
if($debug)
{
	$rand_int = rand(1, 1000);
?><script type="text/javascript">
		//<![CDATA[
		$(document).ready(function ()
		{
			$.post(
            	"<?php echo Router::url(array('controller' => 'proctimes', 'action' => 'proctime', 'plugin' => 'utilities', 'admin' => false)); ?>",  
            	{proctime: $("#proctime").text(), proctime_ajax: $("#proctime_ajax_<?php echo $rand_int?>").text()}, 
            	function(responseText)
            	{  
                	$("#proctime_ajax_results_<?php echo $rand_int?>").html(responseText);  
            	},  
            	"html"  
        	);
		});
		//]]>
	</script>
	<div id="proctime_ajax_<?php echo $rand_int?>" class="ajax_data"><?php 
		$proctime_data = array(
			'proctime' => (microtime(true) - PROC_START),
			'url' => Router::url(null, true),
			'controller' => $this->params["controller"],
			'action' => $this->params["action"],
			'user_id' => AuthComponent::user('id'),
			'pid' => getmypid(),
		);
		echo json_encode($proctime_data);
	?></div>
	<div id="proctime_ajax_results_<?php echo $rand_int?>" class="proctime_results"></div><?php
	echo $this->element('Utilities.app_errors'); 
	echo $this->element('Utilities.sql_dump'); 
}