<?php

App::uses('UtilitiesAppController', 'Utilities.Controller');

class ProctimeSqlStatsController extends UtilitiesAppController 
{
//
	public function admin_proctime($proctime_id = false) 
	{
		$this->Prg->commonProcess();
		
		$conditions = array(
			'ProctimeSqlStat.proctime_id' => $proctime_id,
		);
		
		$this->paginate['order'] = array('ProctimeSqlStat.created' => 'desc');
		$this->paginate['conditions'] = $this->ProctimeSqlStat->conditions($conditions, $this->passedArgs); 
		
		$proctime_sql_stats = $this->paginate();
		$this->set('proctime_sql_stats', $proctime_sql_stats);
	}
}