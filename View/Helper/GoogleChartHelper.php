<?php

// original copied from: http://bakery.cakephp.org/articles/ixu38/2010/04/30/googlechart-api-helper
// I'm sure i'll be changing something from the original. 
// 02-11-2015 - Brian French
/**
* Google Charts Helper class file.
*
* Simplifies creating charts with the google charts api.
*
* Copyright (c) 2010 Remi DUDREUIL
*
* Licensed under The MIT License
* Redistributions of files must retain the above copyright notice.
*
* @filesource
* @copyright	 Copyright (c) 2010 Remi DUDREUIL
* @link		  http://net.productions.free.fr
* @license	   http://www.opensource.org/licenses/mit-license.php The MIT License
*/
App::uses('FlashHelper', 'View/Helper');
App::uses('UtilitiesAppHelper', 'Utilities.View/Helper');

class GoogleChartHelper extends UtilitiesAppHelper
{
	
	// Constants
	public $BASE	= 'http://chart.apis.google.com/chart?';

	// Variables
	public $types	= array(
		'pie' => 'p',
		'line' => 'lc',
		'sparkline' => 'ls',
		'bar-horizontal' => 'bhg',
		'bar-vertical' => 'bvg',
	);

	public $type;
	public $title;
	public $line_image_div;
	public $graph_data	 = array();
	public $size		 = array();
	public $color		 = array();
	public $fill		 = array();
	public $labelsXY	 = false;
	public $legend;
	public $useLegend	 = true;
	public $background	 = 'a,s,ffffff';
	
	// scale parameters
	public $min			= array();
	public $max			= array();
	
	// data parameters
	public $data_size	= 0;
	
	public $query		= array();
	
	/** Create chart
	*/
	public function __toString()
	{
		// Create query
		$this->query = array(
			'cht'	=> $this->types[strtolower($this->type)],					// Type
			'chtt'	=> $this->title,											// Title
			'chd'	=> 't:'.$this->graph_data['values'],								// Data
			'chl'	=> $this->graph_data['names'],									// Data labels
			'chdl'	=> ( ($this->useLegend) && (is_array($this->legend)) ) ? implode('|',$this->legend) : null, // Data legend
			'chs'	=> $this->size[0].'x'.$this->size[1],						// Size
			'chco'	=> preg_replace( '/[#]+/', '', implode(',',$this->color)), // Color ( Remove # from string )
			'chm'	=> preg_replace( '/[#]+/', '', implode('|',$this->fill)),   // Fill ( Remove # from string )
			'chxt'	=> ( $this->labelsXY == true) ? 'x,y' : null,				// X & Y axis labels
			'chf'	=> preg_replace( '/[#]+/', '', $this->background),			// Background color ( Remove # from string )
			'chds'	=> $this->getScale(),
			'chxr'	=> '1,'.min($this->min).','.max($this->max),
			'chbh'	=> 'a',
		);
		
		// Return chart
		return $this->img(
			$this->BASE.http_build_query($this->query),
			$this->title
		);
	}

	/** Set attributes
	*/
	public function setChartAttrs( $attrs )
	{
		foreach( $attrs as $key => $value )
		{
			$this->{"_set_$key"}($value);
		}
	}
	
	/** get automatique scale
	*/
	protected function getScale()
	{
		$scale = '';
		for($i = 0 ; $i < $this->graph_data_size ; $i++)
		{
			$scale .= $this->min[$i].','.$this->max[$i];
			if($i <> ($this->graph_data_size-1))
				$scale .= ',';
		}
		return $scale;
	}
	
	protected function _set_image_div( $line_image_div )
	{
		$this->line_image_div = $line_image_div;
	}
	
	/** Set Min
	*/
	protected function _set_min( $min )
	{
		$this->min = $min;
	}
	
	/** Set Max
	*/
	protected function _set_max( $max )
	{
		$this->max = $max;
	}
	
	/** Set type
	*/
	protected function _set_type( $type )
	{
		$this->type = $type;
	}

	/** Set title
	*/
	protected function _set_title( $title )
	{
		$this->title = $title;
	}

