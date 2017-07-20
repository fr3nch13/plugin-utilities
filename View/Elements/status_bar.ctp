<?php 
/** Example:
 *	
	$steps = array(
		array(
			'title' => __('Tracking created.');
			'completed' => true, // boolean 
		),
		array(
			'title' => __('Package picked up.');
			'completed' => true, // boolean 
		),
		array(
			'title' => __('Package out for delivery.');
			'completed' => true, // boolean 
		),
		array(
			'title' => __('Package Delivered.');
			'completed' => false, // boolean 
		),
	);
 *
 */
$steps = (isset($steps)?$steps:array());
echo $this->Html->css('nihfo.status-bar', array('inline' => false));
?>

<table class="status-bar" cellspacing="0" cellpadding="0">
	<tbody>
		<tr>
			<?php foreach($steps as $step_i => $step): ?>
			<td class="number <?php echo ($step['completed']?'step-number-completed':''); ?>">
				<span><?php echo __('Step %s', $step_i); ?></span>
			</td>
			<?php endforeach; ?>
		</tr>
		<tr>
			<?php foreach($steps as $step_i => $step): ?>
			<td class="content <?php echo ($step['completed']?'step-content-completed':''); ?>">
				<?php 
				if(isset($step['title']))
					echo $this->Html->tag('div', $step['title'], array('class' => 'step-title')); 
				?>
				<?php 
				if(isset($step['content']))
					echo $this->Html->tag('div', $step['content'], array('class' => 'step-title')); 
				?>
			</td>
			<?php endforeach; ?>
		</tr>
	</tbody>
</table>
