<?php
/**
 * File Storage engine for cache.  Filestorage is the slowest cache storage
 * to read and write.  However, it is good for servers that don't have other storage
 * engine available, or have content which is not performance sensitive.
 *
 * You can configure a FileEngine cache, using Cache::config()
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         CakePHP(tm) v 1.2.0.4933
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * File Storage engine for cache.  Filestorage is the slowest cache storage
 * to read and write.  However, it is good for servers that don't have other storage
 * engine available, or have content which is not performance sensitive.
 *
 * You can configure a FileEngine cache, using Cache::config()
 *
 * @package       Cake.Cache.Engine
 */
class CrFileEngine extends CacheEngine {

/**
 * Instance of SplFileObject class
 *
 * @var File
 */
	protected $_File = null;
	
	protected $path_current = false;

/**
 * Settings
 *
 * - path = absolute path to cache directory, default => CACHE
 * - prefix = string prefix for filename, default => cake_
 * - lock = enable file locking on write, default => false
 * - serialize = serialize the data, default => true
 *
 * @var array
 * @see CacheEngine::__defaults
 */
	public $settings = array();

/**
 * True unless FileEngine::__active(); fails
 *
 * @var boolean
 */
	protected $_init = true;

/**
 * Initialize the Cache Engine
 *
 * Called automatically by the cache frontend
 * To reinitialize the settings call Cache::engine('EngineName', [optional] settings = array());
 *
 * @param array $settings array of setting for the engine
 * @return boolean True if the engine has been successfully initialized, false if not
 */
	public function init($settings = array()) {
		$settings += array(
			'engine' => 'File',
			'path' => CACHE,
			'prefix' => 'cake_',
			'lock' => true,
			'serialize' => true,
			'isWindows' => false,
			'mask' => 0666
		);
		parent::init($settings);

		if(DS === '\\') {
			$this->settings['isWindows'] = true;
		}
		if(substr($this->settings['path'], -1) !== DS) {
			$this->settings['path'] .= DS;
		}
		if(!empty($this->_groupPrefix)) {
			$this->_groupPrefix = str_replace('_', DS, $this->_groupPrefix);
		}
		return $this->_active();
	}

/**
 * Garbage collection. Permanently remove all expired and deleted data
 * 
 * @param integer $expires [optional] An expires timestamp, invalidataing all data before.
 * @return boolean True if garbage collection was successful, false on failure
 */
	public function gc($expires = null) {
		return $this->clear(true);
	}

/**
 * Write data for key into cache
 *
 * @param string $key Identifier for the data
 * @param mixed $data Data to be cached
 * @param integer $duration How long to cache the data, in seconds
 * @return boolean True if the data was successfully cached, false on failure
 */
	public function write($key, $data, $duration) {
		if($data === '' || !$this->_init) {
			return false;
		}

		if($this->_setKey($key, true, true) === false) {
			return false;
		}
		
		$key = $this->_fixKey($key);

		$lineBreak = "\n";

		if($this->settings['isWindows']) {
			$lineBreak = "\r\n";
		}

		if(!empty($this->settings['serialize'])) {
			if($this->settings['isWindows']) {
				$data = str_replace('\\', '\\\\\\\\', serialize($data));
			} else {
				$data = serialize($data);
			}
		}

		$expires = time() + $duration;
		$contents = $expires . $lineBreak . $data . $lineBreak;

		if($this->settings['lock']) {
			$this->_File->flock(LOCK_EX);
		}

		$this->_File->rewind();
		$success = $this->_File->ftruncate(0) && $this->_File->fwrite($contents) && $this->_File->fflush();

		if($this->settings['lock']) {
			$this->_File->flock(LOCK_UN);
		}
		
/*
		if(Configure::read('debug') > 0)
		{
			// write it out to the logs
			$input = "Prefix: ". $this->settings['prefix']. "\tPath: ". $this->_File->getPathname();
			$this->_log($input);
		}
*/

		return $success;
	}
	
/**
 * Get the stats of the cache file
 *
 */
	public function fstat($key)
	{
		if(!$this->_init || $this->_setKey($key) === false) {
			return false;
		}
		$key = $this->_fixKey($key);
		
		return $this->_File->fstat();
	}

/**
 * Read a key from the cache
 *
 * @param string $key Identifier for the data
 * @return mixed The cached data, or false if the data doesn't exist, has expired, or if there was an error fetching it
 */
	public function read($key) {
		if(!$this->_init || $this->_setKey($key) === false) {
			return false;
		}
		$key = $this->_fixKey($key);

		if($this->settings['lock']) {
			$this->_File->flock(LOCK_SH);
		}

		$this->_File->rewind();
		$time = time();
		$cachetime = intval($this->_File->current());
		
		if($cachetime !== false && ($cachetime < $time || ($time + $this->settings['duration']) < $cachetime)) {
			if($this->settings['lock']) {
				$this->_File->flock(LOCK_UN);
			}
			return false;
		}

		$data = '';
		$this->_File->next();
		while ($this->_File->valid()) {
			$data .= $this->_File->current();
			$this->_File->next();
		}

		if($this->settings['lock']) {
			$this->_File->flock(LOCK_UN);
		}

		$data = trim($data);

		if($data !== '' && !empty($this->settings['serialize'])) {
			if($this->settings['isWindows']) {
				$data = str_replace('\\\\\\\\', '\\', $data);
			}
			$data = unserialize((string)$data);
		}
		
/*
		if(Configure::read('debug') > 0)
		{
			// write it out to the logs
			$input = "Prefix: ". $this->settings['prefix']. "\tPath: ". $this->_File->getPathname();
			$this->_log($input);
		}
*/
		return $data;
	}

/**
 * Delete a key from the cache
 *
 * @param string $key Identifier for the data
 * @return boolean True if the value was successfully deleted, false if it didn't exist or couldn't be removed
 */
	public function delete($key) {
		if($this->_setKey($key) === false || !$this->_init) {
			return false;
		}
		$path = $this->_File->getRealPath();
		$this->_File = null;
		
		$result = unlink($path);
		if(Configure::read('debug') and $result)
		{
			// write it out to the logs
			$input = "Prefix: ". $this->settings['prefix']. "\tPath: ". $path;
			$this->_log($input);
		}
		return $result;
	}

/**
 * Delete all values from the cache
 *
 * @param boolean $check Optional - only delete expired cache items
 * @return boolean True if the cache was successfully cleared, false otherwise
 */
	public function clear($check = false) {
		if(!$this->_init) {
			return false;
		}
		
		$dir = $this->settings['path']. str_replace('_', DS, $this->settings['prefix']);
		
		$threshold = $now = false;
		if($check) 
		{
			$now = time();
			$threshold = $now - $this->settings['duration'];
		}
		
		$this->_clearDir($dir, $now, $threshold);

/*		
		if(Configure::read('debug') > 0)
		{
			// write it out to the logs
			$input = "Prefix: ". $this->settings['prefix']. "\tPath: ". $dir;
			$this->_log($input);
		}
*/
		return true;
	}
	
