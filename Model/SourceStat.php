<?php

App::uses('AppModel', 'Model');
/**
 * SourceStat Model
 *
 */
class SourceStat extends AppModel 
{
	public $useTable = false;
	
	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
		),
	);
	
	public $hasMany = array(
		'SourceStatLog' => array(
			'className' => 'SourceStatLog',
			'foreignKey' => 'source_stat_id',
			'dependent' => true,
		),
	);
	
	public $actsAs = array(
		'Utilities.Common', 
		'Utilities.Shell', 
		'Utilities.Email',
		'Utilities.Nslookup',
		'Utilities.PassiveTotal',
		'Utilities.VirusTotal',
		'Utilities.Whois',
	);
	
	// define the fields that can be searched
	public $searchFields = array(
	);
	
	public function updateStats()
	{
	///// ran from a cron job.
	///// used to update the stats of the servers
	// $create_log = if we should copy the current stats to the historical logs - should only be done from the cron job
	// $server_ids = the list of servers to update. If none are given, it updates all found servers from the configured cache settings
	
		$time_start = microtime(true);
		
		$this->shellOut(__('Updating External Source Stats.'), 'source_stats');
		
		/// Get all of the stats managed by the Whois Behavior
		$source_stats = $this->Whois_getStats();

		/// Get all of the stats managed by the Nslookup Behavior
		$source_stats = array_merge($source_stats, $this->NS_getStats());
		
		// Get the stats from PassiveTotal
		$source_stats = array_merge($source_stats, $this->PT_getStats());
		
		// Get the stats from VirusTotal
		$source_stats = array_merge($source_stats, $this->VT_getStats());
		
		$this->shellOut(__('Found %s source stats.', count($source_stats)), 'source_stats');
	 	
	 	$mark_important = false;
	 	foreach($source_stats as $i => $source_stat)
	 	{
	 		// if we should mark this email important
	 		if(isset($source_stat['mark_important']))
	 		{
	 			if($source_stat['mark_important'])
	 			{
	 				$mark_important = true;
	 			}
	 		}
	 		$this->shellOut(__('Source stats for %s - %s', $i, json_encode($source_stat)), 'source_stats');
	 	}
		
		// for now, just email the stats to the admins
		// all Admin 
		$adminEmails = $this->User->adminEmails();
		foreach($adminEmails as $adminEmail)
		{
			$emails[$adminEmail] = $adminEmail;
		}
		
	 	// rebuild it to use the EmailBehavior from the Utilities Plugin
	 	$this->Email_reset();
	 	
	 	if($mark_important)
	 	{
	 		$this->Email_setHeader('Important', 'high');
	 		$this->Email_setHeader('X-Priority', 1);
	 	}
		
		// set the variables so we can use view templates
		$viewVars = array(
			'source_stats' => $source_stats,
		);
		$this->Email_set('debug', true);
		
		$this->Email_set('to', $emails);
		$this->Email_set('subject', __('%s - Count: %s', __('Source Stats'), count($source_stats)));
		$this->Email_set('viewVars', $viewVars);
		$this->Email_set('template', 'Utilities.email_source_stats');
		
		if($this->Email_executeFull())
		{
			$this->shellOut(__('Sent %s source stats to %s.', count($source_stats), implode(', ', $emails)), 'source_stats');
		}
		
return $source_stats;


///////////// for later as an example if we want to store in the database
		
		$this->shellOut(__('Found stats for %s Sources', count($memcache_stats)), 'source_stats');
		
		foreach($memcache_stats as $server => $stats)
		{
			if(!is_array($stats)) continue;
			
			$hosthash = $this->_serverHash($server);
			
			// see if a record already exists for this server.
			// this would be the record from the last time this was ran
			$last_stats = $this->findByHosthash($hosthash);
			
			if(!isset($last_stats[$this->alias]['id']))
			{
				$this->create();
				$this->data[$this->alias] = $stats;
				// add some info the the $this->data
				$this->data[$this->alias]['hosthash'] = $hosthash;
				$server_parts = $this->_parseServerString($server);
				$this->data[$this->alias]['host'] = $server_parts[0];
				$this->data[$this->alias]['port'] = $server_parts[1];
			}
			else
			{
				$this->data[$this->alias] = array_merge($last_stats[$this->alias], $stats);
				$this->id = $this->data[$this->alias]['id'];
				if(isset($this->data[$this->alias]['modified']))
					$this->data[$this->alias]['modified'] = date('Y-m-d H:i:s');
			}
			
			// update the stats
			$return = $this->save($this->data);
			
			// new server, no need to save the current as also an old record
			if(!$last_stats) continue;
			
			// only if we need to create an historical account
			// only the cron script does this
			if(!$create_log) continue;
			
			// save the old record in the logs
			$last_stats[$this->alias]['source_stat_id'] = $last_stats[$this->alias]['id'];
			unset($last_stats[$this->alias]['id']);
			
			if(isset($last_stats[$this->alias]['modified']))
				unset($last_stats[$this->alias]['modified']);
			
			if(isset($last_stats[$this->alias]['created']))
				unset($last_stats[$this->alias]['created']);
			
			$this->SourceStatLog->create();
			$this->SourceStatLog->data = array(
				'SourceStatLog' => $last_stats[$this->alias],
			);
			$this->SourceStatLog->save($this->SourceStatLog->data);
		
//			$time_end = microtime(true);
//			$time = $time_end - $time_start;
//			$this->shellOut(__('Completed Memcache Server stat logging - took %s seconds', $time), 'source_stats');
		}
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		
		$this->shellOut(__('Completed Memcache Server stats updates for %s Servers - took %s seconds', count($memcache_stats), $time), 'source_stats');
		
		// purge older logs
		$this->SourceStatLog->deleteAll(array('SourceStatLog.created <' => date('Y-m-d H:i:s', strtotime('-1 year'))), false, false);
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		
		$this->shellOut(__('Completed Memcache stat purge - took %s seconds', $time), 'source_stats');
		
		return true;
	}
	
	public function totalMemcacheStats($server_ids = array())
	{
		$memcache_obj = new Memcache;
		
		$servers = $this->getSourceStats($server_ids);
		
		foreach($servers as $server)
		{
			$memcache_obj->addServer($server[0], $server[1]); 
		}
		
		return $memcache_obj->getExtendedStats();
	}
	
	public function getSourceStats($server_ids = array())
	{
		// update only specific and known servers
		if($server_ids)
		{
			$fixed_servers = $this->getUpdateServerList($server_ids);
			return $fixed_servers;
		}
		// include the list of known servers
		else
		{
			$fixed_servers = $this->getUpdateServerList();
		}
		
		// include the list of known servers from configuration files
		// some may have been added
		$found_servers = array();
		$configured = Cache::configured();
		
		foreach($configured as $config_name)
		{
			$settings = Cache::settings($config_name);
			if(!isset($settings['engine'])) continue;
			if(!in_array(strtolower($settings['engine']), array('memcache', 'memcached')) ) continue;
			
			if(isset($settings['servers']))
			{
				if(!is_array($settings['servers'])) $settings['servers'] = array($settings['servers']);
				
				foreach($settings['servers'] as $server)
				{
					$found_servers[$server] = $server;
				}
			}
		}
		
		if($found_servers)
		{
			// detect the port
			foreach($found_servers as $found_server)
			{
				$hosthash = $this->_serverHash($server);
				$fixed_servers[$hosthash] = $this->_parseServerString($found_server);
			}
		}
		
		return $fixed_servers;
	}
	
	public function configsUsingMemcache($host = false, $port = 11211)
	{
		$configured = Cache::configured();
		
		$configs = array();
		
		foreach($configured as $config_name)
		{
			$settings = Cache::settings($config_name);
			if(!isset($settings['engine'])) continue;
			if(!in_array(strtolower($settings['engine']), array('memcache', 'memcached')) ) continue;
			
			if(isset($settings['servers']))
			{
				if(!is_array($settings['servers'])) $settings['servers'] = array($settings['servers']);
				
				if(!$host)
				{
					$configs[$config_name] = $settings;
				}
				else
				{
					foreach($settings['servers'] as $server)
					{
						$server_parts = $this->_parseServerString($server);
						if($server_parts[0] == $host and $server_parts[1] == $port)
						{
							$configs[$config_name] = $settings;
						}
					}
				}
			}
		}
		return $configs;
	}
	
	public function getUpdateServerList($server_ids = array())
	{
		//// get the list of currently known servers in a specific array format
		$conditions = array();
		if($server_ids)
		{
			if(count($server_ids) === 1) $server_ids = array_pop($server_ids);
			$conditions[$this->alias.'.id'] = $server_ids;
		}
		
		$servers = $this->find('all', array(
			'conditions' => $conditions,
			'recursive' => -1,
		));
		
		$out = array();
		foreach($servers as $server)
		{
			$hosthash = $server[$this->alias]['hosthash'];
			$out[$hosthash] = array($server[$this->alias]['host'], $server[$this->alias]['port']);
		}
		return $out;
	}
	
	// taken from http://api.cakephp.org/2.4/source-class-MemcacheEngine.html#105
	protected function _parseServerString($server) {
		if ($server[0] === 'u') {
			return array($server, 0);
		}
		if (substr($server, 0, 1) === '[') {
			$position = strpos($server, ']:');
			if ($position !== false) {
				$position++;
			}
		} else {
			$position = strpos($server, ':');
		}
		$port = 11211;
		$host = $server;
		if ($position !== false) {
			$host = substr($server, 0, $position);
			$port = substr($server, $position + 1);
		}
		return array($host, $port);
	}
	
	protected function _serverHash($server)
	{
		$server_parts = $this->_parseServerString($server);
		return md5(implode(':', $server_parts));
	}
}