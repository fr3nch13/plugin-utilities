<?php
// app/View/Elements/search.ctp

// Provides a Search box for the site

// set defaults

$model = (isset($model)?$model:Inflector::singularize(Inflector::camelize($this->params->controller)));
$controller = (isset($action)?$action:$this->params->controller);
$action = (isset($action)?$action:$this->params->action);
$field = (isset($field)?$field:'q');
$placeholder = (isset($placeholder)?$placeholder:strtolower($this->params->controller));
$placeholder_or = (isset($placeholder_or)?$placeholder_or:strtolower($this->params->controller));
$method = (isset($method)?$method:'post');
$search_id = (isset($search_id)?$search_id:$model.Inflector::camelize($action).'Form');
$search_primaryKey = (isset($search_primaryKey)?$search_primaryKey:false);

if(!$search_primaryKey and isset($searchFields) and isset($primaryKey))
	if(isset($searchFields[$model.'.'.$primaryKey]) or in_array($model.'.'.$primaryKey, $searchFields))
		$search_primaryKey = true;

// arguments for the clear button
$clear_args = $this->passedArgs;
foreach($clear_args as $k => $v)
{
	if(preg_match('/^Filter\./i', $k))
		unset($clear_args[$k]);
}
$clear_args = array_merge(array('action' => $action), $clear_args, array('page' => 1, $field => '' , 'f' => '', 'ex' => 0, 'p' => 0));

$search_field_id = (isset($search_field_id)?$search_field_id:'f');
$search_fields = (isset($search_fields)?$search_fields:false);

$permalink = (isset($permalink)?$permalink:$this->Html->permaLink());

$value = false;
$field_class = 'search_input';
if(isset($this->passedArgs['ms']) and $this->passedArgs['ms'])
{
	$placeholder = __('[ filtered for multiple items ]');
	$field_class .= ' search_active';
	$clear_args['ms'] = 0;
}
else
{
	if(stripos($placeholder, '_'))
	{
		$plparts = explode('_', $placeholder);
		$plparts[0] = Inflector::singularize($plparts[0]);
		$placeholder = implode(' ', $plparts);
	}
	$placeholder = __('Search %s here', ucwords($placeholder));
	if(stripos($placeholder_or, '_'))
	{
		$plparts = explode('_', $placeholder);
		$plparts[0] = Inflector::singularize($plparts[0]);
		$placeholder = implode(' ', $plparts);
	}
	$placeholder_or = __('Search multiple %s here. Seperate each item with a new line or tab.', ucwords($placeholder_or));
	$value = (isset($this->passedArgs[$field])?trim($this->passedArgs[$field]):false);
	$value = preg_replace('/\t/', "\n", $value);
}
$ex_check = (isset($this->passedArgs['ex'])?trim($this->passedArgs['ex']):false);
$primary_check = (isset($this->passedArgs['p'])?trim($this->passedArgs['p']):false);

$search_field_options = false;
$search_field_selected = false;
if($search_fields)
{
	$search_field_options = array(
		'' => __('All'),
	);
	
	$search_field_selected = '';

	foreach($search_fields as $search_field)
	{
		$search_field_parts = explode('.', $search_field);
		foreach($search_field_parts as $i => $search_field_part)
		{
			$search_field_parts[$i] = Inflector::humanize($search_field_parts[$i]);
			$search_field_parts[$i] = trim(implode(' ', preg_split('/(?=[A-Z])/',$search_field_parts[$i])));
		}
		$search_field_title = implode(' -> ', $search_field_parts);
		
		$search_field_options[$search_field] = $search_field_title;
	}
	
	if(isset($this->passedArgs['f']) and trim($this->passedArgs['f']))
	{
		$search_field_selected = $this->passedArgs['f'];
		$clear_args['f'] = '';
	}
}

if($value)
{
	$field_class .= ' search_active';
}
$url = $this->Html->urlModify(array('page' => 1));
			
if(isset($this->passedArgs['field']) and isset($this->passedArgs['value']))
{
	$url['field'] = $this->passedArgs['field'];
	$url['value'] = $this->passedArgs['value'];
}
if(isset($this->passedArgs['list']))
	$url['list'] = $this->passedArgs['list'];

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
<div class="form_search">
	<?php echo $this->Form->create($model, $form_options); ?>
		<fieldset>
		<?php
			
			$search_single = $this->Html->link(__('+'), $clear_args, array('class' => 'expand_contract'));

			$search_single .= $this->Form->input($field, array(
				'div' => false,
				'label' => false,
				'type' => 'text',
				'placeholder' => $placeholder,
				'value' => $value,
				'class' => $field_class,
				'id' => 'search-query-input',
			));
			
			$search_single_span = $this->Html->tag('div', $search_single, array('class' => 'search_single search_field'));

			$search_or = $this->Html->link(__('-'), $clear_args, array('class' => 'expand_contract'));
			$search_or .= $this->Form->input($field, array(
				'div' => false,
				'label' => false,
				'type' => 'textarea',
				'placeholder' => $placeholder_or,
				'value' => $value,
				'class' => $field_class. ' search_blur',
				'id' => 'search-query-textarea',
			));
			
			$search_or_span = $this->Html->tag('div', $search_or, array('class' => 'search_or search_field'));
			
			$search_button = $this->Form->submit(__('Search'), array(
				'div' => false,
			));
			
			
			$search_field_clear = $this->Form->input('f', array(
				'value' => false,
				'type' => 'hidden',
			));
			
			echo $this->Html->tag('div', $search_single_span. $search_or_span. $search_field_clear. $search_button, array('class' => 'field_holder '));
			
			
			echo $this->Form->input('ex', array(
				'div' => array('class' => 'switch_holder switch_first'),
				'label' => false,
				'type' => 'checkbox',
				'checked' => $ex_check,
				'class' => 'search_exclude',
			));
			
			if($search_primaryKey)
			{
				echo $this->Form->input('p', array(
					'div' => array('class' => 'switch_holder'),
					'label' => false,
					'type' => 'checkbox',
					'checked' => $primary_check,
					'class' => 'search_primary',
				));
			}
			
			if(isset($this->passedArgs['field']) and isset($this->passedArgs['value']))
			{
				echo $this->Form->input('field', array(
					'value' => $this->passedArgs['field'],
					'type' => 'hidden',
				));
				echo $this->Form->input('value', array(
					'value' => $this->passedArgs['value'],
					'type' => 'hidden',
				));
			}
			
			$buttons = '';
			$buttons .= $this->Html->link(__('Clear'), $clear_args, array('class' => 'button clear_button'));
			$buttons .= $this->Html->link(__('Advanced'), array('action' => 'search', $action), array('class' => 'button'));
			$buttons .= $this->Html->link(__('Permalink'), $permalink, array('class' => 'button permalink_button'));
			
			echo $this->Html->tag('div', $buttons, array('class' => 'button_holder'));
		?>
		</fieldset>
	<?php echo $this->Form->end(); ?>

</div>

<script type="text/javascript">

$(document).ready(function()
{
	$('div.form_search').objectSearch({
		sb_options: {
			on_label: '<?php echo _("Exclude"); ?>',
  			off_label: '<?php echo _("Include"); ?>',
  			clear: false
		},
		sb_primary_options: {
			on_label: '<?php echo _("Only ID"); ?>',
  			off_label: ''
		},
		cookieName: '<?php echo "ObjectSearch.$model.$action" ?>'
	});
});//ready 
</script>