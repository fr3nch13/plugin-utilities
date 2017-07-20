<?php 

$debugOptions = array();

$siteConfigOptions = Configure::read('AppConfigKeys');
if($siteConfigOptions and is_array($siteConfigOptions))
{
	if(isset($siteConfigOptions['Site.debug']['options']))
		$debugOptions = $siteConfigOptions['Site.debug']['options'];
}

?>

<div class="top">
	<h1><?php echo __('Edit %s', __('Session Settings')); ?></h1>
</div>
<div class="center">
	<div class="form">
	<?php echo $this->Form->create('User', array('url' => array('action' => 'edit_session')));?>
		<fieldset>
			<legend><?php echo __('Session Settings'); ?></legend>
			<?php
				echo $this->Form->input('role', array(
					'label' => __('Active User Role'),
					'description' => __('Change your user role for this session.'),
					'options' => $availableRoles,
					'default' => AuthComponent::user('role'),
				));
				
				if(AuthComponent::user('role') == 'admin' and $debugOptions)
				{
					echo $this->Form->input('debug', array(
						'label' => __('Debug Level'),
						'description' => __('Change the debug level for your session.'),
						'type' => 'select',
						'options' => $debugOptions,
						'default' => Configure::read('debug'),
					));
				}
				
				echo $this->Form->input('id', array('type' => 'hidden'));
			?>
		</fieldset>
	<?php echo $this->Form->end(__('Update %s', __('Session Settings')));?>
	</div>
</div>