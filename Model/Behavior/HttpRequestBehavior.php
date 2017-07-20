<?php
/* 
 * Used to hold common functions across all apps
 */

App::uses('Hash', 'Core');
App::uses('CakeEmail', 'Network/Email');
App::uses('CookieComponent', 'Controller/Component');
class HttpRequestBehavior extends ModelBehavior 
{
	public $settings = [];
	
	private $_defaults = [];
	
	public $Model = null;
	
	public $Curl = null;
	
	public $curlError = null;
	public $curlErrno = null;
	public $curlOptions = [];
	
	public $requestHeaders = [];
	public $responseHeaders = [];
	
	public $query = [];
	
	public $method = 'GET';
	
	private $methods = ['GET', 'POST', 'HEAD', 'PUT', 'DELETE', 'OPTIONS', 'CONNECT'];
	
	public $uri = null;
	
	public function setup(Model $Model, $settings = [])
	{
		$this->settings[$Model->alias] = array_merge($this->_defaults, $settings);
		
		$this->Model = & $Model;
		$Model->modelError = false;
	}
	
	public function HTTP_reset(Model $Model)
	{
		$Model->modelError = false;
		$this->curlError = null;
		$this->curlErrno = null;
		$this->curlOptions = [];
		$this->requestHeaders = [];
		$this->responseHeaders = [];
		$this->query = [];
		$this->uri = null;
	}
	
	public function HTTP_setMethod(Model $Model, $method = null)
	{
		if(!$method)
		{
			$Model->modelError = __('No Method defined');
			return false;
		}
		
		if(!in_array($method, $this->methods))
		{
			$Model->modelError = __('Invalid Method');
			return false;
		}
		
		$this->method = $method;
		return true;
	}
	
	public function HTTP_getMethod(Model $Model)
	{
		return $this->method;
	}
	
	public function HTTP_setCurlOption(Model $Model, $k = null, $v = null)
	{
		if($k)
			$this->curlOptions[$k] = $v;
	}
	
	public function HTTP_getCurlOptions(Model $Model, $k = null)
	{
		if($k)
		{
			if(isset($this->curlOptions[$k]))
				return $this->curlOptions[$k];
			return false;
		}
		return $this->curlOptions;
	}
	
	public function HTTP_setQuery(Model $Model, $k = null, $v = null)
	{
		if($k)
			$this->query[$k] = $v;
	}
	
	public function HTTP_getQuery(Model $Model, $k = null)
	{
		if($k)
		{
			if(isset($this->query[$k]))
				return $this->query[$k];
			return false;
		}
		return $this->query;
	}
	
	public function HTTP_setRequestHeader(Model $Model, $k = null, $v = null)
	{
		if($k)
			$this->requestHeaders[$k] = $v;
	}
	
	public function HTTP_getRequestHeaders(Model $Model, $k = null)
	{
		if($k)
		{
			if(isset($this->requestHeaders[$k]))
				return $this->requestHeaders[$k];
			return false;
		}
		return $this->requestHeaders;
	}
	
	public function HTTP_getResponseHeaders(Model $Model, $k = null)
	{
		if($k)
		{
			if(isset($this->responseHeaders[$k]))
				return $this->responseHeaders[$k];
			return false;
		}
		return $this->responseHeaders;
	}
	
	public function HTTP_setUri(Model $Model, $uri = null)
	{
		if(!$uri)
		{
			$Model->modelError = __('No URI defined');
			return false;
		}
		
		$this->uri = $uri;
		return true;
	}
	
	public function HTTP_getUri(Model $Model)
	{
		return $this->uri;
	}
	
	public function HTTP_setError(Model $Model, $no = null, $msg = null)
	{
		$this->curlErrno = $no;
		$this->curlError = $msg;
		return true;
	}
	
	public function HTTP_getError(Model $Model)
	{
		if(!$this->curlErrno)
			return false;
		
		return [
			'errno' => $this->curlErrno,
			'msg' => $this->curlError,
		];
	}
	
	public function HTTP_execute(Model $Model)
	{
		if(!$this->Curl)
		{
			App::import('Vendor', 'Utilities.Curl');
			$this->Curl = new Curl();
		}
		
		$url = $this->HTTP_getUri($Model);
		
		$query_url = '';
		$query = $this->HTTP_getQuery($Model);
		
		if(is_array($query) and !empty($query))
		{
			$query_url = [];
			foreach($query as $k => $v)
			{
				$query_url[] = $k. '='. $v;
			}
			$query_url = '?'. implode('&', $query_url);
		}
		
		$headers = $this->HTTP_getRequestHeaders($Model);
		if(is_array($headers) and !empty($headers))
		{
			$this->Curl->httpHeader = $headers;
		}
		
		$method = $this->HTTP_getMethod($Model);
		if($method == 'POST')
		{
			$this->Curl->post = true;
			$this->Curl->postFieldsArray = $query;
			$this->Curl->url = $url;
		}
		else
		{
			$url .= $query_url;
			$this->Curl->url = $url;
			
			if($method != 'GET')
			{
				$this->HTTP_setCurlOption($Model, 'customRequest', $method);
			}
		}
		
		$curl_options_default = [
			'followLocation' => true,
			'maxRedirs' => 5,
			'timeout' => 0,
			'connectTimeout' => 300,
			'cookieFile' => CACHE. 'http_cookieFile',
			'cookieJar' => CACHE. 'http_cookieJar',
			'header' => true,
			'sslVerifyHost' => 0,
			'sslVerifyPeer' => false,
			'userAgent' => 'HttpRequestBehavior',
		];
		
		$curl_options = array_merge($curl_options_default, $this->HTTP_getCurlOptions($Model));
		
		foreach($curl_options as $k => $v)
		{
			$this->Curl->{$k} = $v;
		}

		$data = $this->Curl->execute();
		
		if($this->Curl->response_headers)
		{
			$this->responseHeaders = $this->Curl->response_headers;
		}
			
		if($this->Curl->error)
		{
			$this->HTTP_setError($Model, $this->Curl->errno, $this->Curl->error);
			return false;
		}
		
		return $data;
	}
}