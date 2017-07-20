<?php
umask(0);
/***
 *
 * Configuration settings for the utilities plugin
 *
 */

/**
 * Requirements for cakephp 2.2
 * http://book.cakephp.org/2.0/en/appendices/2-2-migration-guide.html#required-steps-to-upgrade
 */
Configure::write('Dispatcher.filters', array(
	'AssetDispatcher',
	'Utilities.CacheExtDispatcher'
));

/**
 * So we can handle the errors ourselves
 * http://book.cakephp.org/2.0/en/development/errors.html
 */
App::uses('AppError', 'Utilities.Lib');
Configure::write('Error.handler', 'AppError::handleError');


/**
 * To force browser cache refreshing
 */
Configure::write('Asset.timestamp', true);

/*
Router::resourceMap(array(
    array('action' => 'index', 'method' => 'GET', 'id' => false),
    array('action' => 'view', 'method' => 'GET', 'id' => true),
    array('action' => 'add', 'method' => 'POST', 'id' => false),
    array('action' => 'edit', 'method' => 'PUT', 'id' => true),
    array('action' => 'delete', 'method' => 'DELETE', 'id' => true),
    array('action' => 'update', 'method' => 'POST', 'id' => true)
));
*/

/**
 * Logging configurations
 * 
 */
CakeLog::config('debug', array(
	'engine' => 'FileLog',
	'mask' => 0666,
	'size' => 0, // disable file log rotation, handled by logrotate
	'types' => array('notice', 'info', 'debug'),
	'file' => 'debug',
));
CakeLog::config('error', array(
	'engine' => 'FileLog',
	'mask' => 0666,
	'size' => 0, // disable file log rotation, handled by logrotate
	'types' => array('warning', 'error', 'critical', 'alert', 'emergency'),
	'file' => 'error',
));

// for logging information from models
CakeLog::config('model', array(
	'engine' => 'FileLog',
	'mask' => 0666,
	'size' => 0, // disable file log rotation, handled by logrotate
	'types' => array('info', 'notice', 'error', 'warning', 'debug'),
	'scopes' => array('model'),
	'file' => 'models.log',
));

// for logging information from models
CakeLog::config('shell', array(
	'engine' => 'FileLog',
	'mask' => 0666,
	'size' => 0, // disable file log rotation, handled by logrotate
	'types' => array('info', 'notice', 'error', 'warning', 'debug'),
	'scopes' => array('shell'),
	'file' => 'shell.log',
));

// for logging information from controllers
CakeLog::config('controller', array(
	'engine' => 'FileLog',
	'mask' => 0666,
	'size' => 0, // disable file log rotation, handled by logrotate
	'types' => array('info', 'notice', 'error', 'warning', 'debug'),
	'scopes' => array('controller'),
	'file' => 'controllers.log',
));

// for logging information from cache
CakeLog::config('cache', array(
	'engine' => 'FileLog',
	'mask' => 0666,
	'size' => 0, // disable file log rotation, handled by logrotate
	'types' => array('info', 'notice', 'error', 'warning', 'debug'),
	'scopes' => array('cache'),
	'file' => 'cache.log',
));
CakeLog::config('grid_edit', array(
	'engine' => 'FileLog',
	'mask' => 0666,
	'size' => 0, // disable file log rotation, handled by logrotate
	'types' => array('info', 'notice', 'error', 'warning', 'debug'),
	'scopes' => array('grid_edit'),
	'file' => 'grid_edit.log',
));

// log all post requests
// this is done in the beforeFilter in Utilities.CommonAppController.php
CakeLog::config('post', array(
	'engine' => 'FileLog',
	'mask' => 0666,
	'size' => 0, // disable file log rotation, handled by logrotate
	'types' => array('info', 'notice', 'error', 'warning', 'debug'),
	'scopes' => array('post'),
	'file' => 'post.log',
));
 

$route_prefixes = Configure::read('Routing.prefixes');
// the regular dashboard user
// they can see the global/normal dashboards
$route_prefixes[] = 'db_user';
$route_prefixes[] = 'api';
$route_prefixes = array_flip($route_prefixes);
$route_prefixes = array_flip($route_prefixes);
Configure::write('Routing.prefixes', $route_prefixes);

/***
 *
 * Cache settings for the utilities plugin
 *
 */
