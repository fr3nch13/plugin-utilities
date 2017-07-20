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
$options = (isset($options)?$options:array());
$class = (isset($options['class'])?$options['class']:'details');

if($this->Html->getExt('txt'))
{
	echo $this->element('Utilities.details_txt', array(
		'title' => $title,
		'details' => $details,
		'options' => $options,
	));
}
else
{
?>

<div class="<?php echo $class; ?>">
	<?php if($title): ?><h3><?php echo $title; ?></h3><?php endif; ?>
	<dl>
		<?php foreach ($details as $detail)
		{
			$class = '';
			if(isset($detail['class'])) $class = $detail['class'];
			
			echo $this->Html->tag('dt', trim($detail['name'])?$detail['name']:'&nbsp;', array('class' => $class));
			
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
			echo $this->Html->tag('dd', $value, array('class' => $class));
			
		}
		?>
	</dl> 
</div>
<?php
}