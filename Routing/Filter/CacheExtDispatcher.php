<?php

App::uses('CacheDispatcher', 'Routing/Filter');

class CacheExtDispatcher extends CacheDispatcher 
{
	public $request = false;
	public $response = false;
	public function beforeDispatch(CakeEvent $event) 
	{
		$filename = false;
		$this->request = &$event->data['request'];
		
		// needs a recache, so return
		if($this->request->query('recache'))
		{
			return;
		}
		
		// get rid of some stuff if we have to cache/recache
		if(isset($this->request->query['tab']))
			unset($this->request->query['tab']);
		if(isset($this->request->query['key']))
			unset($this->request->query['key']);
		
		$path = $this->request->here();
		if ($path === '/') {
			$path = 'home';
		}
		$prefix = Configure::read('Cache.viewPrefix');
		if ($prefix)
		{
			$path = $prefix . '_' . $path;
		}
		$path = strtolower(Inflector::slug($path));
		
		$filename = CACHE . 'views' . DS . $path . '.php';
		
		if (!file_exists($filename)) {
			$filename = CACHE . 'views' . DS . $path . '_index.php';
		}
		
		$response = parent::beforeDispatch($event);
		
		// the default didn't work, look for our custom one
		if(!$response)
		{
			if (file_exists($filename)) {
				$controller = null;
				$view = new View($controller);
				$view->response = $event->data['response'];
				$result = $view->renderCache($filename, microtime(true));
				if ($result !== false) {
					$event->stopPropagation();
					$event->data['response']->body($result);
					$response = $event->data['response'];
				}
			}
		}
		
		$filetime = time();
		if(file_exists($filename))
		{
			$filetime = filemtime($filename);
		}
			
		if($response)
		{
			$body = $response->body();
			$cachetime = '<div class="cachetime">'.__('Generated:').' <time datetime="'.date('c', $filetime).'"></time></div>';
			if(stripos($body, '<div class="times-loaded">') !== false)
			{
				$body = str_ireplace('<div class="times-loaded">', '<div class="times-loaded">'.$cachetime, $body);
			}
			elseif(stripos($body, '</body>') !== false)
			{
				$body = str_ireplace('</body>', $cachetime.'</body>', $body);
			}
			else
			{
				$body .= $cachetime;
			}
			$response->body($body);
		}
		
		return $response;
	}
}