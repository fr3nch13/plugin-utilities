(function($) 
{
/**
 * Always attach to the div tag that is the parent to the unordered list
 */
$.widget( "nihfo.objectPivot", $.nihfo.objectBase, 
{
	options: {},
	
	// initialize the element
	_create: function() 
	{
		var self = this;
		self._super();
		self.element.addClass( "nihfo-object-pivot" );
		self.refresh();
	},
	
	_destroy: function() 
	{
		var self = this;
		self._super();
		self.element.removeClass( "nihfo-object-pivot" );
	},
	
	refresh: function() 
	{
		var self = this;
		self.setOptions();
		self.hideStuff();
		self.attachObjects();
	},
	
	setOptions: function() 
	{
		var self = this;
		self.options.id = self.element.attr('id');
	},
	
	hideStuff: function() 
	{
		var self = this;
	},
	
	attachObjects: function()
	{
		var self = this;
		
		if(self.options.orgChart)
		{
			self.element.find('> ul').jOrgChart({chartElement: self.element});
			self.element.find('> ul').hide();
		}
	}
});

// the default options
$.nihfo.objectPivot.prototype.options = {
	id: false,
	orgChart: false
}

})(jQuery);