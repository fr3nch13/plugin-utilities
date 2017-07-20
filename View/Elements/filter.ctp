<?php
// app/View/Elements/search.ctp

// Provides a Search box for the site

// set defaults

$table_id = (isset($table_id)?$table_id:false);
$placeholder = (isset($placeholder)?$placeholder:strtolower($this->params->controller));


	if(stripos($placeholder, '_'))
	{
		$plparts = explode('_', $placeholder);
		$plparts[0] = Inflector::singularize($plparts[0]);
		$placeholder = implode(' ', $plparts);
	}
	$placeholder = __('Filter Displayed %s here', ucwords($placeholder));
?>
<div class="form_filter">
<?php
	echo $this->Form->input('filter', array(
		'div' => false,
		'label' => false,
		'id' => $table_id.'-filter',
		'type' => 'search',
		'placeholder' => $placeholder,
	));
?>
</div>

<script type="text/javascript">

$(document).ready(function()
{
// make filtering insensative to case
// NEW selector
jQuery.expr[':'].Contains = function(a, i, m) {
  return jQuery(a).text().toUpperCase()
      .indexOf(m[3].toUpperCase()) >= 0;
};

// OVERWRITES old selecor
jQuery.expr[':'].contains = function(a, i, m) {
  return jQuery(a).text().toUpperCase()
      .indexOf(m[3].toUpperCase()) >= 0;
};
	$('input#<?php echo $table_id."-filter"; ?>').keyup(function() {
				var rows = $('table#<?php echo $table_id ?>').find("tbody tr").hide();
				var data = this.value.split(" ");
				$.each(data, function(i, v) {
					rows.filter(":contains('" + v + "')").show();
				});
			});
});//ready 
</script>