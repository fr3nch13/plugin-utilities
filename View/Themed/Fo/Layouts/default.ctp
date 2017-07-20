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
 * @copyright	 Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link		  http://cakephp.org CakePHP(tm) Project
 * @package	   Cake.View.Layouts
 * @since		 CakePHP(tm) v 0.10.0.1076
 * @license	   MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?><!DOCTYPE html>
<html>
<head>
	<?php echo $this->Html->charset(); ?>
	<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
	<!--nocache-->
	<title>
		<?php 
		$site_title = Configure::read('AppConfig.Site.title');
		if(!$site_title)
		{
			$site_title = Configure::read('Site.title');
		}
		echo $site_title;
		?>:
		<?php 
			$title_for_layout = (isset($title_for_layout)?$title_for_layout:false);
			echo $title_for_layout; 
		?>
	</title>
	<!--/nocache-->
	<?php
		echo $this->fetch('meta');
		
		echo $this->Html->css('overall');
		echo $this->Html->css('generic');
		echo $this->Html->css('content');
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
		echo $this->Html->css('nihfo.object.queue');
		
		if($css_files = Configure::read('css_files'))
		{
			foreach($css_files as $css_file)
			{
				echo $this->Html->css($css_file);
			}
		}
		
		if(Configure::read('debug') > 0)
		{
			echo $this->Html->css('sql_dump');
		}
		echo $this->fetch('css');
		
		// Composer ones
		echo $this->Html->script('/assets/components/jquery/jquery');
		echo $this->Html->script('/assets/components/jquery/jquery-migrate');
		echo $this->Html->script('/assets/components/jqueryui/ui/jquery-ui');
		echo $this->Html->script('/assets/components/jqueryui/ui/jquery.ui.widget');
		// for qunit testing
//		if(Configure::read('debug'))
//			echo $this->Html->script('/assets/components/qunit/qunit');
		echo $this->Html->script('/assets/nihfo-vendors/js-cookie/src/js.cookie');
		echo $this->Html->script('/assets/nihfo-vendors/jquery-timepicker-addon/dist/jquery-ui-timepicker-addon');
		echo $this->Html->script('/assets/nihfo-vendors/jquery-timepicker-addon/dist/jquery-ui-sliderAccess');
//		echo $this->Html->script('/assets/nihfo-vendors/multiple-select/jquery.multiple.select');
		echo $this->Html->script('/assets/nihfo-vendors/dragtable/jquery.dragtable');
		echo $this->Html->script('/assets/nihfo-vendors/jQuery-Autocomplete/dist/jquery.autocomplete');
