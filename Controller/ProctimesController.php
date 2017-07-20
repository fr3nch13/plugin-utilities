<?php

App::uses('UtilitiesAppController', 'Utilities.Controller');

class ProctimesController extends UtilitiesAppController 
{
//
	public function admin_index() 
	{
		$this->Prg->commonProcess();
		
		$conditions = array();
		
		$this->Proctime->recursive = 0;
		$this->paginate['order'] = array('Proctime.created' => 'desc');
		$this->paginate['conditions'] = $this->Proctime->conditions($conditions, $this->passedArgs); 
		
		$proctimes = $this->paginate();
		$this->set('proctimes', $proctimes);
	}
//
	public function admin_view($id = null) 
	{
		$this->Proctime->id = $id;
		if (!$this->Proctime->exists()) 
		{
			return $this->redirect(array('action' => 'index'));
		}
		
		// get the counts
		$this->Proctime->getCounts = array(
			'ProctimeSqlStat' => array(
				'all' => array(
					'conditions' => array(
						'ProctimeSqlStat.proctime_id' => $id,
					),
				),
			),
			'ProctimeQuery' => array(
				'all' => array(
					'conditions' => array(
						'ProctimeQuery.proctime_id' => $id,
					),
				),
			),
		);
		
		$this->Proctime->recursive = 0;
		$this->set('proctime', $this->Proctime->read(null, $id));
	}

//
	public function admin_delete($id = null) 
	{
		$this->Proctime->id = $id;
		if (!$this->Proctime->exists()) {
			throw new NotFoundException(__('Invalid %s', __('Proctime')));
		}
		if ($this->Proctime->delete()) {
			$this->Session->setFlash(__('%s deleted', __('Proctime')));
			return $this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(__('%s was not deleted', __('Proctime')));
		return $this->redirect(array('action' => 'index'));
	}
	
	public function proctime()
	{
		//Configure::write('debug', 0);
		
		$request_data = array(
			'proctime' => 0,
			'url' => false,
			'controller' => false,
			'action' => false,
			'user_id' => 0,
			'pid' => 0,
			'proctime_id' => 0,
		);
		$request_data_ajax = $request_data;
		
		$proctime_id = false;
		
		// Ajax post
		if ($this->request->is('post') || $this->request->is('put')) 
		{
			$request_json = false;
			if(isset($this->request->data['proctime']))
				$request_json = json_decode($this->request->data['proctime']);
			
			if(isset($request_json->proctime))
			{
				$request_data = array_merge($request_data, $this->Common->objectToArray($request_json));
			}
			
			$request_json_ajax = false;
			if(isset($this->request->data['proctime_ajax']))
				$request_json_ajax = json_decode($this->request->data['proctime_ajax']);
			if(isset($request_json_ajax->proctime))
			{
				$request_data_ajax = array_merge($request_data, $this->Common->objectToArray($request_json_ajax));
			}
			
			// create the proctime record
			if(isset($request_data['proctime']) and $request_data['proctime'] > Configure::read('Proctime.threshold'))
			{
				$this->Proctime->create();
				$this->Proctime->data = $request_data;
				if($this->Proctime->save($this->Proctime->data))
				{
					$proctime_id = $this->Proctime->id;
					$request_data['proctime_id'] = $proctime_id;
					if($debugData = $this->Proctime->debugData())
					{
						foreach($debugData  as $source => $info)
						{
							$stat_data = array(
								'proctime_id' => $proctime_id, 
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
							
							$this->Proctime->ProctimeSqlStat->create();
							$this->Proctime->ProctimeSqlStat->data = $stat_data;
							if($this->Proctime->ProctimeSqlStat->save($this->Proctime->ProctimeSqlStat->data))
							{
								$proctime_sql_stat_id = $this->Proctime->ProctimeSqlStat->id;
								if(isset($info['data']) and is_array($info['data']) and count($info['data']))
								{
					
									$sql_order = 0;
									foreach($info['data'] as $i => $query)
									{
										$sql_order++;
										$query_data = array(
											'proctime_id' => $proctime_id,
											'proctime_sql_stat_id' => $proctime_sql_stat_id,
											'took_ms' => $query['took_ms'],
											'num_rows' => $query['numRows'],
											'affected' => $query['affected'],
											'error' => $query['error'],
											'query' => $query['query'],
											'sql_order' => $sql_order,
										);
										$this->Proctime->ProctimeQuery->create();
										$this->Proctime->ProctimeQuery->data = $query_data;
										if(!$this->Proctime->ProctimeQuery->save($this->Proctime->ProctimeQuery->data))
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