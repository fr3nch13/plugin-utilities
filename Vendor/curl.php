<?php
/**
 * cURL Model 0.1
 *
 * (c) 2007 James Hall
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy,
 * modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software
 * is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR
 * IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * Usage:
 *
 * $this->Curl->url = 'google.com';
 * $this->Curl->post = true; // Set options like this, for a list of new names, see the array below organised by type
 * $this->Curl->postFieldsArray = array('field1' => 'value1', 'field2' => 'value2'); // This urlencode post data for you
 * $this->Curl->followLocation = true; // Make sure you use a boolean here, my class will do type checking
 * $this->Curl->userAgent = 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.8.1.1) Gecko/20060601 Firefox/2.0.0.1 (Ubuntu-edgy)';
 * $this->Curl->execute(); // execute() returns the output instead of writing it straight to the page like normal cURL
 * $this->Curl->grab('<p>', '</p>'); // Matches the first set of these, and returns whats in between
 * 
 **/

if(!function_exists('curl_init'))
{
  die('
		cURL is not installed.<br />
		<b>Linux (Ubuntu) Users:</b><pre>sudo apt-get install php5-curl</pre>
		(or use your favourite package manager) then restart Apache:
		<pre>sudo /etc/init.d/apache2 restart</pre>
		<b>Win32 Users:</b>  In order to enable this module on a Windows environment, libeay32.dll and ssleay32.dll  must be present in your PATH.<br />'
	 );
}


class Curl
{
  var $name = 'Curl';
  var $handle; // Curl handle
  var $output = null; // Output from curl_exec
  var $return = null; // Return value for curl_exec
  var $error = null; // Holds the value of an error from curl_exec
  var $errno = null; // Holds the value of an error number from curl_exec
  var $response_headers = null; // Holds the value of an error number from curl_exec
  var $curl_info = array();
  var $options =
	array
	(
	  'bool' => array
	  (
		'autoReferer' => CURLOPT_AUTOREFERER,
		'binaryTransfer' => CURLOPT_BINARYTRANSFER,
		'cookieSession' => CURLOPT_COOKIESESSION,
		'crlf' => CURLOPT_CRLF,
		'dnsUseGlobalCache' => CURLOPT_DNS_USE_GLOBAL_CACHE,
		'failOnError' => CURLOPT_FAILONERROR,
		'fileModifiedTime' => CURLOPT_FILETIME,
		'followLocation' => CURLOPT_FOLLOWLOCATION,
		'forbidReuse' => CURLOPT_FORBID_REUSE,
		'freshConnect' => CURLOPT_FRESH_CONNECT,
		'ftpUseEprt' => CURLOPT_FTP_USE_EPRT,
		'ftpUseEpsv' => CURLOPT_FTP_USE_EPSV,
		'ftpAppend' => CURLOPT_FTPAPPEND,
//		'ftpAscii' => CURLOPT_FTPASCII,
		'ftpListOnly' => CURLOPT_FTPLISTONLY,
		'header' => CURLOPT_HEADER,
		'headerOut' => CURLINFO_HEADER_OUT,
		'httpGet' => CURLOPT_HTTPGET,
		'httpProxyTunnel' => CURLOPT_HTTPPROXYTUNNEL,
//		'mute' => CURLOPT_MUTE,
		'netRc' => CURLOPT_NETRC,
		'nobody' => CURLOPT_NOBODY,
		'noProgress' => CURLOPT_NOPROGRESS,
		'noSignal' => CURLOPT_NOSIGNAL,
		'post' => CURLOPT_POST,
		'put' => CURLOPT_PUT,
		'returnTransfer' => CURLOPT_RETURNTRANSFER,
		'sslVerifyPeer' => CURLOPT_SSL_VERIFYPEER,
		'transferText' => CURLOPT_TRANSFERTEXT,
		'unrestrictedAuth' => CURLOPT_UNRESTRICTED_AUTH,
		'upload' => CURLOPT_UPLOAD,
		'verbose' => CURLOPT_VERBOSE,
	  ),
	  
	  'int' => array
	  (
		'bufferSize' => CURLOPT_BUFFERSIZE,
		'closePolicy' => CURLOPT_CLOSEPOLICY,	
		'connectTimeout' => CURLOPT_CONNECTTIMEOUT,	
		'dnsCacheTimeout' => CURLOPT_DNS_CACHE_TIMEOUT,	
		'httpVersion' => CURLOPT_HTTP_VERSION,
		'httpAuth' => CURLOPT_HTTPAUTH,
		'inFileSize' => CURLOPT_INFILESIZE,
		'lowSpeedLimit' => CURLOPT_LOW_SPEED_LIMIT,
		'lowSpeedTime' => CURLOPT_LOW_SPEED_TIME,
		'maxConnects' => CURLOPT_MAXCONNECTS,
		'maxRedirs' => CURLOPT_MAXREDIRS,
		'port' => CURLOPT_PORT,
		'proxyAuth' => CURLOPT_PROXYAUTH,
		'proxyPort' => CURLOPT_PROXYPORT,
		'proxyType' => CURLOPT_PROXYTYPE,
		'resumeFrom' => CURLOPT_RESUME_FROM,
		'sslVerifyHost' => CURLOPT_SSL_VERIFYHOST,
		'sslVersion' => CURLOPT_SSLVERSION,
		'timeCondition' => CURLOPT_TIMECONDITION,
		'timeout' => CURLOPT_TIMEOUT,
		'timeValue' => CURLOPT_TIMEVALUE,
	  ),
	  
	  'string' => array
	  (
		'caInfo' => CURLOPT_CAINFO,
		'caPath' => CURLOPT_CAPATH,
		'cookie' => CURLOPT_COOKIE,
		'cookieFile' => CURLOPT_COOKIEFILE,
		'cookieJar' => CURLOPT_COOKIEJAR,
		'customRequest' => CURLOPT_CUSTOMREQUEST,
		'encoding' => CURLOPT_ENCODING,
		'ftpPort' => CURLOPT_FTPPORT,
		'interface' => CURLOPT_INTERFACE,
		'kerberosLevel' => CURLOPT_KRB4LEVEL,
		'krb4Level' => CURLOPT_KRB4LEVEL,
		'postFields' => CURLOPT_POSTFIELDS,
		'proxy' => CURLOPT_PROXY,
		'proxyUserPwd' => CURLOPT_PROXYUSERPWD,
		'randomFile' => CURLOPT_RANDOM_FILE,
		'range' => CURLOPT_RANGE,
		'referer' => CURLOPT_REFERER,
		'sslCipherList' => CURLOPT_SSL_CIPHER_LIST,
		'sslCertificate' => CURLOPT_SSLCERT,
		'sslCertificatePassword' => CURLOPT_SSLCERTPASSWD,
		'sslCertificateType' => CURLOPT_SSLCERTTYPE,
		'sslEngine' => CURLOPT_SSLENGINE,
		'sslEngineDefault' => CURLOPT_SSLENGINE_DEFAULT,
		'sslKey' => CURLOPT_SSLKEY,
		'sslKeyPassword' => CURLOPT_SSLKEYPASSWD,
		'sslKeyType' => CURLOPT_SSLKEYTYPE,
		'url' => CURLOPT_URL,
		'userAgent' => CURLOPT_USERAGENT,
		'userPwd' => CURLOPT_USERPWD
	  ),
	  
	  'array' => array
	  (
		'postFieldsArray' => 'postFieldsArray',
		'httpHeader' => CURLOPT_HTTPHEADER,
	  )
	);

