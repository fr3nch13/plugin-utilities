<?php

App::uses('String', 'Utility');

class DebugTask extends Shell
{
	public function execute(Shell $Shell)
	{
		if((Configure::read('debug') > 1) or (isset($Shell->params['verbose']) and $Shell->params['verbose']))
		{
			$Shell->out();
			$Shell->out();
			$Shell->hr();
			$Shell->out('<info>Debug Data</info>');
			$Shell->hr();
			if(!$data = $this->debugData())
			{
				return;
			}
			
			foreach($data as $source_name => $source)
			{
				$Shell->out();
				$Shell->out(__('Source Name: %s', $source_name));
				$Shell->hr(0, 40);
				if(isset($source['stats']))
				{
					$Shell->out($this->makeList($source['stats']));
					$Shell->hr(0, 30);
				}
				
				foreach($source['data'] as $item)
				{
					$query_data = array(
						'nr' => $item['nr'],
						'error' => $item['error'],
						'affected' => $item['affected'],
						'numRows' => $item['numRows'],
						'took_ms' => $item['took_ms'],
						'query' => html_entity_decode($item['query'], ENT_QUOTES),
					);
					$Shell->out($this->makeList($query_data));
					$Shell->hr(0, 30);
				}
			}
		}
	}
	
	
	public function debugData()
	{
		$out = array();
		
		if (!class_exists('ConnectionManager')) 
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
							if ($bindType === true) 
							{
								$bindParam .= h($bindKey) ." => " . h($bindVal) . ", ";
							} 
							else 
							{
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
				$out[$source] = array(
					'stats' => $stats,
					'data' => $data,
				);
			}
		}
		return $out;
	}
	
	// allow to print out an indented list
	public function makeList($data = array(), $width = 72)
	{	
		$out = array();
		$max = $this->_getMaxLength($data) + 3;
		foreach ($data as $k => $v) 
		{
			$out[] = String::wrap($this->formatForList($k.':', $v, $max), array(
				'width' => $width,
				'indent' => str_repeat(' ', $max),
				'indentAt' => 1
			));
		}
		return $out;
	}
	
	protected function _getMaxLength($collection) 
	{
		$max = 0;
		foreach ($collection as $k => $v) 
		{
			$max = (strlen($k) > $max) ? strlen($k) : $max;
		}
		return $max;
	}
	
	protected function formatForList($k = false, $v = false, $width = 0) 
	{
		if (strlen($k) < $width) 
		{
			$k = str_pad($k, $width, ' ');
		}
		return $k . $v;
	}
}