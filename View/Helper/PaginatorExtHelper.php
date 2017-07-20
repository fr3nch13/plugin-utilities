<?php

// plugins/utilities/View/Helper/WrapHelper.php
App::uses('UtilitiesAppHelper', 'Utilities.View/Helper');
App::uses('PaginatorHelper', 'View/Helper');

/*
 * Used as a wrapper for the form helper
 * Used to extend the form helper and add more functionality
 */
class PaginatorExtHelper extends PaginatorHelper 
{
	public $helpers = array(
	'Html', 'Ajax', 'Time', 'Js' => array('JqueryUi'),
	'Number'
	);
	
	// overwrites the PaginatorHelper::url()
	// used to include the page:1 for the first page stuff
	public function url($options = array(), $asArray = false, $model = null) {
		$paging = $this->params($model);
		$url = array_merge(array_filter($paging['options']), $options);
		
		if(isset($this->_View->passedArgs['field']))
			$url['field'] = $this->_View->passedArgs['field'];
		if(isset($this->_View->passedArgs['value']))
			$url['value'] = $this->_View->passedArgs['value'];

		if (isset($url['order'])) {
			$sort = $direction = null;
			if (is_array($url['order'])) {
				list($sort, $direction) = array($this->sortKey($model, $url), current($url['order']));
			}
			unset($url['order']);
			$url = array_merge($url, compact('sort', 'direction'));
		}
		$url = $this->_convertUrlKeys($url, $paging['paramType']);
		if (!empty($url['page']) && $url['page'] == 1) {
//			$url['page'] = null;
		}
		if (!empty($url['?']['page']) && $url['?']['page'] == 1) {
			unset($url['?']['page']);
		}
		if ($asArray) {
			return $url;
		}
		return parent::url($url);
	}
	
	// used to make the numbers nice (e.g. add commas for readability)
	public function counter($options = array())
	{
		if (is_string($options))
		{
			$options = array('formatExt' => $options);
		}
		
		$options['format'] = json_encode(array(
			'page' => '{:page}',
			'pages' => '{:pages}',
			'current' => '{:current}',
			'count' => '{:count}',
			'start' => '{:start}',
			'end' => '{:end}',
			'model' => '{:model}',
		));
		
		if($results = parent::counter($options))
		{
			$results = json_decode($results);
			
			$map = array(
				'%page%' => $this->Number->format($results->page),
				'%pages%' => $this->Number->format($results->pages),
				'%current%' => $this->Number->format($results->current),
				'%count%' => $this->Number->format($results->count),
				'%start%' => $this->Number->format($results->start),
				'%end%' => $this->Number->format($results->end),
				'%model%' => $results->model
			);
			$out = str_replace(array_keys($map), array_values($map), $options['formatExt']);
			
			$newKeys = array(
				'{:page}', '{:pages}', '{:current}', '{:count}', '{:start}', '{:end}', '{:model}'
			);
			$out = str_replace($newKeys, array_values($map), $out);
			
			return $out;
		}
	}
}