	protected function _clearDir($path = false, $now = false, $threshold = false)
	{
	/*
	 * Self recursive way to clear a tree of empty directories recursivly 
	 */
	 	if(!$path) return false;
		if(is_dir($path))
		{
			if($handle = opendir($path))
			{
				while (false !== ($entry = readdir($handle)))
				{
					if(in_array($entry, array('.','..'))) continue;
					$this->_clearDir($path. DS. $entry, $now, $threshold);

				}
				closedir($handle);
			}
			
			$empty = true;
			if($handle = opendir($path))
			{
				while (false !== ($entry = readdir($handle)))
				{
					if(in_array($entry, array('.','..'))) continue;
					$empty = false;
					break;

				}
				closedir($handle);
			}
			
			if($empty)
			{
				$result = exec('rmdir '. $path);
				if(Configure::read('debug') and $result)
				{
					$input = __('GC dir cleared. Prefix: %s - Path: %s', $this->settings['prefix'], $path);
					$this->_log($input);
				}
			}
		}
		elseif(file_exists($path))
		{
			$file = new SplFileObject($path, 'r');
			if($threshold)
			{
				$ctime = $file->getCTime();
				if($ctime > $threshold)
				{
					return true;
				}
				$expires = (int)$file->current();
				if($expires > $now) 
				{
					return true;
				}
			}
			unlink($path);
		}
		return true;
	}

/**
 * Not implemented
 *
 * @param string $key
 * @param integer $offset
 * @return void
 * @throws CacheException
 */
	public function decrement($key, $offset = 1) {
		throw new CacheException(__d('cake_dev', 'Files cannot be atomically decremented.'));
	}

/**
 * Not implemented
 *
 * @param string $key
 * @param integer $offset
 * @return void
 * @throws CacheException
 */
	public function increment($key, $offset = 1) {
		throw new CacheException(__d('cake_dev', 'Files cannot be atomically incremented.'));
	}

/**
 * Sets the current cache key this class is managing, and creates a writable SplFileObject
 * for the cache file the key is referring to.
 *
 * @param string $key The key
 * @param boolean $createKey Whether the key should be created if it doesn't exists, or not
 * @return boolean true if the cache key could be set, false otherwise
 */
	protected function _setKey($key = false, $createKey = false, $createdirs = false) {
		
		$this->path_current = false;
		
		if(!$key)
		{
			return false;
		}
		$groups = null;
		if(!empty($this->_groupPrefix)) {
			$groups = vsprintf($this->_groupPrefix, $this->groups());
		}
		$dir = $this->settings['path'] . $groups;

		if(!is_dir($dir)) {
			mkdir($dir, 0777, true);
		}
		
		$key = $this->_fixKey($key);
		
		$dir = $this->_buildRecursivePath($dir, $key, $createdirs);
		
		if(!is_dir($dir)) {
			return false;
		}
		
		if(!$createKey and !is_file($dir .DS. $key)) {
			return false;
		}
		
		$path = new SplFileInfo($dir .DS. $key);
		
		if(!$createKey and !$path->isFile()) {
			unset($path);
			return false;
		}
		
		$this->path_current = $path->getPathname();
		
		if(empty($this->_File) || $this->_File->getBaseName() !== $key) {
			try {
				$this->_File = $path->openFile('c+');
			} catch (Exception $e) {
				trigger_error($e->getMessage(), E_USER_WARNING);
				return false;
			}
			$exists = file_exists($path->getPathname());
			unset($path);
			
			umask(0);
			if(!$exists && !chmod($this->_File->getPathname(), $this->settings['mask'])) {
				trigger_error(__d(
					'cake_dev', 'Could not apply permission mask "%s" on cache file "%s"',
					array($this->_File->getPathname(), $this->settings['mask'])), E_USER_WARNING);
			}
		}
		return true;
	}
	
/*
 * The cache class likes to add the prefix to the key itself
 * since we're using this as the root directory name, remove it from the key
 *
 */
	protected function _fixKey($key = false)
	{
		// make sure the prefix isn't in the key
		$prefix_len = false;
		if(isset($this->settings['prefix']))
		{
			$prefix_len = strlen($this->settings['prefix']);
		}
		if($key and $prefix_len) 
		{
			if(substr($key, 0, $prefix_len) == $this->settings['prefix'])
			{
				$key = substr($key, $prefix_len, (strlen($key) - $prefix_len));
			}
		}
		return $key;
	}
	
