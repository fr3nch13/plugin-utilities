(function($) 
{
/**
 * Always attach to the details tag itself
 */
$.widget( "nihfo.objectDetails", $.nihfo.objectBase, 
{
	options: {},
	
	// initialize the element
	_create: function() 
	{
		var self = this;
		self._super();
		self.element.addClass( "nihfo-object-details" );
		self.refresh();
	},
	_destroy: function() 
	{
		var self = this;
		self._super();
		self.element.removeClass( "nihfo-object-details" );
	},
	
	refresh: function() 
	{
		var self = this;
		self.setOptions();
		self.fixStuff();
		self.attachViewToggles();
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
	
	attachViewToggles: function( id )
	{
		var self = this;
		
		if(!self.options.viewToggles)
			return true;
		
		var optionDiv = $("<div></div>")
			.addClass('details-options')
			.addClass('no-print');
		
		var buttonHide = $('<a></a>')
				.attr('href', '#')
				.text('Hide Details')
				.addClass('button-hide')
				.appendTo(optionDiv);
		
		var buttonHide = $('<a></a>')
				.attr('href', '#')
				.text('Show Details')
				.addClass('button-show')
				.appendTo(optionDiv);
		
		optionDiv.prependTo(self.element);
		
		self.element.find('.details-options a.button-show').hide();
		
		self.element.find('.details-options a.button-hide').on("click", function (event)
		{
			event.preventDefault();
			$(this).hide();
			self.element.find('.details-options  a.button-show').show();
			self.element.find('.details-content').hide();
			
			return false;
		});
		
		self.element.find('.details-options a.button-show').on("click", function (event)
		{
			event.preventDefault();
			$(this).hide();
			self.element.find('.details-options  a.button-hide').show();
			self.element.find('.details-content').show();
			
			return false;
		});
	}
});

// the default options
$.nihfo.objectDetails.prototype.options = {
	viewToggles: true,
}

})(jQuery);