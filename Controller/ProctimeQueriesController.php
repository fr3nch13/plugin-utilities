<?php

App::uses('UtilitiesAppController', 'Utilities.Controller');

class ProctimeQueriesController extends UtilitiesAppController 
{
//
	public function admin_index() 
	{
		$this->Prg->commonProcess();
		
		$conditions = array(
		);
		
		$this->paginate['order'] = array(
		'ProctimeQuery.created' => 'desc',
		'ProctimeQuery.sql_order' => 'asc',
		);
		$this->paginate['conditions'] = $this->ProctimeQuery->conditions($conditions, $this->passedArgs); 
		$this->ProctimeQuery->recursive = 0;
//		$this->ProctimeQuery->contain('ProctimeSqlStat');
		$proctime_queries = $this->paginate();
		$this->set('proctime_queries', $proctime_queries);
	}
//
	public function admin_proctime($proctime_id = false) 
	{
		$this->Prg->commonProcess();
		
		$conditions = array(
			'ProctimeQuery.proctime_id' => $proctime_id,
		);
		
		$this->paginate['order'] = array('ProctimeQuery.sql_order' => 'asc');
		$this->paginate['conditions'] = $this->ProctimeQuery->conditions($conditions, $this->passedArgs); 
		
		$this->ProctimeQuery->recursive = 0;
		$this->ProctimeQuery->contain('ProctimeSqlStat');
		$proctime_queries = $this->paginate();
		$this->set('proctime_queries', $proctime_queries);
	}
}