// Default Cache
Cache::config('default', array(
	'engine' => 'File',
	'duration'=> 3600,
	'prefix' => 'default_',
	'mask' => 0666,
));

// cache for database lookups (selects)
Cache::config('database', array(
	'engine' => 'File',
	'duration'=> 600,
	'prefix' => 'db_',
	'mask' => 0666,
));

// Explicit file caching
Cache::config('file', array(
	'engine' => 'File',
	'duration'=> 2628000, // one month on average
	'prefix' => 'file_',
	'mask' => 0666,
));

// Cache User Information
Cache::config('users', array(
	'engine' => 'File',
	'duration'=> 2628000, // one month on average
	'prefix' => 'users_',
	'mask' => 0666,
));

// cache's external information
// right now, used in the extractorBehavior 
Cache::config('external', array(
	'engine' => 'File',
	'duration'=> 2628000, // one month on average
	'prefix' => 'external_',
	'mask' => 0666,
));
Cache::config('sessions', array(
//	'engine' => 'Utilities.CrFile',
	'engine' => 'Memcache',
    'mask' => 0666,
    'duration' => 1800, // half hour
    //'path' => CACHE,
    'prefix' => 'sessions'
));

/// used to cache the content from the external sources
// incase a run away nslookup happens, we're not blasting our sources
Cache::config('nslookup', array(
//	'engine' => 'Utilities.CrFile',
	'engine' => 'Memcache',
    'mask' => 0666,
    'duration' => 600, // 10 minutes
    //'path' => CACHE,
    'prefix' => 'nslookup_'
));

Cache::config('nslookup_long', array(
//	'engine' => 'Utilities.CrFile',
	'engine' => 'Memcache',
    'mask' => 0666,
    'duration' => 604800, // 1 WEEK
    //'path' => CACHE,
    'prefix' => 'nslookup_'
));

Cache::config('dnsdbapi', array(
//	'engine' => 'Utilities.CrFile',
	'engine' => 'Memcache',
    'mask' => 0666,
    'duration' => 600,
    //'path' => CACHE,
    'prefix' => 'dnsdbapi_'
));

// mainly used to keeping track of the dnsdbapi key counts
Cache::config('dnsdbapi_long', array(
//	'engine' => 'Utilities.CrFile',
	'engine' => 'Memcache',
    'mask' => 0666,
    'duration' => 604800, // 1 WEEK
    //'path' => CACHE,
    'prefix' => 'dnsdbapi_'
));

Cache::config('virustotal', array(
//	'engine' => 'Utilities.CrFile',
	'engine' => 'Memcache',
    'mask' => 0666,
    'duration' => 86400, // 1 day
    //'path' => CACHE,
    'prefix' => 'virustotal'
));

// mainly used to keeping track of the dnsdbapi key counts
Cache::config('virustotal_long', array(
//	'engine' => 'Utilities.CrFile',
	'engine' => 'Memcache',
    'mask' => 0666,
    'duration' => 604800, // 1 WEEK
    //'path' => CACHE,
    'prefix' => 'virustotal_long'
));

// same as nslookup, only for whois
Cache::config('whois', array(
//	'engine' => 'Utilities.CrFile',
	'engine' => 'Memcache',
    'mask' => 0666,
    'duration' => 600,
    //'path' => CACHE,
    'prefix' => 'whois_'
));

Cache::config('whoiser', array(
//	'engine' => 'Utilities.CrFile',
	'engine' => 'Memcache',
    'mask' => 0666,
    'duration' => 604800, // 1 WEEK
    //'path' => CACHE,
    'prefix' => 'whoiser_'
));

Cache::config('source_stats', array(
//	'engine' => 'Utilities.CrFile',
	'engine' => 'Memcache',
    'mask' => 0666,
    'duration' => 604800, // 1 WEEK
    //'path' => CACHE,
    'prefix' => 'source_stats_'
));

// Used for queries that look like they run once, 
// but actually can run multiple times due to ajax requests
// see Utilities.CommonBehavior::typeFormList();
Cache::config('CachedCounts', array(
//	'engine' => 'Utilities.CrFile',
//	'engine' => 'File',
	'engine' => 'Memcache',
    'mask' => 0666,
	'duration'=> 2628000, // one month on average
    //'path' => CACHE,
    'path' => CACHE.'CachedCounts',
    'prefix' => 'CachedCounts',
    'groups' => array(),
));

