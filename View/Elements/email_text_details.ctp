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

$sep = (isset($sep)?$sep:str_repeat('-', 80));
$sep_sub = (isset($sep_sub)?$sep_sub:str_repeat('-', 40));

$title = str_replace('&amp;', '&', $title);

?>
	<?php echo $sep_sub; ?>
	
	<?php echo $title; ?>
	
	<?php echo $sep_sub; ?>

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
?>
	<?php echo (trim($detail['name'])?$detail['name']:false); ?>
	
		<?php echo $value; ?>
		
<?php
}