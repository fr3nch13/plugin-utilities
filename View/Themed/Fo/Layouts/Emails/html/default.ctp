<?php
/**
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.View.Layouts.Emails.html
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

$site_title = (isset($site_title)?$site_title:(Configure::read('Site.title')?Configure::read('Site.title'):' '));
$site_url = (isset($site_url)?$site_url:(Configure::read('Site.base_url')?Configure::read('Site.base_url'):Router::url('/', true)));
$this->Html->setFull(true);


// instructions to go with this email
$instructions = (isset($instructions)?$instructions:false);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
	<?php echo $this->Html->charset(); ?>
	<title>
		<?php echo $title_for_layout; ?>
	</title>
	<?php
		
		echo $this->fetch('meta'). "\n";
		
		$this->Html->setIncludeCss(true);
		echo $this->Html->css('overall');
		echo $this->Html->css('generic');
		echo $this->Html->css('content');
		echo $this->Html->css('email');
		echo $this->Html->css('jquery-ui');
		echo $this->Html->css('jquery.countdown');
		echo $this->Html->css('jquery.switchButton');
		echo $this->Html->css('jquery.spectrum');
		echo $this->Html->css('jquery.autocomplete');
		echo $this->Html->css('jquery.jOrgChart');
		// for qunit testing
//		if(Configure::read('debug'))
//			echo $this->Html->css('/assets/components/qunit/qunit');
		
		/// composer ones.
		echo $this->Html->css('/assets/nihfo-vendors/jquery-timepicker-addon/dist/jquery-ui-timepicker-addon');
//		echo $this->Html->css('/assets/nihfo-vendors/multiple-select/multiple-select');
		echo $this->Html->css('/assets/nihfo-vendors/bower-chosen/chosen');
		echo $this->Html->css('/assets/nihfo-vendors/dragtable/dragtable');
		echo $this->Html->css('/assets/nihfo-vendors/qtip2/dist/jquery.qtip');
		echo $this->Html->css('/assets/nihfo-vendors/bootstrap-markdown/css/bootstrap-markdown.min');
		echo $this->Html->css('/assets/fortawesome/font-awesome/css/font-awesome');
		
		if(CakePlugin::loaded('Tags'))
		{
			echo $this->Html->css('Tags.tags');
		}
		if(CakePlugin::loaded('Usage'))
		{
			echo $this->Html->css('Usage.usage');
		}
		echo $this->Html->css('superfish');
		echo $this->Html->css('superfish.style');
		echo $this->Html->css('iconize');
		if(CakePlugin::loaded('Upload'))
		{
			echo $this->Html->css('Upload.avatars');
		}
		if(CakePlugin::loaded('Filter'))
		{
			echo $this->Html->css('Filter.plugin.filter.general');
		}

		echo $this->Html->css('helper_auto_complete');
		
		///// begin using widgets to controll everything, instead of javascript included everywhere
		echo $this->Html->css('nihfo.object.base');
		echo $this->Html->css('nihfo.object.site');
		echo $this->Html->css('nihfo.object.message');
		echo $this->Html->css('nihfo.object.dashboard');
		echo $this->Html->css('nihfo.object.table');
		echo $this->Html->css('nihfo.object.tabs');
		echo $this->Html->css('nihfo.object.sections');
		echo $this->Html->css('nihfo.object.search');
		echo $this->Html->css('nihfo.object.global_search');
		echo $this->Html->css('nihfo.object.details');
		echo $this->Html->css('nihfo.object.stats');
		echo $this->Html->css('nihfo.object.pivot');
		echo $this->Html->css('nihfo.object.form');
		
		if($css_files = Configure::read('css_files'))
		{
			foreach($css_files as $css_file)
			{
				echo $this->Html->css($css_file);
			}
		}
		
		
		echo "\n<!-- this->fetch('css') -->\n";
		echo $this->fetch('css');
		echo "\n<!-- scripts_for_layout -->\n";
		echo $scripts_for_layout;
	?>
</head>
<body class="object-site">
	<div class="no-print"><?= $this->Html->tag('h4', __('Sent from %s - %s', $site_title, $this->Html->link($site_url))) ?></div>
	<?php if($instructions): ?>
	<div class="instructions">
		<h3><?php echo __('Instructions/Comments:'); ?></h3>
		<p><?php echo str_replace("\n", "\n<br />", $instructions) ?></p>
	</div>
	<div class="clearb"> </div>
	<?php endif; ?>
	
	
	<div id="site" class="site">
		<div id="header" class="site-header">
			<div id="site-content" class="site-content">
				<div id="site_title">
					<?php echo $this->Html->link($site_title, $site_url); ?>
				</div>
			</div>
		</div>
		
		<div id="body" class="main-body">
			<div id="body_content" class="content main-content">
				<?php 
				echo $this->fetch('content'); 
				// echo $content_for_layout;
				?>
			</div>
		</div>
	</div>
	<div class="clearb"> </div>
	<div id="footer">
		<div class="content">
			<?php 
			echo $this->Html->tag('h4', __('Sent from %s - %s', $site_title, $this->Html->link($site_url)));
			?>
		</div>
	</div>
</body>
</html>