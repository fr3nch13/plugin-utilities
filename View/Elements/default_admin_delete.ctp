<?php 
// File: plugins/utilities/View/Element/default_admin_delete.ctp
?>
<div class="top">
	<h1><?php echo __('Delete %s: %s', $nice_name, $item_name); ?></h1>
</div>
<div class="center">
	<div class="form">
		<?php echo $this->Form->create(); ?>
		    <fieldset>
		        <legend><?php echo __('Delete %s: %s', $nice_name, $item_name); ?></legend>
		        <h4 class="info"><?php echo __('Some other objects are assigned to this %s, please select another %s to transfer them to.', $nice_name, $nice_name); ?></h4>
		    	<?php
					
					echo $this->Wrap->divClear();
					
					echo $this->Form->input('current_id', array(
						'type' => 'hidden',
					));
					
					foreach($associations as $associated_model)
					{
						$nice_model = Inflector::underscore($associated_model);
						$nice_models = Inflector::pluralize($nice_model);
						$nice_model = Inflector::humanize($nice_model);
						$nice_models = Inflector::humanize($nice_models);
						echo $this->Form->input($associated_model.'.new_id', array(
							'label' => array(
								'text' => __('Select another %s to assign to the %s currently assigned to this one.', $nice_name, $nice_models),
							),
							'options' => $options,
							'searchable' => true,
						));
					}
		    	?>
		    </fieldset>
		<?php echo $this->Form->end(__('Delete %s', $nice_name)); ?>
	</div>
</div>