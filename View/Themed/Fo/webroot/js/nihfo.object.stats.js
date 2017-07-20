(function($) 
{
/**
 * Attaches to the list of stats
 */
$.widget( "nihfo.objectStats", $.nihfo.objectBase, 
{
	options: {},
	
	// initialize the element
	_create: function() 
	{
		var self = this;
		self._super();
		self.element.addClass( "nihfo-object-stats" );
		self.refresh();
	},
	
	_destroy: function() 
	{
		var self = this;
		self._super();
		self.element.removeClass( "nihfo-object-stats" );
	},
	
	refresh: function() 
	{
		var self = this;
		self.setOptions();
		self.fixStuff();
		self.getAjaxCounts();
	},
	
	setOptions: function() 
	{
		var self = this;
		self.options.id = self.element.attr('id');
	},
	
	fixStuff: function() 
	{
		var self = this;
	},
	
	trackAjax: function()
	{
		var self = this;
		$( document ).ajaxStop(function() {
			self.fixStuff();
		});
	},
	
	getAjaxCounts: function()
	{
		var self = this;
		
		self.element.find('a.ajax-count').each(function( index )
		{
			var stat = $(this);
			
			// set the stat color
			if(stat.data('color-hex'))
			{
				stat.css( "color", stat.data('color-hex') );
			}
			if(stat.data('color-border'))
			{
				stat.css( "text-shadow", '-0.5px -0.5px 0 '+stat.data('color-border')+', 0.5px -0.5px 0 '+stat.data('color-border')+', -0.5px 0.5px 0 '+stat.data('color-border')+', 0.5px 0.5px 0 '+stat.data('color-border') );
			}
			
			// get the ajax count
			if(stat.data('count-url'))
			{
				self.ajax({
					type: 'GET',
					url: stat.data('count-url'),
					beforeSend: function(jqXHR, settings) {
						stat.html('...');
					},
					success: function(data, textStatus, jqXHR) {
						stat.html(data);
						stat.attr('title', 'Count: '+data);
					},
					error: function(jqXHR, textStatus, errorThrown) {
						var statHtml = 'ER';
						var statTitle = errorThrown;
						if(textStatus == 'abort')
						{
							statHtml = 'AB';
							statTitle = 'Request was aborted.';
						}
						else if(textStatus == 'error' && errorThrown == '')
						{
							statHtml = 'AB';
							statTitle = 'Request was aborted.';
						}
						else if(textStatus == 'error' && errorThrown == 'Not Found')
						{
							statHtml = 'NF';
							statTitle = 'Not Found';
						}
						
						stat.html(statHtml);
						stat.attr('title', statTitle);
					}
				});
			}
			
			// new tabs
			if(stat.attr('aria-controls'))
			{
				stat.on("click", function (event)
				{
					event.preventDefault();
					
					// find this tab
					var tabId = stat.attr('aria-controls');
					var tab = $('#'+tabId);
					if(!tab) return true;  // no tab exists on this page
					
					// find the panel
					var panelId = tab.attr('aria-controls');
					var panel = $('#'+panelId);
					if(!panel) return true;  // no panel exists on this page
					
					var href = (stat.attr('href')||false);
					if(href) // update the panel's url that it will load with this stat's url
					{
						panel.attr('data-current-url', href);
						panel.data("current-url", href);
					}
					tab.trigger('click');
				});
			}
			// old tabs
			else if(stat.data('tab-id'))
			{
				stat.on("click", function (event)
				{
					event.preventDefault();
					var href = (stat.attr('href')||false);
					if(href)
					{
						var tabIndex = stat.data('tab-id').split('-')[1];
						var tabLink = $('li[aria-controls="ui-tabs-'+tabIndex+'"] a.ui-tabs-anchor');
						if(tabLink.length)
						{
							self.ajaxAbortAll();
							tabLink.attr('data-stat-id', stat.attr('id'));
							tabLink.attr('href', href);
							tabLink.trigger('click');
							history.pushState(null, null, "#ui-tabs-"+tabIndex);
						}
					}
				});
			}
		});
	}
});

// the default options
$.nihfo.objectStats.prototype.options = {}

})(jQuery);