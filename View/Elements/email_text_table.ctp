<?php

// hold the array of table headers
// format: $th['column_key'] = content;
$th = (isset($th)?$th:array()); 

// holds the data
// format: $td[i++]['column_key'] = content
// for full format, 
// see: http://book.cakephp.org/2.0/en/core-libraries/helpers/html.html#HtmlHelper::tableCells
$td = (isset($td)?$td:array());  

$no_records_default = __('No records were found.');

$no_records = (isset($no_records)?$no_records:$no_records_default);

$sep = (isset($sep)?$sep:str_repeat('-', 80));

?>
    
<?php if(count($td)): ?>
<?php 
	$th_index = array();
	$x = 0;
	foreach ($th as $i => $th_column)
	{
		$options = false;
		$content = array();
		if(is_array($th_column))
		{
			if(isset($th_column['content'])) $content = $th_column['content'];
			if(isset($th_column['options'])) $options = $th_column['options'];
		}
		else
		{
			$content = $th_column;
		}
		$th_index[$x] = $content;
		$x++;
	}
	
	$this->Html->setSpaceWrap($th_index);
	
	$th_count = count($th);
	
	$rows = array();
	foreach($td as $i => $cells)
	{
		$row = array();
		foreach($cells as $j => $cell)
		{
			if(isset($th_index[$j]))
			{
				if(is_array($cell))
				{
					$_cell = $cell;
					$cell = array();
					foreach($_cell as $v)
					{
						if(is_string($v)) $cell[] = $v;
					}
					$cell = implode("\n". $this->Html->spaceWrap(). ' - ', $cell);
				}
				$row[] = $this->Html->spaceWrap($th_index[$j]). ' - '. $cell;
			}
		}
		$row = implode("\n", $row);
		$rows[] = $row;
	}
	echo implode("\n". $sep. "\n", $rows);
?>
<?php else: ?>
    
<?php echo $no_records; ?>
    
<?php endif; ?>
