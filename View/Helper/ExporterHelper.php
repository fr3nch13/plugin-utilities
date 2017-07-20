<?php

App::uses('UtilitiesAppHelper', 'Utilities.View/Helper');

/*
 * Used to handle formatting and exporting data based on their extension
 */
class ExporterHelper extends UtilitiesAppHelper 
{
	public function view($data = array(), $stats = array(), $ext = false, $model = false, $comment_out = false, $include_comments = true)
	{
		// add the filter to the stats
		if($stats)
		{
			if(isset($this->params['named']['q']) and !isset($stats['filter']))
			{
				$q = $this->params['named']['q'];
				if(stripos($q, "\n") !== false)
				{
					$q_parts = explode("\n", $q);
					$q = array();
					foreach($q_parts as $q_part)
					{
						$q[] = trim($q_part);
					}
				}
				$stats['filter'] = $q;
			}
		}
		
		if($ext == 'json')
		{
			return $this->view_json($data, $stats, $model, $comment_out, $include_comments);
		}
		elseif($ext == 'xml')
		{
			return $this->view_xml($data, $stats, $model, $comment_out, $include_comments);
		}
		elseif($ext == 'csv')
		{
			return $this->view_csv($data, $stats, $model, $comment_out, $include_comments);
		}
	}
	
	public function view_json($data = array(), $stats = array(), $model = false, $comment_out = false, $include_comments = true)
	{
		$modelp = Inflector::pluralize($model);
		$models = Inflector::singularize($model);
		$data = array(
			'root' => array(
				'Stats' => $stats,
				$modelp => array($models => $data),
			),
		);
		
		if(Configure::read('debug') > 0)
		{
			$data['root']['debug'] = $this->debugData();
		}

		return json_encode($data);
	}
	
	public function view_xml($data = array(), $stats = array(), $model = false, $comment_out = false, $include_comments = true)
	{
		$modelp = Inflector::pluralize($model);
		$models = Inflector::singularize($model);
		
		
        if($stats)
        {
        	$stats_line = array();
        	foreach($stats as $k => $v)
        	{
        		if(is_array($v))
        		{
        			$old_k = $k;
        			$k = Inflector::pluralize($k);
					$stats[$k] = array($old_k => $v);
					unset($stats[$old_k]);
        		}
        	}
        }
		
		$data = array(
			'root' => array(
				'Stats' => $stats,
				$modelp => array($models => $data),
			),
		);
		
		if(Configure::read('debug') > 0)
		{
			$data['root']['debug'] = $this->debugData();
		}

		$xmlObject = Xml::fromArray($data);
		return $xmlObject->asXML();
	}
	
	public function view_csv($data = array(), $stats = array(), $model = false, $comment_out = false, $include_comments = true)
	{
		$modelp = Inflector::pluralize($model);
		$models = Inflector::singularize($model);
		
		// get the header
		if(!is_array($data)) return false;
		if(!count($data)) return false;
		
		$content = '';
		if($include_comments)
		{
			$content = '## new lines in the content are replaced by \' [newline] \' (including the single space before, and after the brackes)'. "\n";
			$content .= "## \n## \n";
		}
		
		$line_first = array_shift($data);
		$headers = array_keys($line_first);
		array_unshift($data, $line_first);
		array_unshift($data, $headers);
		
		ob_start();
        $csvBuffer = fopen("php://output", 'w');
        foreach($data as $val) {
        	$val = preg_replace('/\n+/', ' [newline] ', $val);
            fputcsv($csvBuffer, $val);
        }
        fclose($csvBuffer);
        
        $content .= ob_get_clean();
		
		if($comment_out and $include_comments) 
		{
			$content = explode("\n", $content);
			foreach($content as $i => $line)
			{
				$content[$i] = '## '. $line;
			}
			$content = implode("\n", $content);
		}
        
        if($stats and $include_comments)
        {
        	$content .= "\n\n";
        	$content .= "### STATS\n";
        	foreach($stats as $k => $v)
        	{
        		$stats_line_headers = array();
        		$stats_line_rows = array();
        		$row_cnt = 0;
        		
        		if(is_array($v))
        		{
        			$row_cnt++;
        			$v = Hash::flatten($v);
        			
        			if(is_array($v))
        			{
        				foreach($v as $kk => $vv)
        				{
        					
        					$stats_line_headers[$kk] = $kk;
        					$stats_line_rows[$row_cnt][$kk] = $vv;
        				}
        			}
					else
					{
						$v = implode(',', $v);
        				$stats_line_headers[$k] = $k;
        				$stats_line_rows[$row_cnt][$k] = $v;
					}
        		}
        		else
        		{
        			$stats_line_headers[$k] = $k;
        			$stats_line_rows[$row_cnt][$k] = $v;
        		}
        		
        		$content .= '## '. implode(', ', $stats_line_headers). "\n";
        		foreach($stats_line_rows as $stats_line_row)
        		{
        			$content .= '## '. implode(', ', $stats_line_row). "\n";
        		}
        	}
        }
		
		$debug = array();
		if(Configure::read('debug') > 0 and $include_comments)
		{
			$debug = $this->debugData();
		}
		
		if($debug and $include_comments)
		{
        	$content .= "\n\n";
        	$content .= "### DEBUG\n";
			foreach($debug as $source => $debug_data)
			{
				$content .= "# \n". $this->view_csv($debug_data['data'], $debug_data['stats'], 'query', true);
			}
        }
        
        return $content;
	}
	
	public function debugData()
	{
		if (!class_exists('ConnectionManager') || Configure::read('debug') < 2) 
		{
			return array();
		}
		
		$out = array();
		
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
}