	/** Set data
	*/
	protected function _set_data( $data )
	{
		// Clear any previous data
		$this->graph_data = array();
		$this->graph_data_size = 0;
		
		// Check if multiple data
		if( is_array(reset($data)) )
		{
			/** Multiple sets of data 
			*/
			foreach( $data as $key => $value )
			{
				// Add data values
				if(!isset($this->graph_data['values'])) $this->graph_data['values'] = array();
				$this->graph_data['values'][] = implode( ',', $value );
				
				// Add data names
				$this->graph_data['names'] = implode( '|', array_keys( $value ) );
				
				$this->graph_data_size++;
			}
			
			/** Implode data correctly
			*/
			$this->graph_data['values'] = implode('|', $this->graph_data['values']);
			/** Create legend
			*/
			$this->legend = array_keys( $data );
		}
		else
		{
			/** Single set of data
			*/
			// Add data values
			$this->graph_data['values'] = implode( ',', $data );
			
			// Add data names
			$this->graph_data['names'] = implode( '|', array_keys( $data ) );
			$this->graph_data_size++;
		}
	}

	/** Set legend
	*/
	protected function _set_legend( $legend )
	{
		$this->legend = $legend;
	}

	/** Set size
	*/
	protected function _set_size( $width, $height = null )
	{
		// check if width contains multiple params
		if(is_array( $width ) )
		{
			$this->size = $width;
		}
		else
		{
			// set each individually
			$this->size[] = $width;
			$this->size[] = $height;
		}
	}

	/** Set color
	*/
	protected function _set_color( $color )
	{
		$this->color = $color;
	}

	/** Set labels
	*/
	protected function _set_labelsXY( $labels )
	{
		$this->labelsXY = $labels;
	}

	/** Set fill
	*/
	protected function _set_fill( $fill )
	{
		// Fill must have atleast 4 parameters
		if( count( $fill ) < 4 )
		{
			// Add remaining params
			$count = count( $fill );
			for( $i = 0; $i < $count; ++$i )
				$fill[$i] = 'b,'.$fill[$i].','.$i.','.($i+1).',0';
		}
		
		$this->fill = $fill;
	}

	/** Set background
	*/
	protected function _set_background( $background )
	{
		$this->background = 'bg,s,'.$background;
	}

	/** Create img html tag
	*/
	protected function img( $url, $alt = null, $attrs = array() )
	{
		$attrs = array_merge_recursive(array('alt' => $alt, 'style' => 'width:'.$this->size[0].'px;height:'.$this->size[1].';'), $attrs);
		return $this->Html->image($url, $attrs);
	}
	
	
/////// Below are the new functions for the new google charts
	
	public $columns = array();
	
	public $default_options = array(
		'div_id' => 'line_chart_material',
		'width' => 900,
		'height' => 500,
		'title' => 'Google Chart',
		'subtitle' => 'subtitle',
	);
	
	public $options = array();
	
	public function reset()
	{
		$this->options = array();
		$this->columns = array();
	}
	
	public function setOptions($options)
	{
		$this->options = array_merge($this->default_options, $options);
	}
	
	public function addColumn($name = false, $data = false)
	{
		$this->columns[$name] = $data;
	}
	
