<?php

$sections_id = (isset($sections_id)?$sections_id:'object-sections-'. rand(1,1000));

$this->start('page_content'); 

$content = array();
foreach($this->get('page_sections', array()) as $page_section_id => $page_section_url)
{
	if(is_array($page_section_url))
		$page_section_url = $this->Html->url($page_section_url);
	$content[] = $this->Html->tag('div', '', array('class' => 'sections-section', 'href' => $page_section_url, 'id' => $page_section_id));
}
echo $this->Html->tag('div', implode("\n", $content), array('class' => 'object-sections', 'id' => $sections_id));
?>

<script type="text/javascript">
//<![CDATA[
$(document).ready(function ()
{
	var sectionsOptions = {};
	
	$('div#<?php echo $sections_id; ?>').objectSections(sectionsOptions);
});
//]]>
</script>
<?php
$this->end();
$this->set('page_content', $this->fetch('page_content'));

echo $this->extend('Utilities.page_generic');
