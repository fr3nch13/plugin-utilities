<?php
?>
<div class="top">
	<h1><?php echo __('Manage the %s', __('App Config')); ?></h1>
</div>
<div class="center">
	<div class="posts form">
	<?php echo $this->Form->create();?>
	    <fieldset>
	        <legend><?php echo __('Manage the %s', __('App Config')); ?></legend>
	    	<?php
	    		foreach($fields as $key => $settings)
	    		{
	    			if(is_string($settings))
	    			{
	    				echo $settings;
	    				continue;
	    			}
	    			if(isset($settings['clearb']))
	    			{
	    				echo $this->Html->tag('div', '', array('class' => 'clearb'));
	    				continue;
	    			}
	    			if(isset($settings['type']) and $settings['type'] == 'legend')
	    			{
	    				unset($settings['type']);
	    				if(isset($settings['label']))
	    				{
	    					$label = $settings['label'];
	    					unset($settings['label']);
	    					echo $this->Html->tag('h3', $label, $settings);
	    				}
	    				continue;
	    			}
	    			echo $this->Form->input($key, $settings);
	    		}
	    	?>
	    </fieldset>
	<?php echo $this->Form->end(__('Save')); ?>
	</div>
</div>