// logging database updates
CakeLog::config('queries', array(
	'engine' => 'FileLog',
	'mask' => 0666,
	'size' => 0, // disable file log rotation, handled by logrotate
	'types' => array('info', 'notice', 'error', 'warning', 'debug'),
	'scopes' => array('queries'),
	'file' => 'queries.log',
));

// logging metrics
CakeLog::config('metrics', array(
	'engine' => 'FileLog',
	'mask' => 0666,
	'size' => 0, // disable file log rotation, handled by logrotate
	'types' => array('info', 'notice', 'error', 'warning', 'debug'),
	'scopes' => array('metrics'),
	'file' => 'metrics.log',
));

// logging failed validation related info
CakeLog::config('validations', array(
	'engine' => 'FileLog',
	'mask' => 0666,
	'size' => 0, // disable file log rotation, handled by logrotate
	'types' => array('info', 'notice', 'error', 'warning', 'debug'),
	'scopes' => array('validations'),
	'file' => 'validations.log',
));

// for looking information from nslookups
CakeLog::config('nslookup', array(
	'engine' => 'FileLog',
	'mask' => 0666,
	'size' => 0, // disable file log rotation, handled by logrotate
	'types' => array('info', 'notice', 'error', 'warning', 'debug'),
	'scopes' => array('nslookup'),
	'file' => 'nslookups.log',
));

// for looking information from dnsdbapi
CakeLog::config('dnsdbapi', array(
	'engine' => 'FileLog',
	'mask' => 0666,
	'size' => 0, // disable file log rotation, handled by logrotate
	'types' => array('info', 'notice', 'error', 'warning', 'debug'),
	'scopes' => array('dnsdbapi'),
	'file' => 'dnsdbapi.log',
));

// for looking information from dnsdbapi
CakeLog::config('virustotal', array(
	'engine' => 'FileLog',
	'mask' => 0666,
	'size' => 0, // disable file log rotation, handled by logrotate
	'types' => array('info', 'notice', 'error', 'warning', 'debug'),
	'scopes' => array('virustotal'),
	'file' => 'virustotal.log',
));

// for looking information from dnsdbapi
CakeLog::config('passivetotal', array(
	'engine' => 'FileLog',
	'mask' => 0666,
	'size' => 0, // disable file log rotation, handled by logrotate
	'types' => array('info', 'notice', 'error', 'warning', 'debug'),
	'scopes' => array('passivetotal'),
	'file' => 'passivetotal.log',
));

// for looking information from whois
CakeLog::config('whois', array(
	'engine' => 'FileLog',
	'mask' => 0666,
	'size' => 0, // disable file log rotation, handled by logrotate
	'types' => array('info', 'error', 'warning', 'debug'),
	'scopes' => array('whois'),
	'file' => 'whois.log',
));

// log any posts that are invalidated
CakeLog::config('invalidations', array(
	'engine' => 'FileLog',
	'mask' => 0666,
	'size' => 0, // disable file log rotation, handled by logrotate
	'types' => array('info', 'notice', 'error', 'warning', 'debug'),
	'scopes' => array('invalidations'),
	'file' => 'invalidations.log',
));

// log any posts that are invalidated
CakeLog::config('source_stats', array(
	'engine' => 'FileLog',
	'mask' => 0666,
	'size' => 0, // disable file log rotation, handled by logrotate
	'types' => array('info', 'notice', 'error', 'warning', 'debug'),
	'scopes' => array('invalidations'),
	'file' => 'source_stats.log',
));