	protected function _buildRecursivePath($dir = false, $key = false, $create = false)
	{	
		if(isset($this->settings['prefix']) and $this->settings['prefix'])
		{
			// add the prefix to the dir
			$dir .= DS. str_replace('_', DS, $this->settings['prefix']);
			$dir = str_replace('//', '/', $dir); 
			$prefix_len = strlen($this->settings['prefix']);
			// remove the prefix from the key
			if($key) 
			{
				if(substr($key, 0, $prefix_len) == $this->settings['prefix'])
				{
					$key = substr($key, $prefix_len, (strlen($key) - $prefix_len));
				}
			}
			
			if(!is_dir($dir))
			{
				mkdir($dir, 0777, true);
				chmod($dir, 0777);
			}
		}
		
		if($key)
		{
			if(!preg_match('/^[a-f0-9]{32}$/', strtolower($key))) $key = md5($key);
			// make the subdirs for the cache dir
			$key_array = preg_split('//', strtolower($key), -1, PREG_SPLIT_NO_EMPTY);
			$i=0;
			foreach($key_array as $k => $char)
			{
				if($i==6) break;
				if(in_array($char, range('a', 'z')))
				{
					$dir .= DS. $char;
					$i++;
				}
				elseif(in_array($char, range(0, 9)))
				{
					$dir .= DS. $char;
					$i++;
				}
				if(!is_dir($dir) and $create)
				{
					mkdir($dir, 0777, true);
					chmod($dir, 0777);
				}
			}
		}
		return $dir;
	}

/**
 * Determine is cache directory is writable
 *
 * @return boolean
 */
	protected function _active() {
		$dir = new SplFileInfo($this->settings['path']);
		if($this->_init && !($dir->isDir() && $dir->isWritable())) {
			$this->_init = false;
			trigger_error(__d('cake_dev', '%s is not writable', $this->settings['path']), E_USER_WARNING);
			return false;
		}
		return true;
	}
	
	protected function _log($msg = false)
	{
	/*
	 * Log information regarding logging
	 */
		if(!$msg) return false;
		if(Configure::read('debug') > 0)
		{
			$msg = "[". GetCallingMethodName(0). "]\t". $msg;
			CakeLog::info($msg, 'cache');
		}
	}

/**
 * Generates a safe key for use with cache engine storage engines.
 *
 * @param string $key the key passed over
 * @return mixed string $key or false
 */
	public function key($key) {
		if(empty($key)) {
			return false;
		}

		$key = Inflector::underscore(str_replace(array(DS, '/', '.'), '_', strval($key)));
		return $key;
	}

/**
 * Recursively deletes all files under any directory named as $group
 *
 * @return boolean success
 **/
	public function clearGroup($group) {
		$directoryIterator = new RecursiveDirectoryIterator($this->settings['path']);
		$contents = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::CHILD_FIRST);
		foreach ($contents as $object) {
			$containsGroup = strpos($object->getPathName(), DS . $group . DS) !== false;
			if($object->isFile() && $containsGroup) {
				unlink($object->getPathName());
			}
		}
		return true;
	}
}