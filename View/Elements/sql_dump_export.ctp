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
if(Cache::read('ProctimeInternal.debug_level') !== false)
{
	$debug = Cache::read('ProctimeInternal.debug_level');
}

if (!class_exists('ConnectionManager') || $debug < 2) {
{

	return false;
}

$noLogs = !isset($sqlLogs);
if ($noLogs)
{
	$sources = ConnectionManager::sourceList();

	$sqlLogs = array();
	foreach ($sources as $source)
	{
		$db = ConnectionManager::getDataSource($source);
		if (!method_exists($db, 'getLog'))
		{
			continue;
		}
		$sqlLogs[$source] = $db->getLog();
	}
}

if ($noLogs || isset($_forced_from_dbo_))
{
	foreach ($sqlLogs as $source => $logInfo)
	{
		$stats = array(
			'source' => $source,
			'count' => $logInfo['count'],
			'time' => $logInfo['time']
		);
		$data = array();
		
		foreach ($logInfo['log'] as $k => $i)
		{
			$i += array('error' => '');
			if (!empty($i['params']) && is_array($i['params'])) 
			{
				$bindParam = $bindType = null;
				if (preg_match('/.+ :.+/', $i['query'])) 
				{
					$bindType = true;
				}
				foreach ($i['params'] as $bindKey => $bindVal) 
				{
					if ($bindType === true) {
						$bindParam .= h($bindKey) ." => " . h($bindVal) . ", ";
					} else {
						$bindParam .= h($bindVal) . ", ";
					}
				}
				$i['query'] .= " , params[ " . rtrim($bindParam, ', ') . " ]";
			}
			$data[] = array(
				'nr' => ($k + 1),
				'query' => h($i['query']),
				'error' => $i['error'],
				'affected' => $i['affected'],
				'numRows' => $i['numRows'],
				'took_ms' => $i['took'],
			);
		}
		echo $this->Exporter->view($data, $stats, $this->request->params['ext'], Inflector::camelize(Inflector::singularize($this->request->params['controller'])));
	}
}
?>