<?php
/**
 * Custom error handling
 * see: http://book.cakephp.org/2.0/en/development/errors.html
 */
App::uses('Hash', 'Utilities');
class AppError
{
    public static function handleError($code, $description, $file = null, $line = null, $context = null) 
    {
        // show a nice message to the browser's console.
        App::uses('CakeSession', 'Model/Datasource');
        $appErrors = CakeSession::read('AppErrors');
        if(!$appErrors)
        	$appErrors = array();
        
        $md5 = md5($code.$description.$file.$line);
        
        $backtrace = array();
        $traces = debug_backtrace();
        	
        foreach($traces as $i => $trace)
        {
        	$backtrace[] = array(
        		'file' =>  (isset($trace['file'])?$trace['file']:false),
        		'line' =>  (isset($trace['line'])?$trace['line']:false),
        		'class' =>  (isset($trace['class'])?$trace['class']:false),
        		'type' =>  (isset($trace['type'])?$trace['type']:false),
        		'function' =>  (isset($trace['function'])?$trace['function']:false),
        	);
        }
        unset($traces);
        
        $appErrors[$md5] = array(
        	'code' => $code,
        	'description' => $description,
        	'file' => $file,
        	'line' => $line,
        	'context' => $context,
        	'backtrace' => $backtrace,
        );
        CakeSession::write('AppErrors', $appErrors);
        
        return true;
    }
}