	public function display()
	{
		$addColumn = array();
		$rows = array();
		$jsRows = array();
		$i=0;
		foreach($this->columns as $name => $data)
		{
			$i++;
			$addColumn[] = __('line_data.addColumn(\'number\', \'%s\');', $name);
			
			foreach($data as $key => $count)
			{
				if(!isset($rows[$key])) $rows[$key] = array();
				if(!isset($rows[$key][0])) $rows[$key][0] = $key;
				$rows[$key][$i] = (int)$count;
			}
		}
		
		foreach($rows as $row)
		{
			$jsRows[] = $row;
		}
		
		$addColumn = implode("\n", $addColumn);
		$jsRows = json_encode($jsRows);
		
		$color = false;
		if(is_array($this->color) and $this->color)
		{
			$color_json = array();
			foreach($this->color as $i => $color)
			{
				$color_json[] = array('color' => $color);
			}
			$color_json = '"series": '. json_encode($color_json). ',';
		}
		
		$line_image_div = false;
		if($this->line_image_div)
		{
			$line_image_div = '
			var line_image_div = document.getElementById(\''.$this->line_image_div.'\');
			google.visualization.events.addListener(line_chart, \'ready\', function () {
      			line_image_div.innerHTML = \'<img src="\' + line_chart.getImageURI() + \'">\';
    		});';
		}
		
		$drawLineChart = <<<EOF
		$(document).ready(function () {
		
		function drawLineChart%s () {
			
			var line_data = new google.visualization.DataTable();
			
			line_data.addColumn('string', 'X');
			%s
			line_data.addRows(%s);
			
			var line_options = {
				chart: {
					title: '%s',
					subtitle: '%s'
				},
				tooltip: { trigger: 'selection' },
				'legend':'top',
				%s
			};
			
			var line_chart = new google.visualization.LineChart(document.getElementById('%s'));
			
			%s

			line_chart.draw(line_data, line_options);
		}
		
		drawLineChart%s();
		});
EOF;
		$drawLineChart = __($drawLineChart, $this->options['div_id'], $addColumn, $jsRows, $this->options['title'], $this->options['subtitle'], $color_json, $this->options['div_id'], $line_image_div, $this->options['div_id']);
		$this->Js->buffer($drawLineChart);
	
		
		$drawPieChart = <<<EOF
		$(document).ready(function () {
		
		function drawPieChart%s () {
			
			var window.pieDatum[containerId] = new google.visualization.arrayToDataTable(%s);
			
			var pie_options = {
				title: '%s',
			};
			
			var pie_chart = new google.visualization.PieChart(document.getElementById('%s'));
			
			pie_chart.draw(window.pieDatum[containerId], pie_options);
		}
		
		drawPieChart%s();
		});
EOF;
		return $this->Js->writeBuffer();
	}
	
	public function displayBarChart($options = array(), $data = array())
	{
		if(!$data)
		{
			return false;
		}
		
		$id = 'barChart_'. rand(0, 1000);
		
		if(!isset($options['title']))
			$options['title'] = '';
		
		if(isset($options['id']))
		{
			$id = $options['id'];
			unset($options['id']);
		}
		
		$style = '';
		if(isset($options['width']))
		{
			$style .= ' width: '. $options['width']. 'px;';
		}
		if(isset($options['height']))
		{
			$style .= ' height: '. $options['height']. 'px;';
		}
		
		$imageStuff = '';
		$div_image = '';
		if(isset($options['includeImage']) and $options['includeImage'])
		{
			unset($options['includeImage']);
			$id_image = $id. '_image';
			$div_image = $this->Html->tag('div', '', array('id' => $id_image, 'class' => 'google_chart google_chart_png google_chart_bar', 'style' => $style));
			
			$imageStuff = '
				var bar_image_div = document.getElementById(\''.$id_image.'\');
				google.visualization.events.addListener(window.googleChartCharts[containerId], \'ready\', function () {
					bar_image_div.innerHTML = \'<img src="\' + window.googleChartCharts[containerId].getChart().getImageURI() + \'">\';
				});';
		}
		extract($data);
		
		$firstLabel = array_shift($legend);
		$labels = array(
			__('window.googleChartDatum[containerId].addColumn("string", "%s");', $firstLabel),
		);
		foreach($legend as $i => $legendName)
		{
			$labels[] = __('window.googleChartDatum[containerId].addColumn("number", "%s");', $legendName);
		}
		$labels = implode("\n", $labels);
		
		$_data = $data;
		$data = array();
		
		$i = 0;
		foreach($_data as $k => $row)
		{
			foreach($row as $cell)
			{
				if(preg_match('/^\d+$/', $cell))
					$data[$i][] = (int)$cell;
				else
					$data[$i][] = $cell;
			}
			$i++;
		}
		
		$drawBarChart = <<<EOF
		$(document).ready(function () {
			function drawBarChart_%s () {
				var containerId = '%s';
				
				if(typeof window.googleChartCharts === 'undefined')
				{
					window.googleChartCharts = {};
				}
				
				if(typeof window.googleChartDatum === 'undefined')
				{
					window.googleChartDatum = {};
				}
				
				if(typeof window.googleChartOptions === 'undefined')
				{
					window.googleChartOptions = {};
				}
				
				window.googleChartDatum[containerId] = new google.visualization.DataTable();
				
				// first one is always the x-axis
				%s
				
				window.googleChartDatum[containerId].addRows(%s);
				
				window.googleChartOptions[containerId] = %s;
				
				window.googleChartCharts[containerId] = new google.visualization.ChartWrapper({
					'chartType': 'BarChart',
					'containerId': containerId,
					'options': window.googleChartOptions[containerId],
					'dataTable': window.googleChartDatum[containerId],
				});
				
				%s
				
				if(typeof window.googleChartEventSelect === 'undefined')
				{
					window.googleChartEventSelect = function(){};
				}
				
				window.googleChartEventSelect[containerId] = function( event ){
					return true;
					var container = document.getElementById(containerId);
					container = $(container);
					var stats = container.parents('.dashboard-block-content').find('.dashboard-stats .dashboard-stat');
					
					// first unexplode all of the slices
					for (var y = 0, maxrows = window.googleChartDatum[containerId].getNumberOfRows(); y < maxrows; y++) {
						window.googleChartOptions[containerId].slices[y].offset = '0';
						if(stats)
						{
							stats.each(function( index )
							{
								$(this).removeClass('highlighted');
							});
						}
					}
					
					var chart = window.googleChartCharts[containerId].getChart();
					var selection = chart.getSelection();
					
					for (var i = 0; i < selection.length; i++) {
						var item = selection[i];
						var rowNumber = parseInt(item.row);
						var value = window.googleChartDatum[containerId].getValue(rowNumber, 2);
						value = value.replace(/\./g, '_');
						var stat_selected = container.parents('.dashboard-block-content').find('.dashboard-stats .dashboard-stat.'+value);
						
						if(chart.selectedSlice == rowNumber) // If this is already selected, unselect it
						{
							chart.selectedSlice = -1;
						}
						else  // else explode it
						{
							chart.selectedSlice = rowNumber;
							// explode only this slice 
							window.googleChartOptions[containerId].slices[rowNumber].offset = '.1';
							
							if(stat_selected)
								stat_selected.addClass('highlighted');
						}
					}
					window.googleChartCharts[containerId].draw();
				}
				
				google.visualization.events.addListener(window.googleChartCharts[containerId], 'select', window.googleChartEventSelect[containerId]);
				
				window.googleChartCharts[containerId].draw();
				window.googleChartCharts[containerId].getChart().selectedSlice = -1;
			}
			
			drawBarChart_%s();
		});
EOF;
		$drawBarChart = __($drawBarChart, $id, $id, $labels, json_encode($data), json_encode($options), $imageStuff, $id);
		$this->Js->buffer($drawBarChart);
		$div = $this->Html->tag('div', '', array('id' => $id, 'class' => 'google_chart google_chart_svg google_chart_bar', 'style' => $style));
		
		return $div. $div_image. $this->Js->writeBuffer();
	}
	