//		echo $this->Html->script('/assets/nihfo-vendors/searchabledropdown/jquery.searchabledropdown.src');
		echo $this->Html->script('/assets/nihfo-vendors/bower-chosen/chosen.jquery');
		echo $this->Html->script('/assets/nihfo-vendors/qtip2/dist/jquery.qtip');
		echo $this->Html->script('/assets/nihfo-vendors/shapeshift/core/jquery.shapeshift');
		echo $this->Html->script('/assets/nihfo-vendors/bootstrap-markdown/js/markdown');
		echo $this->Html->script('/assets/nihfo-vendors/bootstrap-markdown/js/to-markdown');
		echo $this->Html->script('/assets/nihfo-vendors/bootstrap-markdown/js/bootstrap-markdown');
		echo $this->Html->script('/assets/rmm5t/jquery-timeago/jquery.timeago');
		
		echo $this->Html->script('crypto-js');
		echo $this->Html->script('nihfo.purl');
		echo $this->Html->script('jquery.plugin');
		echo $this->Html->script('jquery.countdown');
		echo $this->Html->script('jquery.ui.position');
		echo $this->Html->script('jquery.switchButton');
		echo $this->Html->script('jquery.parsecss');
		echo $this->Html->script('jquery.spectrum');
		echo $this->Html->script('superfish');
		echo $this->Html->script('helper_auto_complete');
		echo $this->Html->script('webtoolkit.base64');
		echo $this->Html->script('fo.custom.js');
		echo $this->Html->script('jquery.floatThead');
		echo $this->Html->script('jquery.numeric');
		echo $this->Html->script('jquery.jOrgChart');
		echo $this->Html->script('jquery.truncate');
		
		///// begin using widgets to control everything, instead of javascript included everywhere
		echo $this->Html->script('nihfo.object.base');
		echo $this->Html->script('nihfo.object.site');
		echo $this->Html->script('nihfo.object.message');
		echo $this->Html->script('nihfo.object.dashboard');
		echo $this->Html->script('nihfo.object.table');
		echo $this->Html->script('nihfo.object.tabs');
		echo $this->Html->script('nihfo.object.sections');
		echo $this->Html->script('nihfo.object.search');
		echo $this->Html->script('nihfo.object.global_search');
		echo $this->Html->script('nihfo.object.details');
		echo $this->Html->script('nihfo.object.stats');
		echo $this->Html->script('nihfo.object.pivot');
		echo $this->Html->script('nihfo.object.form');
		echo $this->Html->script('nihfo.object.queue');
		
		if(CakePlugin::loaded('Filter'))
		{
			echo $this->Html->script('Filter.plugin.filter.general');
		}
		
		if($js_files = Configure::read('js_files'))
		{
			foreach($js_files as $js_file)
			{
				echo $this->Html->script($js_file);
			}
		}
		
		echo $this->fetch('script');
	?>
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<script type="text/javascript">
	//<![CDATA[
		google.load("visualization", "1", {packages:['corechart', 'controls']});
	//]]>
	</script>
	
	<script type="text/javascript">
	//<![CDATA[
	$(document).ready(function ()
	{
		// slowely move the below javascript to the objectSite widget
		var objectSiteOptions = {
			subscribable: "<?= (isset($subscribable) and $subscribable?'1':'0'); ?>",
			subscribeUrl: '<?= $this->Html->permaLink() ?>',
			subscribeUrlCheck: '<?= $this->Html->url(["plugin" => "utilities", "prefix" => false, "controller" => "subscriptions", "action" => "check"]) ?>',
			subscribeUrlToggle: '<?= $this->Html->url(["plugin" => "utilities", "prefix" => false, "controller" => "subscriptions", "action" => "subscribe"]) ?>'
		};
		$('body').objectSite(objectSiteOptions);
			
		<?php if(isset($auth_timeout) and $auth_timeout): ?>
		// show the countdown for the session
		var authTimeout = new Date('<?php echo date("r", $auth_timeout); ?>');
		$('#auth_timeout').countdown({until: authTimeout, description: 'Session Timeout'});
		<?php endif; ?> 
			
		$.post(
			"<?php echo Router::url(array('controller' => 'proctimes', 'action' => 'proctime', 'plugin' => 'utilities', 'admin' => false)); ?>",
			{proctime: $("#proctime").text(), proctime_ajax: '{}'},  
			function(responseText)
			{
				$("#proctime_results").html(responseText);  
			},  
			"html"  
		);
	});
	//]]>
	</script>
	
</head>
<?php 
$userRole = false;
if(AuthComponent::user('id'))
{
	$userRole = AuthComponent::user('role');
}
?>
<body class="user-role-<?=$userRole?> object-site">
	
	<div id="no_javascript" class="notification notification-javascript">
		<?php echo __('Please enable javascript.'); ?>
	</div>
	
	<div id="loading" class="notification notification-loading">
		<i class="fa fa-spinner fa-spin"></i>
	</div>
	<!--nocache-->
	<div id="flash_wrapper" class="notification notification-flash">
		<?php echo $this->Flash->render(); ?>
	</div>
	<!--/nocache-->
	<a href="#header" id="return-to-top"><i class="fa fa-chevron-up"></i></a>
	<div id="site" class="site">
		<!--nocache-->
		<div id="header" class="site-header">
			<div id="site-content" class="site-content">
				<div id="site_title" class="site-title">
					<?php echo $this->Html->link($site_title, '/'); ?>
				</div>
			
				<div id="menu_user" class="user-menu">
					<?php
					// check to see if a user menu exists for this app
					// if not, then include the default one from this plugin
					$menu_user = $this->element('menu_user');
					$menu_user_message = false;
					
					// instead of false, you'll get an error message
					if (Configure::read('debug') > 0)
					{
						if(preg_match('/menu_user\.\w+/', $menu_user))
						{
							$menu_user_message =  $menu_user;
							$menu_user = false;
						}
					}
					
					if(!$menu_user )
					{
						$menu_user = $this->element('Utilities.menu_user_tmp');
					}
					echo $menu_user;
					?>
				</div>
				
			</div>
		</div>
		
		<div id="menu_main" class="main-menu">
			<div class="content">
				<?php
				// check to see if a main menu exists for this app
				// if not, then include the default one from this plugin
				
				$menu_main = $this->element('menu_main');
				$menu_main_message = false;
				
				// instead of false, you'll get an error message
				if (Configure::read('debug') > 0)
				{
					if(preg_match('/menu_main\.\w+/', $menu_main))
					{
						$menu_main_message =  $menu_main;
						$menu_main = false;
					}
				}
				
				if(!$menu_main )
				{
					$menu_main = $this->element('Utilities.menu_main_tmp');
				}
				echo $menu_main;
				echo $menu_main_message;
				?>
			</div>
		</div>
		<!--/nocache-->
		
		<div id="body" class="main-body">
			<div id="body_content" class="content main-content">
				<?php echo $this->fetch('content'); ?>
			</div>
		</div>
		<div class="clearb"> </div>
		
		<!--nocache-->
		<div id="footer">
			<div class="content">

