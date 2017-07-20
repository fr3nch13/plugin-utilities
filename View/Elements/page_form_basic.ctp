<?php


$page_title = (isset($page_title)?$page_title:false);
$page_subtitle = (isset($page_subtitle)?$page_subtitle:false);
$page_subtitle2 = (isset($page_subtitle2)?$page_subtitle2:false);
$page_options_title = (isset($page_options_title)?$page_options_title:__('Options'));
$page_options = (isset($page_options)?$page_options:[]);
$page_options_title2 = (isset($page_options_title2)?$page_options_title2:__('More Options'));
$page_options2 = (isset($page_options2)?$page_options2:[]);
$page_options_html = (isset($page_options_html)?$page_options_html:[]);
$page_description = (isset($page_description)?$page_description:false);
$subscribable = (isset($subscribable)?$subscribable:false);

$model = (isset($model)?$model:Inflector::singularize(Inflector::camelize($this->params->controller)));
$controller = (isset($action)?$action:$this->params->controller);
$action = (isset($action)?$action:$this->params->action);

$form_id = (isset($form_id)?$form_id:'object-form-'. rand(1,1000));
$object_title = (isset($object_title)?$object_title:Inflector::humanize(Inflector::underscore($model)));

$inputs = (isset($inputs)?$inputs:[]);
$inputs = Hash::normalize($inputs);

if(isset($inputs['id']))
{
	if(!isset($action_title))
		$action_title = __('Edit');
	if(!isset($save_title))
		$save_title = __('Update');
}

$action_title = (isset($action_title)?$action_title:__('Add'));
$action_title = __('%s %s', $action_title, $object_title);
$page_title = ($page_title?$page_title:$action_title);

$save_title = (isset($save_title)?$save_title:__('Save'));
$save_title = __('%s %s', $save_title, $object_title);

$form_title = (isset($form_title)?$form_title:$page_title);
$form_options = (isset($form_options)?$form_options:[]);
$submit_title = (isset($submit_title)?$submit_title:$save_title);




echo $this->element('Utilities.object_top', [
	'page_title' => $page_title,
	'page_subtitle' => $page_subtitle,
	'page_subtitle2' => $page_subtitle2,
	'page_description' => $page_description,
	'page_options_title' => $page_options_title,
	'page_options' => $page_options,
	'page_options_title2' => $page_options_title2,
	'page_options2' => $page_options2,
	'page_options_html' => $page_options_html,
	'subscribable' => $subscribable,
]);
?>
<?php 
?>
<div class="center object-form" id="<?= $form_id ?>">
		<?php echo $this->Form->create($form_options); ?>
		    <fieldset>
		        <legend><?= $form_title; ?></legend>
		    	<?php
		    		foreach($inputs as $field => $fieldOptions)
		    		{
		    			echo $this->Form->input($field, $fieldOptions);
		    		}
		    	?>
		    </fieldset>
		<?php echo $this->Form->end($submit_title); ?>
	</div>
</div>

	
<script type="text/javascript">
//<![CDATA[
$(document).ready(function ()
{
	var formOptions = {
	};
	
	$('div#<?php echo $form_id; ?>').objectForm(formOptions);
});
//]]>
</script>