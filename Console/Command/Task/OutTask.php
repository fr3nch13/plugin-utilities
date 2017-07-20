<?php

App::uses('String', 'Utility');

class OutTask extends Shell
{
	public $timeStart = null;
	public $timeNow = null;
	
	public $calling_method_back = null;
	
	public function execute(Shell $Shell, $message = null, $newlines = 1, $level = Shell::NORMAL)
	{
		// only if it's no being normally run (e.g. cron, or verbose)
		if($level != Shell::NORMAL)
		{
			if(is_string($message))
			{
				$message = array($message);
			}
			
			if(is_array($message))
			{	
				foreach($message as $i => $line)
				{
					// see if a timestamp is already there
					$year = date('Y');
					if(substr($line, 0, strlen($year)) == $year) continue;
					
					// from models
					// see if the date is stored in the index
					if(substr($i, 0, 1) == 'x')
					{
						$date = preg_replace('/^[0-9x]+\s/', '', $i);
					}
					else
					{
						$date = date('Y-m-d H:i:s');
					}
					$message[$i] = "[". GetCallingMethodName(1). "]\t". $line;
					CakeLog::write($msgLevel, $message[$i]);
					
					$message[$i] = $date. "\t".$message[$i];
				}
			}
		}
		return $message;
	}
	
	public function out($input = false, $scope = 'shell', $level = 'info', $notify = true, $calling_method_back = 4)
	{
		if(!$scope)
			$scope = 'shell';
		if(!$level)
			$level = 'info';
		if(!$calling_method_back)
			$calling_method_back = 4;
		if($this->calling_method_back)
			$calling_method_back = $this->calling_method_back;
		
		if(!$this->timeStart)
			$this->timeStart = time();
		$this->timeNow = time();
		$timeDiff = ($this->timeNow - $this->timeStart);
		
		
	 	if($input)
	 	{
	 		$my_pid = getmypid();
	 		if(!$my_pid) $my_pid = '0000';
	 		
	 		$mem_usage = memory_get_usage();
	 		$mem_usage = ceil($mem_usage / 1024). 'K';
	 		$mem_usage = ceil($mem_usage / 1024). 'M';
		 	$input = '[pid:'. $my_pid. "]\t[mem:".$mem_usage."]\t[time diff:".$timeDiff."]\t[Model:". GetCallingMethodName($calling_method_back). "]\t". $input;
	 		
	 		CakeLog::write($level, $input, $scope);
	 	}
	 	
	 	// return the output and reset the variable
	 	return $input;
	}
	
	public function info($input = false, $scope = 'model')
	{
		return $this->out($input, $scope, 'info');
	}
	
	public function notice($input = false, $scope = 'model')
	{
		return $this->out($input, $scope, 'notice');
	}
	
	public function error($input = false, $scope = 'model')
	{
		return $this->out($input, $scope, 'error');
	}
	
	public function warning($input = false, $scope = 'model')
	{
		return $this->out($input, $scope, 'warning');
	}
	
	public function debug($input = false, $scope = 'model')
	{
		return $this->out($input, $scope, 'debug');
	}
}