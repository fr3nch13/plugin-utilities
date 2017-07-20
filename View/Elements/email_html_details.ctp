<?php
/**
 * File: /app/View/Elements/details.ctp
 * 
 * Use: provides a standard for displaying a list of details.
 *
 * Usage: echo $this->element('details', array([details]));
 */
$title = (isset($title)?$title:__('Details'));
$details = (isset($details)?$details:array());
$class = (isset($options['class'])?$options['class']:'');
?>

<div class="details <?php echo $class; ?>">
	<h3><?php echo $title; ?></h3>
	<table class="details_blocks" rowspan="0" colspan="0" >
		<?php foreach ($details as $detail)
		{
			
			$value = '&nbsp;';
			if(trim($detail['value']))
			{
				$value = $detail['value'];
				if(isset($detail['filter_data']))
				{
					$filter_data = array(
						'field' => false,
						'value' => $value,
					);
					if(isset($detail['escape']) and $detail['escape'])
					{
						$filter_data['escape'] = $detail['escape'];
					}
					if(is_string($detail['filter_data']))
					{
						$filter_data['field'] = $detail['filter_data'];
					}
					elseif(is_array($detail['filter_data']))
					{
						$filter_data = array_merge($filter_data, $detail['filter_data']);
					}
					$value = $this->Wrap->filterLink($value, $filter_data);
				}
				elseif(isset($detail['escape']) and $detail['escape'])
				{
					$value = $this->Wrap->escape($value);
				}
			}
			
			$tds = $this->Html->tag('td', (trim($detail['name'])?$detail['name']:'&nbsp;'), array('class' => 'details_dt'));
			$tds .= $this->Html->tag('td', $value, array('class' => 'details_dd'));
			
			echo $this->Html->tag('tr', $tds);
		}
		?>
	</table> 
</div>