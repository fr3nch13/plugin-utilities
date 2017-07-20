<?php

class CommonComponent extends Component 
{
	public $components = array('Session');
	public $Controller = null;
	
	// defaults
	public $Proctime_config = array(
		'threshold' => 1, // seconds before we log a slow process time
	);

	public function initialize(Controller $Controller) 
	{
		$this->Controller = & $Controller;
		
		$debug = Configure::read('debug');
		if(CakeSession::check('Auth.User.debug'))
		{
			$debug = CakeSession::read('Auth.User.debug');
		}
		
		// used to trigger tracking of sql queries
		// will be reset in beforeRender below
		$this->Proctime_config = Configure::read('Proctime');
		Configure::write('debug', $debug);
	}
	
	public function beforeRender(Controller $Controller)
	{
		// saves the info to the session so we have some context when the proctime ajax call is made
		CakeSession::write('CommonComponent.sql_info', $this->saveDebugData());
		
		return parent::beforeRender($Controller);
	}
	
	public function formatBytes($bytes, $precision = 1)
	{	
		$units = array('B', 'KB', 'MB', 'GB', 'TB');
		
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);
		
		$bytes /= pow(1024, $pow);
		
		return __('%s %s', round($bytes, $precision), $units[$pow]);
	}
	
	public function toByteSize($p_sFormatted = 0) 
	{
		$aUnits = array('B'=>0, 'KB'=>1, 'M'=>2, 'MB'=>2, 'G'=>3, 'GB'=>3, 'T'=>4, 'TB'=>4, 'P'=>5, 'PB'=>5, 'E'=>6, 'EB'=>6, 'Z'=>7, 'ZB'=>7, 'Y'=>8, 'YB'=>8);
		$sUnit2 = strtoupper(trim(substr($p_sFormatted, -2)));
		$sUnit1 = strtoupper(trim(substr($p_sFormatted, -1)));
		$sUnit = false;
		$iUnits = false;
		
		if(intval($sUnit) !== 0) 
		{
			$sUnit = 'B';
		}
		if(in_array($sUnit2, array_keys($aUnits))) 
		{
			$sUnit = $sUnit2;
			$iUnits = trim(substr($p_sFormatted, 0, strlen($p_sFormatted) - 2));
		}
		if(in_array($sUnit1, array_keys($aUnits))) 
		{
			$sUnit = $sUnit1;
			$iUnits = trim(substr($p_sFormatted, 0, strlen($p_sFormatted) - 1));
		}
		
		if(!intval($iUnits) == $iUnits) 
		{
			return false;
		}
		return $iUnits * pow(1024, $aUnits[$sUnit]);
	}
	
	public function maxFileSize()
	{
		$max_upload = ini_get('upload_max_filesize')?ini_get('upload_max_filesize'):0;
		$max_upload = $this->toByteSize($max_upload);
		$max_post = ini_get('post_max_size')?ini_get('post_max_size'):0;
		$max_post = $this->toByteSize($max_post);
		$memory_limit = ini_get('memory_limit')?ini_get('memory_limit'):0;
		$memory_limit = $this->toByteSize($memory_limit);
		$upload_bytes = min($max_upload, $max_post, $memory_limit);
		
		if($upload_bytes)
		{
			return $this->formatBytes($upload_bytes);
		}
		return __('Unknown');
	}
	
	public function dashboardUserRole()
	{
		$userRole = AuthComponent::user('role');
		
		$matches = array();
		if(!preg_match('/^db_(.*)/', $userRole, $matches))
		{
			return false;
		}
		$userRole = $matches[1];
		
		return $userRole;
	}
	
	public function isAdmin()
	{
		if(isset($this->request->params['admin']) and $this->request->params['admin'])
		{
			return true;
		}
		
		return false;
	}
	
	public function roleCheck($roles = false, $user_role = false)
	{
		if(!$roles) return false;
		if(!$user_role)
		{
			$user_role = AuthComponent::user('role');
			if(!$user_role) return false;
		}
		
		if(!is_array($roles))
		{
			$roles = array($roles);
		}
		
		if(in_array($user_role, $roles)) 
		{
			return true;
		}
		return false;
	}
	
	public function saveDebugData()
	{
		$out = array();
		
		App::uses('ConnectionManager', 'Model');
		
		if(!class_exists('ConnectionManager')) 
		{
			return false;
		}
		
		$debug = Configure::read('debug');
		$sqlLogs = array();
		
		if($debug > 1)
		{
			$sources = ConnectionManager::sourceList();
		
			$sqlLogs = array();
			foreach ($sources as $source)
			{
				$db = ConnectionManager::getDataSource($source);
				if(!method_exists($db, 'getLog'))
				{
					continue;
				}
				$sqlLogs[$source] = $db->getLog(false, false);
			}
		}

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
				$i['error'] = '';
				if(!empty($i['params']) && is_array($i['params'])) 
				{
					$bindParam = $bindType = null;
					if(preg_match('/.+ :.+/', $i['query'])) 
					{
						$bindType = true;
					}
					foreach ($i['params'] as $bindKey => $bindVal) 
					{
						if($bindType === true) 
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
					'query' => $i['query'],
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
		
		return $out;
	}
	
	public function objectToArray($obj) 
	{
		$arrObj = is_object($obj) ? get_object_vars($obj) : $obj;
		$arr = '';
		if($arrObj)
		{
			foreach ($arrObj as $key => $val) 
			{
				$val = (is_array($val) || is_object($val)) ? $this->objectToArray($val) : $val;
				$arr[$key] = $val;
			}
		}
		return $arr;
	}
}