	public function displayLineChart($options = array(), $data = array())
	{
		if(!$data)
		{
			return false;
		}
		
		$id = 'lineChart_'. rand(0, 1000);
		
		if(!isset($options['title']))
			$options['title'] = '';
		
		if(isset($options['id']))
		{
			$id = $options['id'];
			unset($options['id']);
		}
		
		$style = '';
		if(isset($options['width']))
		{
			$style .= ' width: '. $options['width']. 'px;';
		}
		if(isset($options['height']))
		{
			$style .= ' height: '. $options['height']. 'px;';
		}
		
		$imageStuff = '';
		$div_image = '';
		if(isset($options['includeImage']) and $options['includeImage'])
		{
			unset($options['includeImage']);
			$id_image = $id. '_image';
			$div_image = $this->Html->tag('div', '', array('id' => $id_image, 'class' => 'google_chart google_chart_png google_chart_line', 'style' => $style));
			
			$imageStuff = '
				var line_image_div = document.getElementById(\''.$id_image.'\');
				google.visualization.events.addListener(window.googleChartCharts[containerId], \'ready\', function () {
					line_image_div.innerHTML = \'<img src="\' + window.googleChartCharts[containerId].getChart().getImageURI() + \'">\';
				});';
		}
		extract($data);
		
		$firstLabel = array_shift($legend);
		$labels = array(
			__('window.googleChartDatum[containerId].addColumn("string", "%s");', $firstLabel),
		);
		foreach($legend as $i => $legendName)
		{
			$labels[] = __('window.googleChartDatum[containerId].addColumn("number", "%s");', $legendName);
		}
		$labels = implode("\n", $labels);
		
		$_data = $data;
		$data = array();
		
		$i = 0;
		foreach($_data as $k => $row)
		{
			foreach($row as $cell)
			{
				if(preg_match('/^\d+$/', $cell))
					$data[$i][] = (int)$cell;
				else
					$data[$i][] = $cell;
			}
			$i++;
		}
		
		$drawLineChart = <<<EOF
		$(document).ready(function () {
			function drawLineChart_%s () {
				var containerId = '%s';
				
				if(typeof window.googleChartCharts === 'undefined')
				{
					window.googleChartCharts = {};
				}
				
				if(typeof window.googleChartDatum === 'undefined')
				{
					window.googleChartDatum = {};
				}
				
				if(typeof window.googleChartOptions === 'undefined')
				{
					window.googleChartOptions = {};
				}
				
				window.googleChartDatum[containerId] = new google.visualization.DataTable();
				
				// first one is always the x-axis
				%s
				
				window.googleChartDatum[containerId].addRows(%s);
				
				window.googleChartOptions[containerId] = %s;
				
				window.googleChartCharts[containerId] = new google.visualization.ChartWrapper({
					'chartType': 'LineChart',
					'containerId': containerId,
					'options': window.googleChartOptions[containerId],
					'dataTable': window.googleChartDatum[containerId],
				});
				
				%s
				
				if(typeof window.googleChartEventSelect === 'undefined')
				{
					window.googleChartEventSelect = function(){};
				}
				
				window.googleChartEventSelect[containerId] = function( event ){
					return true;
					var container = document.getElementById(containerId);
					container = $(container);
					var stats = container.parents('.dashboard-block-content').find('.dashboard-stats .dashboard-stat');
					
					// first unexplode all of the slices
					for (var y = 0, maxrows = window.googleChartDatum[containerId].getNumberOfRows(); y < maxrows; y++) {
						window.googleChartOptions[containerId].slices[y].offset = '0';
						if(stats)
						{
							stats.each(function( index )
							{
								$(this).removeClass('highlighted');
							});
						}
					}
					
					var chart = window.googleChartCharts[containerId].getChart();
					var selection = chart.getSelection();
					
					for (var i = 0; i < selection.length; i++) {
						var item = selection[i];
						var rowNumber = parseInt(item.row);
						var value = window.googleChartDatum[containerId].getValue(rowNumber, 2);
						value = value.replace(/\./g, '_');
						var stat_selected = container.parents('.dashboard-block-content').find('.dashboard-stats .dashboard-stat.'+value);
						
						if(chart.selectedSlice == rowNumber) // If this is already selected, unselect it
						{
							chart.selectedSlice = -1;
						}
						else  // else explode it
						{
							chart.selectedSlice = rowNumber;
							// explode only this slice 
							window.googleChartOptions[containerId].slices[rowNumber].offset = '.1';
							
							if(stat_selected)
								stat_selected.addClass('highlighted');
						}
					}
					window.googleChartCharts[containerId].draw();
				}
				
				google.visualization.events.addListener(window.googleChartCharts[containerId], 'select', window.googleChartEventSelect[containerId]);
				
				window.googleChartCharts[containerId].draw();
				window.googleChartCharts[containerId].getChart().selectedSlice = -1;
			}
			
			drawLineChart_%s();
		});
EOF;
		$drawLineChart = __($drawLineChart, $id, $id, $labels, json_encode($data), json_encode($options), $imageStuff, $id);
		$this->Js->buffer($drawLineChart);
		$div = $this->Html->tag('div', '', array('id' => $id, 'class' => 'google_chart google_chart_svg google_chart_line', 'style' => $style));
		
		return $div. $div_image. $this->Js->writeBuffer();
	}
	
