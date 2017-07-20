(function($) 
{
/**
 * 
 */
$.widget( "nihfo.objectGlobalSearch", $.nihfo.objectBase, 
{
	options: {},
	
	// initialize the element
	_create: function() 
	{
		this._super();
		this.element.addClass( "nihfo-object-global-search" );
		this.refresh();
	},
	_destroy: function() 
	{
		this._super();
		this.element.removeClass( "nihfo-object-global-search" );
	},
	
	refresh: function() 
	{
		var self = this;
		self.setOptions();
		self.hideStuff();
		self.attachEvents();
		self.watchClearButton();
	},
	
	setOptions: function() 
	{
		var self = this;
		this.options.id = this.element.attr('id');
	},
	
	hideStuff: function() 
	{
	},
	
	attachEvents: function()
	{
		var self = this;
		
/*
		self.element.find('input.search-term').addClass('half');
		self.element.find('textarea.search_input').on('blur', function(event){
			$( this ).switchClass( "search_focus", "search_blur");
		});
*/
	},
	
	watchClearButton: function()
	{
		var self = this;
		self.element.find('input.clear-button').on('click', function(event){
			event.preventDefault();
			var url = $(this).data('url');
			if(url)
			{
				location.href = url;
			}
		});
	}
});

// the default options
$.nihfo.objectGlobalSearch.prototype.options = {
}

})(jQuery);