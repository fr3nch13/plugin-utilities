<?php

$out = array();
if($appErrors = CakeSession::read('AppErrors'))
{
	$out[] = $this->Html->tag('h4', __('Application errors reported:'));
	
	$consoleBuffer = array(
		__('URL: %s', $this->Html->permaLink()),
		__('Referer: %s', (isset($_SERVER["HTTP_REFERER"])?$_SERVER["HTTP_REFERER"]:'unknown')),
		__('Controller: %s', $this->request->controller),
		__('Action: %s', $this->request->action),
		__('Post UUID: %s', CakeSession::read('postId')),
	);
	
	
	$backCount = count($consoleBuffer);
	foreach($appErrors as $appError)
	{
		$file = str_replace(APP, 'APP'. DS, $appError['file']);
		$msg = __('Code: %s - %s File: %s: %s', $appError['code'], $appError['description'], $file, $appError['line']);
		$out[] = $this->Html->tag('div', $msg, array('class' => 'error-message'));
		$consoleBuffer[] = $msg;
		
		// backgraces
		if(isset($appError['backtrace']))
		{
			$out[] = '<div class="backtraces">';
			$out[] = $consoleBuffer[] = __('Backtrace:');
			foreach($appError['backtrace'] as $trace)
			{
				$out[] = $consoleBuffer[] = __('<b>File: %s(%s)</b>, Function: %s%s%s();', 
					$trace['file'], $trace['line'], $trace['class'], $trace['type'], $trace['function']
				);
				$out[] = '<br/>';
				$consoleBuffer[] = "\n";
			}
			$out[] = '</div>';
		}
		
		$out[] = '<hr/>';
		$consoleBuffer[] = '===================';
	}
	$out[] = '<script type="text/javascript"> $(document).ready(function () {	';
	foreach($consoleBuffer as $consoleLine)
	{
		$consoleLine = strip_tags($consoleLine);
		$consoleLine = trim($consoleLine);
		
		if($consoleLine)
			$out[] = "console.error('".strip_tags($consoleLine)."');";
	}
	$out[] = ' }); </script>';
	CakeSession::write('AppErrors', array());
	CakeSession::write('postId', false);
	
	//get it to send an error message via email.
	// I know this is a weird place to do it, but it seems the only real good place for it.
	App::uses('CakeEmail', 'Network/Email');
	$Email = new CakeEmail();
	
	$from = array('example@example.com' => 'Portals');
	
	if(class_exists('AuthComponent') and AuthComponent::user('email'))
		$from = array(AuthComponent::user('email') => AuthComponent::user('name'));
	
	$Email->from($from);
	$Email->to('');
	$Email->subject(__('App Errors - Count: %s', count($appErrors)));
	$Email->send(implode("\n", $consoleBuffer));
}
if($out)
{
	$classes = array('app-errors');
	if($this->request->is('ajax')) $classes[] = 'app-errors-ajax';
	echo $this->Html->tag('div', implode("\n", $out), array('class' => $classes));
}