Configure::write('Nslookup.parser_settings', array(
	'dnsdbapi' => array(
		'disabled' => true, // leave here until we have an actual contract with them, until than, this is a seperate process
		'keys' => array(
			'', // ours
		),
		'limits' => array(
			'day' => 490,
		),
		'key' => false, // keep this as false!!!
		'false_limit' => 490, // the limit that we want to set.
		'host' => 'https://api.dnsdb.info',
		'path_rdata_ip' => '/lookup/rdata/ip/',
		'path_rrset_ip' => '/lookup/rrset/ip/',
		'json_key_ip' => 'rrname',
		'path_rdata_host' => '/lookup/rdata/name/',
		'path_rrset_host' => '/lookup/rrset/name/',
		'json_key_host' => 'rdata',
	),
	'domaintools_dns' => array(
//		'disabled' => true,
		'url_base' => 'https://api.domaintools.com/v1/',
		'uri_postfix_hostname' => '/reverse-ip/',
		'uri_postfix_ipaddress' => '/host-domains/',
		'api_username' => '',
		'api_key' => '',
		'username' => '',
		'password' => '',
		'account_url_base' => 'https://secure.domaintools.com/',
		'account_url_login' => 'log-in/',
		'account_url_summary' => 'my-account/',
		'account_url_api' => 'api/dashboard/',
	),
	'domaintools_dns_hist' => array(
//		'disabled' => true,
		'url_base' => 'https://api.domaintools.com/v1/',
		'uri_postfix' => '/hosting-history/',
		'api_username' => '',
		'api_key' => '',
	),
	'zoneedit' => array(
		'disabled' => true,
		'url_base' => 'https://www.zoneedit.com/proxy.php',
		'hostname_query' => array(
			'endpoint' => 'Util.action',
			'action' => 'doNSLookup',
			'type' => 'A',
			'server' => '',
			'host' => false,
		),
		'ipaddress_query' => array(
			'endpoint' => 'Util.action',
			'action' => 'doNSLookup',
			'ipaddress' => false
		),
		'curl_options' => array(
			'timeout' => 40,
			'connectTimeout' => 40,
			'sslVerifyPeer' => false,
			'sslVerifyHost' => 0,
		),
	),
	'networktools' => array(
	/*
	 * Disabled, they don't have an api, and now they instituted captcha
	 * still leaving code here as a reminder to check again later
	 */
		'disabled' => true,
		'url_base' => 'http://network-tools.com/default.asp',
		'host_query' => array(
			'host' => false, 
			'prog' => false,
		),
	),
	'namespace' => array(
	/*
	 * So, apparently they are having issues with ICANN, and can't be reached.
	 * https://www.icann.org/en/news/litigation/namespace-v-icann
	 */
		'disabled' => true,
		'url_base' => 'https://name.space.xs2.net/cgi-bin/nslookup.pl',
		'host_query' => array(
			'nsinput' => false, 
		),
	),
	'webmaster_toolkit' => array(
	/*
	 * Disabled because:
	 * 1. their https goes to a different site for vitamins or something
	 * 2. they don't accept posts
	 */
		'disabled' => true,
		'url_base' => 'http://www.webmaster-toolkit.com/dns-query.shtml',
		'host_query' => array(
			'address' => false, 
		),
	),
));

Configure::write('VirusTotal.settings', array(
	'apikey' => '', 
	'username' => '',
	'password' => '',
	'limits' => array(
		'minute' => 4,
		'hour' => 240,
		'day' => 990,
		'month' => 178560,
	),
//	'limit_type' => 'day',
	'limit_type' => 'minute', // for dev/public keys
	'save_raw' => true,
	'lookup_types' => array(
		'hostname_dnslookup' => array(
			'uri' => 'https://www.virustotal.com/vtapi/v2/domain/report',
			'query' => 'domain',
			'raw_prefix' => 'hostname_report',
			'apikey' => '',
			'limits' => array(
				'minute' => 4,
				'hour' => 240,
			),
		),
		'ipaddress_dnslookup' => array(
			'uri' => 'https://www.virustotal.com/vtapi/v2/ip-address/report',
			'query' => 'ip',
			'raw_prefix' => 'ipaddress_report',
			'apikey' => '',
			'limits' => array(
				'minute' => 4,
				'hour' => 240,
			),
		),
		'hostname_report' => array(
			'uri' => 'https://www.virustotal.com/vtapi/v2/domain/report',
			'query' => 'domain',
			'raw_prefix' => 'hostname_report',
			'apikey' => '',
			'limits' => array(
				'day' => 990,
			),
		),
		'ipaddress_report' => array(
			'uri' => 'https://www.virustotal.com/vtapi/v2/ip-address/report',
			'query' => 'ip',
			'raw_prefix' => 'ipaddress_report',
			'apikey' => '',
			'limits' => array(
				'day' => 990,
			),
		),
		'file_behaviour' => array(
			'uri' => 'https://www.virustotal.com/vtapi/v2/file/behaviour',
			'query' => 'hash',
			'raw_prefix' => 'file_behaviour',
			'apikey' => '',
			'limits' => array(
				'day' => 990,
			),
		),
	),
));