  function Curl()
  {
	$this->handle = curl_init();
  }
  
  function getInfo($key = null)
  {
	$array = $this->curl_info;
  	if($key)
  	{
  		$result = (isset($array[$key])?$array[$key]:false);
		return $result;
  	}
  	return $array;
  }
  
  function url()
  {
	return $this->getInfo('url');
  }
  
  function contentType()
  {
	return $this->getInfo('content_type');
  }
  
  function httpCode()
  {
	return $this->getInfo('http_code');
  }
  
  function headerSize()
  {
	return $this->getInfo('header_size');
  }
  
  function requestSize()
  {
	return $this->getInfo('request_size');
  }
  
  function fileTime()
  {
	return $this->getInfo('filetime');
  }
  
  function sslVerifyResult()
  {
	return $this->getInfo('ssl_verify_result');
  }

  function redirectCount()
  {
	return $this->getInfo('redirect_count');
  }

  function totalTime()
  {
	return $this->getInfo('total_time');
  }

  function nameLookupTime()
  {
	return $this->getInfo('namelookup_time');
  }

  function connectTime()
  {
	return $this->getInfo('connect_time');
  }

  function preTransferTime()
  {
	return $this->getInfo('pretransfer_time');
  }

  function sizeUpload()
  {
	return $this->getInfo('size_upload');
  }

  function sizeDownload()
  {
	return $this->getInfo('size_download');
  }

  function speedDownload()
  {
	return $this->getInfo('speed_download');
  }

  function speedUpload()
  {
	return $this->getInfo('speed_upload');
  }

  function downloadContentLength()
  {
	return $this->getInfo('download_content_length');
  }

  function uploadContentLength()
  {
	return $this->getInfo('upload_content_length');
  }

  function startTransferTime()
  {
	return $this->getInfo('starttransfer_time');
  }

  function redirectTime()
  {
	return $this->getInfo('redirect_time');
  }
 
	function _parsePostFieldsArray($array = array())
	{
		$data = array();
		foreach ($array as $key=>$val)
		{
			$data[] = urlencode($key) . '=' . urlencode($val);
		}
		return implode('&', $data);
	}
	
