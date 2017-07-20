(function($) 
{
/**
 * Always attach to the table tag itself
 */
$.widget( "nihfo.objectMessage", $.nihfo.objectBase, 
{
	options: {},
	
	// initialize the element
	_create: function() 
	{
		var self = this;
		self._super();
		self.element.addClass( "nihfo-object-message" );
		self.refresh();
	},
	_destroy: function() 
	{
		var self = this;
		self._super();
		self.element.removeClass( "nihfo-object-message" );
	},
	
	refresh: function() 
	{
		var self = this;
		self.setOptions();
		self.hideStuff();
	},
	
	setOptions: function() 
	{
		var self = this;
		self.options.id = self.element.attr('id');
	},
	
	hideStuff: function() 
	{
		$(".notification-flash").delay(6000).fadeOut('slow');
	},
	
	update: function(message, status)
	{
		var self = this;
		self.element.clearQueue();
		self.element.hide();
		messageType = 'default';
		if(status == 200)
			messageType = 'success';
		else if(status == 500)
			messageType = 'error';
		
		// clear our object of html
		self.element.html('');
		
		var messageHolder = $("<div></div>")
			.addClass('message')
			.addClass(messageType)
			.attr('id', 'flashMessage')
			.html(message);
		self.element.append(messageHolder);
		self.element.show();
		self.element.delay(6000).fadeOut('slow');
	}
});

// the default options
$.nihfo.objectMessage.prototype.options = {}

})(jQuery);