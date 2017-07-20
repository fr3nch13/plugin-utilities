(function($) 
{
/**
 * Always attach to the table tag itself
 */
$.widget( "nihfo.objectSections", $.nihfo.objectBase, 
{
	options: {},
	
	// initialize the element
	_create: function() 
	{
		var self = this;
		self._super();
		self.element.addClass( "nihfo-object-sections" );
		self.refresh();
	},
	
	_destroy: function() 
	{
		var self = this;
		self._super();
		self.element.removeClass( "nihfo-object-sections" );
	},
	
	refresh: function() 
	{
		var self = this;
		self.setOptions();
		self.fixStuff();
		self.loadSections();
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
	
	loadSections: function()
	{
		var self = this;
		self.element.find('.sections-section').each(function( index )
		{
			var section = $(this);
			if(section.attr('href'))
			{
				self.ajax({
					url: section.attr('href'),
					dataType: 'html',
					success: function(data) {
						section.html(data);
						self.attachSectionOptionsHijack(section.attr('id'));
						self.watchForLineChartToggle(section.attr('id'));
					}
				});
			}
		});
	},
	
	attachSectionOptionsHijack: function( id )
	{
		var self = this;
		var section = self.element.find('#'+id);
		
		section.find('a.section-hijack').on("click", function (event) 
		{
			event.preventDefault();
			self.ajax({
				url: $(this).attr('href'),
				dataType: 'html',
				success: function(data) {
					section.html(data);
					self.attachSectionOptionsHijack(section.attr('id'));
				}
			});
		});
	},
	
	watchForLineChartToggle: function( id )
	{
		var self = this;
		var section = self.element.find('#'+id);
		
		var selector = section.find('.chart-line-options select.chart-line-toggle');
		
		selector.on('change', function(event)
		{
			event.preventDefault();
			var lineId = $(this).val();
			$(this).val('0');
			
			// find the line chart
			var googleChart = $(this).parents('.sections-section').find('.google_chart_svg');
			if (!googleChart.length) 
				return false;
			var googleChartId = googleChart.attr('id');
			
			var googleChartObject = false;
			if(window.googleChartCharts[googleChartId])
				googleChartObject = window.googleChartCharts[googleChartId];
			
			if(!googleChartObject)
				return false;
			
			var option = selector.find('option[value="'+lineId+'"]');
			if(option.hasClass('collapsed'))
			{
				option.removeClass('collapsed');
			}
			else
			{
				selector.addClass('collapsed');
				option.addClass('collapsed');
			}
			
			// see if none are selected, then remove the collapsed from the selector
			if(!selector.find('option.collapsed').length)
			{
				selector.removeClass('collapsed');
			}
			
			var optionShowIndex = [];
			
			selector.find('option').each(function(index)
			{
				if(!$(this).hasClass('collapsed'))
				{
					optionShowIndex.push($(this).index());
				}
			});
			
			googleChartObject.setView({'columns':  optionShowIndex});
			googleChartObject.draw();
		});
	}
});

// the default options
$.nihfo.objectSections.prototype.options = {
	id: false,
}

})(jQuery);