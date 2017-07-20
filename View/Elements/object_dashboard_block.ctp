<?php
$title = (isset($title)?$title:false);
$title_url = (isset($title_url)?$title_url:false);
$subtitle = (isset($subtitle)?$subtitle:false);
$description = (isset($description)?$description:false);
$page_options_title = (isset($page_options_title)?$page_options_title:__('Block Options'));
$page_options = (isset($page_options)?$page_options:array());
$page_options_title2 = (isset($page_options_title2)?$page_options_title2:__('More Options'));
$page_options2 = (isset($page_options2)?$page_options2:array());
$options = (isset($options)?$options:array());
$content = (isset($content)?$content:false);
$show_bookmark = (isset($show_bookmark)?$show_bookmark:true);

$uri = array(
	'base' => Configure::read('Site.base_url'),
	'plugin' => (isset($this->request->params['plugin'])?$this->request->params['plugin']:false),
	'prefix' => (isset($this->request->params['prefix'])?$this->request->params['prefix']:false),
	'controller' => (isset($this->request->params['controller'])?$this->request->params['controller']:false),
	'action' => (isset($this->request->params['action'])?$this->request->params['action']:false),
	'passedArgs' => array(),
);
foreach(Configure::read('Routing.prefixes') as $prefix)
{
	if(isset($this->request->params[$prefix]))
		$uri[$prefix] = $this->request->params[$prefix];
}
foreach($this->passedArgs as $k => $v)
{
	$uri['passedArgs'][$k] = $v;
}
?>
<div class="dashboard-block-wrapper"  data-href='<?= json_encode($uri); ?>'>
	<div class="dashboard-block-header">
		<div class="dashboard-block-title">
		<?php
			$title_html = $title;
			if($title_url)
				$title_html = $this->Html->link($title, $title_url);
			echo $title_html;
		?>
		</div>
		<div class="dashboard-block-options no-print">
			<?php if($show_bookmark): ?>
			<?= $this->Html->link('<i class="fa fa-bookmark-o fa-icon-only fa-fw"></i>', array('action' => 'db_myblock'), array('escape' => false, 'title' => __('Add to My Dashboard'), 'class' => 'bookmarker bookmarker_add')) ?>
			<?= $this->Html->link('<i class="fa fa-bookmark fa-icon-only fa-fw"></i>', array('action' => 'db_myblock'), array('escape' => false, 'title' => __('Remove from My Dashboard'), 'class' => 'bookmarker bookmarker_remove')) ?>
			<?php endif; ?>
			<?= ($description?$this->Html->link('<i class="fa fa-info-circle fa-icon-only fa-fw"></i>', '#', array('escape' => false, 'title' => __('Block Description'), 'class' => 'button-description')):'') ?>
			<?= $this->Html->link('<i class="fa fa-refresh fa-icon-only fa-fw"></i>', '#', array('escape' => false, 'title' => __('Refresh this Block'), 'class' => 'dashboard-block-refresh')) ?>
			<?= $this->Html->link('<i class="fa fa-eye-slash fa-icon-only fa-fw"></i>', '#', array('escape' => false, 'title' => __('Hide Block Content'), 'class' => 'button-hide')) ?>
			<?= $this->Html->link('<i class="fa fa-eye fa-icon-only fa-fw"></i>', '#', array('escape' => false, 'title' => __('Show Block Content'), 'class' => 'button-show')) ?>
			<i class="fa fa-arrows fa-icon-only fa-fw sort-handle" title="<?= __('Rearrange this Block') ?>"></i>
		</div>
	</div>
	
	<?php if($page_options and is_array($page_options)): ?>
	<div class="dashboard-block-content-options no-print">
	<?php 
		echo $this->element('Utilities.page_options', array('page_options' => $page_options, 'page_options_title' => $page_options_title)); 
		if($page_options2 and is_array($page_options2))
		{
			echo $this->element('Utilities.page_options', array('page_options' => $page_options2, 'page_options_title' => $page_options_title2)); 
		} ?>
	</div>
	<?php endif; ?>
	
	<?php if($subtitle): ?>
	<div class="dashboard-block-subtitle"><?php echo $subtitle; ?></div>
	<?php endif; ?>
	
	<div class="dashboard-block-description">
	<?php
		echo $description;
	?>
	</div>
	
	<div class="dashboard-block-content">
	<?= $content ?>
	</div>
</div>