Configure::write('PassiveTotal.settings', array(
	'apikey' => '',
	'uri' => 'https://www.passivetotal.org/api/passive',
	'username' => '',
	'password' => '',
));
Configure::write('Hexillion.settings', array(
//	'disabled' => true,
	'url_base_auth' => 'https://hexillion.com/rf/xml/1.0/auth/',
	'auth_query' => array(
		'username' => '',
		'password' => '',
	),
	'url_base' => 'https://hexillion.com/co/DomainDossier.aspx',
	'host_query' => array(
		'addr' => false, 
		'dom_dns' => 'true', 
		'sessionkey' => false,
	),
	'account_url' => 'https://hexillion.com/co/accounts',
	'account_query' => array(
		'sessionkey' => false,
	),
	'balance_threshold' => 1000,
));

// For account balances
Configure::write('Whois.account_settings', array(
	'domaintools' => array(
//		'disabled' => true,
		'url_base' => 'https://api.domaintools.com/v1/',
		'uri_whois' => '/reverse-whois/?terms=',
		'api_username' => '',
		'api_key' => '',
		'username' => '',
		'password' => '',
	),
	'whoisxmlapi' => array(
		// Info found at: https://www.whoisxmlapi.com/whois-api-doc.php
//		'disabled' => true,
		'username' => '',
		'password' => '',
		'url_base' => 'https://www.whoisxmlapi.com/accountServices.php',
		'query' => array(
			'servicetype' => 'accountbalance',
			'username' => '',
			'password' => '',
		),
	),
));

// For regular whois
Configure::write('Whois.parser_settings', array(
	'domaintools_whois' => array(
//		'disabled' => true,
		'url_base' => 'https://api.domaintools.com/v1/',
		'uri_whois' => '/whois/',
		'api_username' => '',
		'api_key' => '',
	),
	'domaintools_whois_history' => array(
		'disabled' => true,
		'url_base' => 'https://api.domaintools.com/v1/',
		'uri_whois' => '/whois/history/',
		'api_username' => '',
		'api_key' => '',
	),
	'domaintools_reverse_whois' => array(
		'disabled' => true,
		'url_base' => 'https://api.domaintools.com/v1/',
		'uri_whois' => '/reverse-whois/?terms=',
		'api_username' => '',
		'api_key' => '',
	),
	'whoisxmlapi' => array(
//		'disabled' => true,
		'username' => '',
		'password' => '',
		'url_base' => 'https://www.whoisxmlapi.com/whoisserver/WhoisService',
	),
));

// For reverse whois
Configure::write('Whois.reverse_parser_settings', array(
	'domaintools_reverse_whois' => array(
		'disabled' => true,
		'url_base' => 'https://api.domaintools.com/v1/',
		'uri_whois' => '/reverse-whois/?terms=',
		'api_username' => '',
		'api_key' => '',
	),
	'whoisxmlapi_reverse' => array(
		'disabled' => true,
		'username' => '',
		'password' => '',
		'url_base' => 'https://www.whoisxmlapi.com/whoisserver/WhoisService',
	),
));

$app_config = (Configure::read('Whoiser')?Configure::read('Whoiser'):array());

// Whoiser connection settings
Configure::write('Whoiser.settings', array_merge(array(
	'email' => '',
	'api_key' => '',
	'search_url' => 'https://whoiser/whoiser/api/searches/search.json',
	'status_url' => 'https://whoiser/whoiser/api/searches/status/%s.json',
	'recompile_url' => 'https://whoiser/whoiser/api/searches/recompile/%s.json',
	'details_url' => 'https://whoiser/whoiser/api/searches/details/%s.json',
), $app_config));

/***
 * In the app config now
Configure::write('Proctime', array(
	'threshold' => 0.5, // seconds before we log a slow process time
));
*/
if(!Configure::read('Proctime.threshold'))
{
	Configure::write('Proctime.threshold', 0.5); // seconds before we log a slow process time
}

