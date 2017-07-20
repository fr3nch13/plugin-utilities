<?php
/* 
 * Used to extract Yara and Snort Signatures from a text string
 */
// App::uses('Sanitize', 'Utility');
App::uses('Inflector', 'Core');

class ShellBehavior extends ModelBehavior 
{
	// used to track changes made in the models that need to be displayed when a shell runs
	public $shellOut = array();
	public $shellOutIssues = array();
	public $shellOutIssues_I = 0;
	
	public function shellOut(Model $Model, $input = false, $scope = 'model', $level = 'info', $notify = true, $calling_method_back = 4)
	{
	/*
	 * adds and returns the list of outputs for shell commands from models
	 * used to return what a model is doing, mainly for tracking and debugging purposes
	 */
	 	if($input)
	 	{
	 		$my_pid = getmypid();
	 		if(!$my_pid) $my_pid = '0000';
	 		
	 		if(!isset($Model->shell_input))
	 		{
	 			$Model->shell_input = 3;
	 		}
	 		
	 		if($Model->shell_input == 3)
	 		{
	 			$mem_usage = memory_get_usage();
	 			$mem_usage = ceil($mem_usage / 1024). 'K';
	 			$mem_usage = ceil($mem_usage / 1024). 'M';
		 		$input = '[pid:'. $my_pid. "]\t[mem:".$mem_usage."]\t[Model:". GetCallingMethodName($calling_method_back). "]\t". $input;
	 		}
	 		elseif($Model->shell_input == 2)
	 		{
		 		$input = '[pid:'. $my_pid. "]\t". $input;
	 		}
	 		elseif($Model->shell_input == 1)
	 		{
		 		// do nothing
	 		}
	 		
			$date = 'x'.rand(0, 100).'x '. date('Y-m-d H:i:s');
			
	 		$this->shellOut[$date] = $input;
			
			$level = trim(strtolower($level));
			if($notify and in_array($level, array('error', 'warning', 'notice')))
			{
				if(!isset($this->shellOutIssues[$level]))
				{
					$this->shellOutIssues[$level] = array();
				}
				if(!isset($this->shellOutIssues[$level][$input]))
				{
					$this->shellOutIssues[$level][$input] = array();
				}
				$this->shellOutIssues[$level][$input][] = date('Y-m-d H:i:s');
			}
	 		
	 		if(isset($Model->shell_nolog) and $Model->shell_nolog)
	 		{
	 			if($Model->shell_input)
		 			echo $input. "\n";
	 		}
	 		else
	 		{
			 	CakeLog::write($level, $input, $scope);
	 		}
	 		
	 	}
	 	
	 	// return the output and reset the variable
	 	$out = $this->shellOut;
	 	$this->shellOut = array();
	 	return $out;
	}
	
	public function cronOut(Model $Model, $input = false, $scope = 'cron', $level = 'info', $notify = true)
	{
	// just a wrapper for shellOut
		return $this->shellOut($Model, $input, $scope, $level, $notify, 5);
	}
	
	public function getShellIssues(Model $Model, $previous_issues = array())
	{
		// return the issues and reset the variable;
		$issues = $this->shellOutIssues;
		
		// get the issues from the related models
		foreach($Model->associations() as $assoc_type)
		{
			foreach($Model->{$assoc_type} as $assoc_alias => $assoc_info)
			{
				if($this->shellOutIssues_I < 2 and is_object($Model->{$assoc_alias}) and method_exists($Model->{$assoc_alias}, 'getShellIssues'))
				{
					$this->shellOutIssues_I++;
					$this_issues = $Model->{$assoc_alias}->getShellIssues();
					$this->shellOutIssues_I--;
					if(count($this_issues))
					{
						foreach($this_issues as $level => $level_issues)
						{
							foreach($level_issues as $message => $times)
							{
								$issues[$level][$message] = $times;
							}
						}
					}
				}
			}
		}
		
		return $issues;
	}
	
	public function Shell_exec(Model $Model, $cmd = false)
	{
		if(!$cmd)
			return false;
		try {
			
			$return_var = 0;
			ob_start();
			passthru($cmd, $return_var);
			$results = ob_get_contents();
			ob_end_clean();
			return trim($results);
		}
		catch(Exception $e)
		{
			$Model->modelError = $e->getMessage();
			return false;
		}
		return false;
	}
}