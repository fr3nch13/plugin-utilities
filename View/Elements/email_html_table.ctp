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

// highlight the owner in the table if needed
$possible_owner = ((isset($possible_owner) and $possible_owner)?$possible_owner:false);

?>
	<div class="clearb"> </div>
	
	<?php if($possible_owner): ?>
	<div class="possible_owner">
		<span class="owner">&nbsp;</span> = Owner
	</div>
	<?php endif; ?>
	
	<?php if(count($td)): ?>
	
	<table class="listings" cellspacing="0" cellpadding="0">
		<thead>
		<tr>
			<?php foreach ($th as $i => $th_column)
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
				echo $this->Html->tag('th', $content, $options);
			}
			?>
		</tr>
		</thead>
		<tbody>
		<?php 
			$th_count = count($th);

			foreach($td as $i => $cell)
			{
				// incase the cells don't match up
				if(count($cell) < $th_count)
				{
					$last_cell = array_pop($td[$i]);
					if(!is_array($last_cell))
					{
						$last_cell = array($last_cell);
					}
					$last_cell[]['colspan'] = $th_count - (count($cell) -1);
					array_push($td[$i], $last_cell);
				}
			}
			
			echo $this->Html->tableCells($td);
		?>
		</tbody>
	</table>
	<?php else: ?>
	<div class="no_results">
		<?php echo $no_records; ?>
	</div>
	<?php endif; ?>
