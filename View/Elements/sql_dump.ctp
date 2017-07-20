<?php
/**
 * SQL Dump element. Dumps out SQL log information
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.View.Elements
 * @since         CakePHP(tm) v 1.3
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

$debug = Configure::read('debug');

$sqlLogs = false;
if(!class_exists('ConnectionManager') || $debug < 2) {
	return false;
}

if($sqlLogs = CakeSession::read('CommonComponent.sql_info'))
{
	CakeSession::write('CommonComponent.sql_info', array());
}

if($sqlLogs):
?>
<div class="debugging no-print">
	<div class="debugging-options" style="text-align: right;">
		<a href="#" class="show-sql-logs button"><?php echo __('Show sql Logs'); ?></a>
		<a href="#" class="hide-sql-logs button"><?php echo __('Hide sql Logs'); ?></a>
	</div>
<?php
	foreach ($sqlLogs as $source => $logInfo):
		$text = $logInfo['stats']['count'] > 1 ? 'queries' : 'query';
		printf(
			'<table class="cake-sql-log" id="cakeSqlLog_%s" summary="Cake SQL Log" cellspacing="0">',
			preg_replace('/[^A-Za-z0-9_]/', '_', uniqid(time(), true))
		);
		
	?>
	<caption>
		<?php printf('<b>[Debugging level %s is enabled]</b> (%s) %s %s took %s ms', $debug, $source, $logInfo['stats']['count'], $text, $logInfo['stats']['time']); ?>
	</caption>
	<thead>
		<tr><th>Nr</th><th>Query</th><th>Error</th><th>Affected</th><th>Num. rows</th><th>Took (ms)</th></tr>
	</thead>
	<tbody>
	<?php
		foreach ($logInfo['data'] as $k => $i) :
			
			echo "<tr><td>" . $i['nr'] . "</td><td>" . h($i['query']) . "</td><td>{$i['error']}</td><td style = \"text-align: right\">{$i['affected']}</td><td style = \"text-align: right\">{$i['numRows']}</td><td style = \"text-align: right\">{$i['took_ms']}</td></tr>\n";
		endforeach;
	?>
	</tbody></table>
	<?php
	endforeach;
else:
	echo '<p>Encountered unexpected $sqlLogs cannot generate SQL log</p>';
endif;

// add the cacher stats if they exist
if(isset($fstat_data['stats']['ctime']) and $fstat_data['stats']['ctime']):
	printf(
		'<table class="cake-sql-log" id="cakeCacherFstats_%s" summary="Cacher Fstats Log" cellspacing="0">',
		preg_replace('/[^A-Za-z0-9_]/', '_', uniqid(time(), true) )
	);
	echo __('<caption><b>[Recacher Stats]</b></caption>');
?>

	<thead>
		<tr><th>Model</th><th>Cache Key</th><th>Created</th><th>Expires</th></tr>
	</thead>
	<tbody>
<?php
	foreach($cacher_fstats as $model => $fstats)
	{
		foreach($fstats as $cache_key => $fstat_data)
		{
			$created = $fstat_data['stats']['ctime_nice'];
			$expires = ($fstat_data['stats']['ctime'] + $fstat_data['stats']['cache_settings']['duration']);
			$expires = date('Y-m-d H:i:s', $expires);
			echo __('<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>', $model, $cache_key, $created, $expires);
		}
	}
?>
</tbody></table>
</div>
<?php

unset($cacher_fstats);
endif;
	$this->Js->buffer($this->Js->get('table.cake-sql-log td')->event('mouseenter', '$(this).parent().addClass(\'hovered\')', array('stop' => false)));
	$this->Js->buffer($this->Js->get('table.cake-sql-log td')->event('mouseleave', '$(this).parent().removeClass(\'hovered\')', array('stop' => false)));
	echo $this->Js->writeBuffer();
?>
	<script type="text/javascript">
		//<![CDATA[
		$(document).ready(function ()
		{
        		$('.debugging table.cake-sql-log').hide();
			$('.debugging .debugging-options a.hide-sql-logs').hide();
			$('.debugging .debugging-options a.hide-sql-logs').click(function(e){
				e.preventDefault();
				$(this).parents('.debugging').find('table.cake-sql-log').hide();
				$(this).parents('.debugging-options').find('a.show-sql-logs').show();
				$(this).hide();
			});
			$('.debugging .debugging-options a.show-sql-logs').click(function(e){
				e.preventDefault();
				$(this).parents('.debugging').find('table.cake-sql-log').show();
				$(this).parents('.debugging-options').find('a.hide-sql-logs').show();
				$(this).hide();
			});
		});
		//]]>
	</script>
<div class="clearb"> </div>