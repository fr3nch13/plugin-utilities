<?php

/**
 * Common Network Utility
 * Holds common functions needed for translating different ipv4 stuff
 */

class CommonNetwork 
{
	// convert cidr to netmask
	// e.g. 21 = 255.255.248.0
	public function cidrToNetmask($cidr)
	{
		if(stripos($cidr, '/') !== false)
			list($network, $cidr) = explode('/', $cidr);
		$bin = '';
		for( $i = 1; $i <= 32; $i++ )
			$bin .= $cidr >= $i ? '1' : '0';
		
		$netmask = long2ip(bindec($bin));
		
		if ( $netmask == "0.0.0.0")
			return false;
		
		return $netmask;
	}
	
	// get network address from cidr subnet
	// e.g. 10.0.2.56/21 = 10.0.0.0
	public function cidrToNetwork($network_cidr)
	{
		list($network, $cidr) = explode('/', $network_cidr);
		return long2ip((ip2long($ip)) & ((-1 << (32 - (int)$cidr))));
	}
	
	// get the network's ip range from a cidr
	// e.g. 10.1.10.0/28 = array(10.1.10.0, 10.1.10.3)
	function cidrToRange($network_cidr, $long = false) 
	{
		list($network, $cidr) = explode('/', $network_cidr);
		$range = array();
		$range[0] = long2ip((ip2long($network)) & ((-1 << (32 - (int)$cidr))));
		$range[1] = long2ip((ip2long($network)) + pow(2, (32 - (int)$cidr)) - 1);
		
		if($long)
		{
			$range[0] = $this->convertIpToLong($range[0]);
			$range[1] = $this->convertIpToLong($range[1]);
		}
		
		return $range;
	}
	
	// get the array of ip addresses from a cidr
	// e.g. 10.1.10.0/28 = array(10.1.10.0, 10.1.10.1, 10.1.10.2, 10.1.10.3)
	function cidrToIpArray($network_cidr = false) 
	{
		list($network, $cidr) = explode('/', $network_cidr);
		$ipcount = pow(2, (32 - $cidr));
		$network = long2ip((ip2long($network)) & ((-1 << (32 - (int)$cidr)))); 
		$start = ip2long($network); 
		
		$iparr = array();
		for ($beat = 0; $beat < $ipcount; $beat++) 
		{
			$iparr[$beat] = long2ip($start + $beat);
		}
		return $iparr;
	}
	
	// convert netmask to cidr
	// e.g. 255.255.255.128 = 25
	public function netmaskToCidr($netmask = false)
	{
		$bits = 0;
		$netmask = explode(".", $netmask);
		
		foreach($netmask as $octect)
			$bits += strlen(str_replace("0", "", decbin($octect)));
		
		return $bits;
	}
	
	// is ip in subnet
	// e.g. is 10.5.21.30 in 10.5.16.0/20 == true
	//	  is 192.168.50.2 in 192.168.30.0/23 == false
	public function ipInCidr($ip, $network_cidr)
	{
		list($network, $cidr) = explode('/', $network_cidr);
		if ((ip2long($ip) & ~((1 << (32 - $cidr)) - 1) ) == ip2long($network))
			return true;
		
		return false;
	}
	
	public function netmaskToArray($ipaddress = false, $netmask = false)
	{
		if(!$ipaddress)
			return [];
		if(!$netmask)
			return [];
		
		$netmask = trim($netmask);
		$cidr = $this->netmaskToCidr($netmask);
		return $this->cidrToIpArray($ipaddress.'/'.$cidr);
  	}
	
	// Use these two functions to convert from and to numbers compatible to MySQLs INET_ATON and INET_NTOA
	// http://php.net/manual/en/function.long2ip.php#107257
    function convertIpToString($ip)
    {
        $long = 4294967295 - ($ip - 1);
        return long2ip(-$long);
    }
    
    function convertIpToLong($ip)
    {
        return sprintf("%u", ip2long($ip));
    }
}