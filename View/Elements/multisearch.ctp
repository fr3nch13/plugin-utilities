<?php
// app/View/Elements/multisearch.ctp

// Provides a Search box for the site

// set defaults

$previous_action = (isset($this->request->query['previous_action'])?$this->request->query['previous_action']:'index');
$prefix = false;
if(stripos($previous_action, '_') !== false)
{
	list($prefix, $previous_action) = split('\_', $previous_action);
}

$model = (isset($model)?$model:Inflector::singularize(Inflector::camelize($this->params->controller)));
$action = (isset($previous_action)?$previous_action:$this->params->action);
$field = (isset($field)?$field:'q');
$title = (isset($title)?$title:strtolower($this->params->controller));
$method = (isset($method)?$method:'get');
$search_id = (isset($search_id)?$search_id:false);
if(stripos($title, '_'))
{
	list($a, $b) = split('\_', $title);
	$a = Inflector::singularize($a);
	$title = $a. ' '. $b;
}
$title = __('Search %s Here', ucwords($title));

$value = (isset($this->params['named'][$field])?trim($this->params['named'][$field]):false);

$field_class = false;
if($value)
{
	$field_class = 'search_active';
}
$url = array_merge(array('action' => $action), array('page' => 1, 'ms' => 1), $this->params['pass']);

if($prefix)
{
	$url[$prefix] = true;
}

$form_options = array(
	'url' => $url,
	'class' => 'tabform',
	'type' => $method,
);
if($search_id)
{
	$form_options['id'] = $search_id;
}
?>
<div class="top">
	<h1><?php echo $title; ?></h1>
</div>
<div class="form_multisearch">
	<?php echo $this->Form->create($model, $form_options); ?>
		<fieldset>
		<?php
			echo $this->Form->input($field, array(
				'div' => false,
				'label' => __('Place each different search term on a new line.'),
				'type' => 'textarea',
				'value' => $value,
				'class' => $field_class,
			));
			
			echo $this->Form->submit(__('Search'), array(
				'div' => false,
			));
			
			echo $this->Wrap->divClear();
		?>
		</fieldset>
	<?php echo $this->Form->end(); ?>
</div>