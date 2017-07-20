<?php

App::uses('Mysql', 'Model/Datasource/Database');


class MysqlExt extends Mysql 
{
	
	public function renderStatement($type, $data) 
	{
		$query = parent::renderStatement($type, $data);
		
		if(Configure::read('debug') < 3)
			return $query;
		
		if(!in_array($type, array('create', 'update', 'delete')))
		{
			return $query;
		}
		
		if(preg_match('/(`proctime_queries`|`usage_entities`|`usage_counts`)/', $query))
		{
			return $query;
		}
		
		$input = array(
			'timestamp' => date('Y-m-d H:i:s'),
			'type' => $type,
			'user_id' => false,
			'memory' => memory_get_usage(),
			'backtrace' => array(),
			'query' => false,
		);
		
		$input['query'] = $query;
		
		$user_id = false;
		if (class_exists('AuthComponent'))
		{
			$user_id = AuthComponent::user('id');
		}
		$input['user_id'] = $user_id;
		
		$backtraces = debug_backtrace(0);
		$_backtraces = array();
		
		foreach($backtraces as $i => $backtrace)
		{
			$_backtraces[] = array(
				'file' => (isset($backtrace['file'])?$backtrace['file']:false),
				'line' => (isset($backtrace['line'])?$backtrace['line']:false),
				'class' => (isset($backtrace['class'])?$backtrace['class']:false),
				'function' => (isset($backtrace['function'])?$backtrace['function']:false),
			);
			unset($backtraces[$i]);
			unset($backtrace);
		}
		
		unset($backtraces);
		$input['backtrace'] = $_backtraces;
		
		$reload_stdout = false;
		if(CakeLog::stream('stdout'))
		{
			$reload_stdout = true;
			CakeLog::disable('stdout');
		}
		
		CakeLog::write('info', json_encode($input), 'queries');
		
		if($reload_stdout)
		{
			CakeLog::enable('stdout');
		}
		
		return $query;
	}
}