	function _thisHttpHeader($array = array())
	{
		$data = array();
		foreach ($array as $key=>$val)
		{
			$data[] = $key. ': '. $val;
		}
		return $data;
	}
  
	function execute()
	{
		$parse_response_header = false;
		$parse_request_header = false;
		$parse_cookieJar = false;
		foreach($this->options as $type => $options)
		{
			foreach ($options as $key => $val)
			{
				if(isset($this->{$key}))
				{
					$type_check = 'is_' . $type;
					if($type_check($this->{$key}))
					{
						if($key == 'postFieldsArray')
						{
							curl_setopt($this->handle, CURLOPT_POSTFIELDS, $this->_parsePostFieldsArray($this->{$key}));
						}
						elseif($key == 'httpHeader')
						{
							curl_setopt($this->handle, CURLOPT_HTTPHEADER, $this->_thisHttpHeader($this->{$key}));
						}
						elseif($key == 'header')
						{
							$parse_response_header = true;
							curl_setopt($this->handle, $val, $this->{$key});
						}
						elseif($key == 'headerOut')
						{
							$parse_request_header = true;
							curl_setopt($this->handle, $val, $this->{$key});
						}
						elseif($key == 'cookieJar')
						{
							$parse_cookieJar = $this->{$key};
							curl_setopt($this->handle, $val, $this->{$key});
						}
						else
						{
							curl_setopt($this->handle, $val, $this->{$key});
						}
					}
					else
					{
						trigger_error('Expected type \'' . $type . '\' for ' . $key, E_USER_ERROR);
					}
				}
			}
		}
		
		$this->error = null;
	
		ob_start();
		$this->return = curl_exec($this->handle);
		$this->output = ob_get_contents();
		ob_end_clean();
		
		$this->curl_info = curl_getinfo($this->handle);
		
		if($this->return === false)
		{
			$this->error = curl_error($this->handle);
			$this->errno = curl_errno($this->handle);
			
			return false;
		}
		
		if($parse_request_header)
		{
			$request_headers = curl_getinfo($this->handle, CURLINFO_HEADER_OUT );
			$this->request_headers = $this->parseHeaders($request_headers);
		}
		
		if($parse_response_header)
		{
			list($response_headers, $this->output) = explode("\r\n\r\n", $this->output, 2);
			$this->response_headers = $this->parseHeaders($response_headers);
			
			$this->output = trim($this->output);
			
			/// Goddamn proxies
			if (substr($this->output, 0, 4) === 'HTTP')
			{
				$lines = explode("\n", $this->output);
				$headers = array();
				foreach($lines as $i => $line)
				{
					if(!trim($line)) { unset($lines[$i]); break; }
					$headers[] = $line;
					unset($lines[$i]);
				}
				
				$this->output = implode("\n", $lines);
				unset($lines);
				
				if($headers)
				{
					$headers = implode("\n", $headers);
					$this->proxy_headers = $this->response_headers;
					$this->response_headers = $this->parseHeaders($headers);
					unset($headers);
				}
			}
		}
		
		
		return $this->output ;
	}
	
	function close()
	{
		if($this->handle)
		{
			curl_close($this->handle);
		}
	}
  
	function grab($start, $end)
	{
		$startPos = strpos($this->output, $start);
		$endPos = strpos(substr($this->output, $startPos), $end) + strlen($end);
		
		return substr($this->output, $startPos, $endPos);
	}
	
	function grabInside($start, $end)
	{
		$startPos = strpos($this->output, $start) + strlen($start);
		$endPos = strpos(substr($this->output, $startPos), $end);
		
		return substr($this->output, $startPos, $endPos);	
	}
	
	function parseHeaders($headers = array(), $header = null)
	{
		if(is_string($headers))
		{
			$headers = explode("\r\n", $headers);
		}
		
		$output = array();
		
		if ('HTTP' === substr($headers[0], 0, 4))
		{
			list(, $output['status'], $output['status_text']) = explode(' ', $headers[0]);
			unset($headers[0]);
		}
		
		foreach ($headers as $v)
		{
			if(!trim($v)) continue;
			if(preg_match('/^(POST|GET|PUT|DELETE|HEAD|TRACE|CONNECT)/i', $v))
			{
				$h = preg_split('/\s+/', $v);
				$output['method'] = (isset($h[0])?$h[0]:false);
				$output['uri'] = (isset($h[1])?$h[1]:false);
				$output['http-version'] = (isset($h[2])?$h[2]:false);
			}
			elseif($h = preg_split('/:\s*/', $v))
			{
				$hkey = array_shift($h);
				$hvalue = implode(':', $h);
				$output[strtolower($hkey)] = $hvalue;
			}
		}
		
    	if (null !== $header)
    	{
			if (isset($output[strtolower($header)]))
			{
				return $output[strtolower($header)];
			}
        	return;
		}
		
		return $output;
	}
}