	public function displayPieChart($options = array(), $data = array())
	{
		$id = 'pieChart_'. rand(0, 1000);
		
		if(!isset($options['title']))
			$options['title'] = '';
		
		if(isset($options['id']))
		{
			$id = $options['id'];
			unset($options['id']);
		}
		
		$style = '';
		if(isset($options['width']))
		{
			$style .= ' width: '. $options['width']. 'px;';
		}
		if(isset($options['height']))
		{
			$style .= ' height: '. $options['height']. 'px;';
		}
		
		$imageStuff = '';
		$div_image = '';
		if(isset($options['includeImage']) and $options['includeImage'])
		{
			unset($options['includeImage']);
			$id_image = $id. '_image';
			$div_image = $this->Html->tag('div', '', array('id' => $id_image, 'class' => 'google_chart google_chart_png google_chart_pie', 'style' => $style));
			
			$imageStuff = '
				var google_image_div = document.getElementById(\''.$id_image.'\');
				google.visualization.events.addListener(window.googleChartCharts[containerId], \'ready\', function () {
					google_image_div.innerHTML = \'<img src="\' + window.googleChartCharts[containerId].getChart().getImageURI() + \'">\';
				});';
		}
		
		$labels = array_shift($data);
		
		$drawPieChart = <<<EOF
		$(document).ready(function () {
			function drawPieChart_%s () {
				var containerId = '%s';
				
				if(typeof window.googleChartCharts === 'undefined')
				{
					window.googleChartCharts = {};
				}
				
				if(typeof window.googleChartDatum === 'undefined')
				{
					window.googleChartDatum = {};
				}
				
				if(typeof window.googleChartOptions === 'undefined')
				{
					window.googleChartOptions = {};
				}
				
				window.googleChartDatum[containerId] = new google.visualization.DataTable();
				window.googleChartDatum[containerId].addColumn('string', '%s'); // Implicit domain label col.
				window.googleChartDatum[containerId].addColumn('number', '%s');
				window.googleChartDatum[containerId].addColumn('string', '%s');
				window.googleChartDatum[containerId].addRows(%s);
				
				window.googleChartOptions[containerId] = %s;
				
				window.googleChartCharts[containerId] = new google.visualization.ChartWrapper({
					'chartType': 'PieChart',
					'containerId': containerId,
					'options': window.googleChartOptions[containerId],
					'view': {'columns': [0, 1]},
					'dataTable': window.googleChartDatum[containerId],
				});
				
				%s
				
				if(typeof window.googleChartEventSelect === 'undefined')
				{
					window.googleChartEventSelect = function(){};
				}
				
				window.googleChartEventSelect[containerId] = function( event ){
					var container = document.getElementById(containerId);
					container = $(container);
					var stats = container.parents('.dashboard-block-content').find('.dashboard-stats .dashboard-stat');
					
					// first unexplode all of the slices
					for (var y = 0, maxrows = window.googleChartDatum[containerId].getNumberOfRows(); y < maxrows; y++) {
						window.googleChartOptions[containerId].slices[y].offset = '0';
						if(stats)
						{
							stats.each(function( index )
							{
								$(this).removeClass('highlighted');
							});
						}
					}
					
					var chart = window.googleChartCharts[containerId].getChart();
					var selection = chart.getSelection();
					
					for (var i = 0; i < selection.length; i++) {
						var item = selection[i];
						var rowNumber = parseInt(item.row);
						var value = window.googleChartDatum[containerId].getValue(rowNumber, 2);
						value = value.replace(/\./g, '_');
						var stat_selected = container.parents('.dashboard-block-content').find('.dashboard-stats .dashboard-stat.'+value);
						
						if(chart.selectedSlice == rowNumber) // If this is already selected, unselect it
						{
							chart.selectedSlice = -1;
						}
						else  // else explode it
						{
							chart.selectedSlice = rowNumber;
							// explode only this slice 
							window.googleChartOptions[containerId].slices[rowNumber].offset = '.2';
							
							if(stat_selected)
								stat_selected.addClass('highlighted');
						}
					}
					window.googleChartCharts[containerId].draw();
				}
				
				google.visualization.events.addListener(window.googleChartCharts[containerId], 'select', window.googleChartEventSelect[containerId]);
				
				window.googleChartCharts[containerId].draw();
				window.googleChartCharts[containerId].getChart().selectedSlice = -1;
			}
			
			drawPieChart_%s();
		});
EOF;
		$drawPieChart = __($drawPieChart, $id, $id, $labels[0], $labels[1], '', json_encode($data), json_encode($options), $imageStuff, $id);
		$this->Js->buffer($drawPieChart);
		$div = $this->Html->tag('div', '', array('id' => $id, 'class' => 'google_chart google_chart_svg google_chart_pie', 'style' => $style));
		
		return $div. $div_image. $this->Js->writeBuffer();
	}
} 