/**
 * Added functions to be used throughout the application
 *
 */
if( !function_exists('random_string') )
{
	function random_string($l = 10)
	{
		$s = '';
		$c = "abcdefghijklmnopqrstuvwxwz0123456789";
		for(;$l > 0;$l--) $s .= $c{rand(0,strlen(($c-1)))};
		return str_shuffle($s);
	}
}
if( !function_exists('sortByLength') )
{
	function sortByLength($a,$b)
	{
		if($a == $b) return 0;
		return (strlen($a) > strlen($b) ? -1 : 1);
	}
}
if( !function_exists('lensort') )
{
	function lensort($a,$b)
	{
		return strlen($b)-strlen($a);
	}
}
if( !function_exists('GetCallingMethodName') )
{
	function GetCallingMethodName($back = 0)
	{
		$e = new Exception();
		$trace = $e->getTrace();
		//position 0 would be the line that called this function so we ignore it
		$pos = 2 + $back;
		while(!isset($trace[$pos]))
		{
			$pos--;
		}
		$last_call = $trace[$pos];
		$pos--;
		$last_file = $trace[$pos];
		
		$out = '';
		$out .= (isset($last_file['file'])?basename($last_file['file']):'').
				(isset($last_file['line'])?'('.$last_file['line'].') ':'').
				(isset($last_call['class'])?$last_call['class']:$last_call['file']).
				'->'.
				(isset($last_call['function'])?$last_call['function']:'NULL');
		
		return $out;
	}
}
if( !function_exists('loadDbConfigs') )
{
	// finds all database configs and returns them
	function loadDbConfigs(&$dbConfigObject)
	{
		$configs = array();
		foreach(CakePlugin::loaded() as $pluginName)
		{
			$pluginPath = CakePlugin::path($pluginName). 'Config'. DS. 'database.php';
			if(!is_readable($pluginPath))
				continue;
			include_once($pluginPath);
			
			$pluginClass = strtoupper($pluginName). '_DATABASE_CONFIG';
			if(!class_exists($pluginClass))
				continue;
			
			$pluginObject = new $pluginClass;
			if(isset($pluginObject->default))
			{
				$pluginVariable = 'plugin_'. strtolower($pluginName);
				$dbConfigObject->{$pluginVariable} = $pluginObject->default;
			}
			if(isset($pluginObject->test))
			{
				$pluginVariable = 'plugin_'. strtolower($pluginName). '_test';
				$dbConfigObject->{$pluginVariable} = $pluginObject->test;
			}
			
			foreach(get_object_vars($pluginObject) as $connectionName => $connectionSettings)
			{
				if($connectionName == 'default')
				{
					$pluginVariable = 'plugin_'. strtolower($pluginName);
					$dbConfigObject->{$pluginVariable} = $pluginObject->{$connectionName};
				}
				elseif($connectionName == 'test')
				{
					$pluginVariable = 'plugin_'. strtolower($pluginName). '_test';
					$dbConfigObject->{$pluginVariable} = $pluginObject->{$connectionName};
				}
				else
				{
					$pluginVariable = 'plugin_'. strtolower($pluginName). '_'. $connectionName;
					$dbConfigObject->{$pluginVariable} = $pluginObject->{$connectionName};
				}
			}
		}
	}
}

/**
 *
 * Load other plugins that the Utilities plugin needs
 *
*/
CakePlugin::load('Contacts', array('bootstrap' => true));

// handles taking snapshots of the mysql database
CakePlugin::load('Snapshot', array('bootstrap' => true));

// used to log any save transactions
CakePlugin::load('Dblogger');

// Documents and Form manager
CakePlugin::load('Docs');

// Load the Filter plugin
CakePlugin::load('Filter');

CakePlugin::load('OAuthClient', array('bootstrap' => true, 'routes' => true));

// Load the search plugin
CakePlugin::load('Queue');

// Load the search plugin
CakePlugin::load('Search');

// Load the ssdeep plugin
CakePlugin::load('Ssdeep');

// Load the tags plugin
CakePlugin::load('Tags');

// used to manage the avatars for users
CakePlugin::load('Upload');

// tracks usage stats
CakePlugin::load('Usage', array('bootstrap' => true));

// for better debugging
//CakePlugin::load('DebugKit');
