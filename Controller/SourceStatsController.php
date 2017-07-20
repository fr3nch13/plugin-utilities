<?php

App::uses('UtilitiesAppController', 'Utilities.Controller');

class SourceStatsController extends UtilitiesAppController 
{
//
	public function admin_index() 
	{
		$this->Prg->commonProcess();
		
		$conditions = array();
		
		$this->SourceStat->updateStats();
	}
//
	public function admin_view($id = null) 
	{
		$this->SourceStat->id = $id;
		if (!$this->SourceStat->exists()) 
		{
			return $this->redirect(array('action' => 'index'));
		}
		
		$this->SourceStat->recursive = 0;
		$this->set('source_stat', $this->SourceStat->read(null, $id));
	}

//
	public function admin_delete($id = null) 
	{
		$this->SourceStat->id = $id;
		if (!$this->SourceStat->exists()) {
			throw new NotFoundException(__('Invalid %s', __('SourceStat')));
		}
		if ($this->SourceStat->delete()) {
			$this->Session->setFlash(__('%s deleted', __('SourceStat')));
			return $this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('%s was not deleted', __('SourceStat')));
		return $this->redirect(array('action' => 'index'));
	}
	
	public function source_stat()
	{
		//Configure::write('debug', 0);
		
		$request_data = array(
			'source_stat' => 0,
			'url' => false,
			'controller' => false,
			'action' => false,
			'user_id' => 0,
			'pid' => 0,
			'source_stat_id' => 0,
		);
		$request_data_ajax = $request_data;
		
		$source_stat_id = false;
		
		// Ajax post
		if ($this->request->is('post') || $this->request->is('put')) 
		{
			$request_json = json_decode($this->request->data['source_stat']);
			if(isset($request_json->source_stat))
			{
				$request_data = array_merge($request_data, $this->Common->objectToArray($request_json));
			}
			
			$request_json_ajax = json_decode($this->request->data['source_stat_ajax']);
			if(isset($request_json_ajax->source_stat))
			{
				$request_data_ajax = array_merge($request_data, $this->Common->objectToArray($request_json_ajax));
			}
			
			// create the source_stat record
			if($request_data['source_stat'] > Configure::read('SourceStat.threshold'))
			{
				$this->SourceStat->create();
				$this->SourceStat->data = $request_data;
				if($this->SourceStat->save($this->SourceStat->data))
				{
					$source_stat_id = $this->SourceStat->id;
					$request_data['source_stat_id'] = $source_stat_id;
					if($debugData = $this->SourceStat->debugData())
					{
						foreach($debugData  as $source => $info)
						{
							$stat_data = array(
								'source_stat_id' => $source_stat_id, 
								'sql_source' => 'default',
								'sql_count' => 0,
								'sql_times' => 0,
							);
							if(isset($info['stats']))
							{
								$stat_data = array_merge($stat_data, array(
									'sql_source' => ($info['stats']['source']?$info['stats']['source']:''),
									'sql_count' => ($info['stats']['count']?$info['stats']['count']:0),
									'sql_time' => ($info['stats']['time']?$info['stats']['time']:0),
								));
							}
							
							$this->SourceStat->SourceStatSqlStat->create();
							$this->SourceStat->SourceStatSqlStat->data = $stat_data;
							if($this->SourceStat->SourceStatSqlStat->save($this->SourceStat->SourceStatSqlStat->data))
							{
								$source_stat_sql_stat_id = $this->SourceStat->SourceStatSqlStat->id;
								if(isset($info['data']) and is_array($info['data']) and count($info['data']))
								{
					
									$sql_order = 0;
									foreach($info['data'] as $i => $query)
									{
										$sql_order++;
										$query_data = array(
											'source_stat_id' => $source_stat_id,
											'source_stat_sql_stat_id' => $source_stat_sql_stat_id,
											'took_ms' => $query['took_ms'],
											'num_rows' => $query['numRows'],
											'affected' => $query['affected'],
											'error' => $query['error'],
											'query' => $query['query'],
											'sql_order' => $sql_order,
										);
										$this->SourceStat->SourceStatQuery->create();
										$this->SourceStat->SourceStatQuery->data = $query_data;
										if(!$this->SourceStat->SourceStatQuery->save($this->SourceStat->SourceStatQuery->data))
										{
										}
									}
								}
							}
						}
					}
				}
			}
		}
		
		$this->set('request_data', $request_data);
		$this->set('request_data_ajax', $request_data_ajax);
		return $this->render(null, 'ajax_nodebug');
	}
}