<div class="hasCountdown_holder">
	<div id="auth_timeout"></div>
	<div style="clear:both;"> </div>
</div>
<div class="site-data no-print">
	<div class="times version-info" rel="<?php echo $this->Html->url(array('controller' => 'main', 'action' => 'versions', 'ext' => 'json', 'plugin' => 'utilities', 'prefix' => false, 'admin' => false, 'crm' => false)); ?>">
		<div class="times-loaded">
		<?php 
		echo __('Page Loaded: %s', $this->Html->tag('time', '', array(
			'title' => __('When you last loaded this page'),
			'class' => 'page-loaded',
			'datetime' => date('c'),
		))); 
		?>
		</div>
		<div class="version-info-version"><?php echo __('Version: '); ?><span></span></div>
		<div class="version-info-time">
		<?php echo __('Version Time: '); ?> <time></time>
		</div>
	</div>
	
	<?php if($this->Common->roleCheck(array('regular', 'saa', 'reviewer', 'admin'))): ?>
	<div id="proctime" class="ajax_data"><?php 
		$proctime_data = array(
			'proctime' => (microtime(true) - PROC_START),
			'url' => Router::url(null, true),
			'controller' => $this->params["controller"],
			'action' => $this->params["action"],
			'user_id' => AuthComponent::user('id'),
			'pid' => getmypid(),
		);
		echo json_encode($proctime_data);
	?></div>
	<div id="proctime_results" class="proctime_results"></div>
	<div class="ajax-times">
		<div><?php echo __('Ajax Start Time: ')?><span class="ajax-times-start">0</span></div>
		<div><?php echo __('Ajax End Time: ')?><span class="ajax-times-end">0</span></div>
		<div><?php echo __('Ajax Diff Time: ')?><span class="ajax-times-diff">0</span></div>
	</div>
	<div class="ajax-counts">
		<div><?php echo __('Total Started Ajax Counts: ')?><span class="ajax-counts-start">0</span></div>
		<div><?php echo __('Total Completed Ajax Counts: ')?><span class="ajax-counts-complete">0</span></div>
		<div><?php echo __('Total Successful Ajax Counts: ')?><span class="ajax-counts-success">0</span></div>
		<div><?php echo __('Total Failed Ajax Counts: ')?><span class="ajax-counts-error">0</span></div>
		<div><?php echo __('Total Done Ajax Counts: ')?><span class="ajax-counts-done">0</span></div>
		<div><?php echo __('Total Abort Ajax Counts: ')?><span class="ajax-counts-abort">0</span></div>
	</div>
	<?php endif; ?>
	<div class="clearb"> </div>
</div>
<?php 
if($this->Common->roleCheck(array('regular', 'saa', 'reviewer', 'admin')))
{
	$debug = Configure::read('debug');
	if(CakeSession::check('ProctimeInternal.debug_level'))
	{
		$debug = CakeSession::read('ProctimeInternal.debug_level');
	}
	if($debug)
	{
		echo $this->element('Utilities.sql_dump'); 
	}
}
?>
			</div>
			<div id="sessionTimeout"> </div>
		</div>
		<!--/nocache-->
	</div>
<?php
if($this->Common->roleCheck(array('regular', 'saa', 'reviewer', 'admin')))
{
	echo $this->element('Utilities.app_errors'); 
}
echo $this->Js->writeBuffer();
?>
</body>
</html>
