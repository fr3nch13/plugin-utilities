<?php

App::uses('UtilitiesAppModel', 'Utilities.Model');
App::uses('CakeSession', 'Model/Datasource');

class Main extends UtilitiesAppModel 
{
	public $useTable = false;
	
	/**
	 * Gathers the version information from composer and git
	 * and parses it
	 */
	public function versions()
	{
		$versions = array();
		
		if(!$composer = $this->Shell_exec('which composer'))
		{
			return false;
		}
		
		// show the root package info first
		putenv("COMPOSER_HOME=".ROOT. DS.".composer");
		$versions_cmd = __('%s show -s --working-dir %s 2>&1', $composer, ROOT);
		if(!$results = $this->Shell_exec($versions_cmd))
		{
			return false;
		}
		
		$version_defaults = array(
			'name' => false,
			'version' => false,
			'description' => false,
			'homepage' => false,
			'path' => false,
			'keywords' => array(),
			'time' => false,
			'timestamp' => false,
			'self' => false,
		);

		$versions_first = $version_defaults;
		$versions_first['self'] = true;
		
		$results = explode("\n", $results);
		foreach($results as $line)
		{
			$parts = array();
			preg_match('/^([^\s]+)\s+\:\s+(.*)/', $line, $parts);
			
			if(count($parts) != 3)
				continue;
			
			$key = $parts[1];
			$value = $parts[2];
			
			if($key == 'descrip.')
				$key = 'description';
			
			if($key == 'keywords')
				$value = explode(', ', $value);
			
			
			if(isset($versions_first[$key]))
				$versions_first[$key] = $value;
		}
		
		// grab info from out root package composer.json file
		if(!is_readable(ROOT.DS.'composer.json'))
			continue;
			
		$composer_info = $this->readComposerJson(ROOT.DS.'composer.json');
		
		if(isset($composer_info['homepage']))
			$versions_first['homepage'] = $composer_info['homepage'];
		
		if(isset($composer_info['keywords']) and $composer_info['keywords'])
			$versions_first['keywords'] = $composer_info['keywords'];
		
		// now use git to find out the root package's version
		
		if($git = $this->Shell_exec('which git'))
		{
			$git_cmd = __('cd %s; %s describe 2>&1', ROOT, $git);
			if($results = $this->Shell_exec($git_cmd))
			{
				$versions_first['version'] = $results;
				$git_cmd = __('cd %s; %s show -s --pretty=format:"%ad" -1 %s 2>&1', ROOT, $git, $versions_first['version']);
				if($results = $this->Shell_exec($git_cmd))
				{
					$versions_first['time'] = $results;
					$versions_first['timestamp'] = strtotime($versions_first['time']);
				}
			}
		}
		
		$versions[$versions_first['name']] = $versions_first;
	
		// read the installed packages from the lock file
		if(!$packages = $this->readComposerJson(ROOT.DS.'composer.lock'))
		{
			return false;
		}
		
		foreach($packages['packages'] as $package)
		{
			$package_name = $package['name'];
			if(isset($package['time']))
				$package['timestamp'] = strtotime($package['time']);
			$versions[$package_name] = array_merge($version_defaults, $package);
		}
		
		return $versions;
	}
	
	public function readComposerJson($path = false)
	{
		if(!$path)
			return false;
		
		if(!is_readable($path))
			return false;

		
		$parts = explode(DS, $path);
		$filename = array_pop($parts);
		
		if(!in_array($filename, array('composer.json', 'composer.lock')))
			return false;
		
		$composer_content = file_get_contents($path);
		
		if(!$results = json_decode($composer_content))
			return false;
		
		unset($composer_content);
		
		$results = $this->objectToArray($results);
		
		return $results;
	}
}