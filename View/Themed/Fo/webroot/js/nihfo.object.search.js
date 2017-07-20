(function($) 
{
/**
 * 
 */
$.widget( "nihfo.objectSearch", $.nihfo.objectBase, 
{
	options: {},
	
	// initialize the element
	_create: function() 
	{
		this._super();
		this.element.addClass( "nihfo-object-search" );
		this.refresh();
	},
	_destroy: function() 
	{
		this._super();
		this.element.removeClass( "nihfo-object-search" );
	},
	
	refresh: function() 
	{
		var self = this;
		self.setOptions();
		self.hideStuff();
		self.attachSwitchButtons();
		self.toggleSearchField();
		self.attachEvents();
	},
	
	setOptions: function() 
	{
		this.options.id = this.element.attr('id');
	},
	
	hideStuff: function() 
	{
	},
	
	attachEvents: function()
	{
		var self = this;
		
		self.element.find('textarea.search_input').on('focus', function(event){
			$( this ).switchClass( "search_blur", "search_focus");
		});
		self.element.find('textarea.search_input').on('blur', function(event){
			$( this ).switchClass( "search_focus", "search_blur");
		});
		self.element.find('a.clear_button').on('click', function(event){
			self.cookie.remove(self.options.cookieName);
			return true;
		});
		self.element.find('a.expand_contract').each(function(){
			var class_name = $( this ).parent().attr("class");
			$(this).on('click', function(event){
				event.preventDefault();
				
				// going to multiple search
				if($( this ).parent().hasClass('search_single'))
				{
					self.cookie.set(self.options.cookieName, true);
				}
				// going back to single
				else
				{
					self.cookie.remove(self.options.cookieName);
				}
				self.toggleSearchField();
			});
		});
			
	},
	
	attachSwitchButtons: function()
	{
		var self = this;
		self.element.find('input.search_exclude').each(function(){
			$(this).addClass( "nihfo-object-search-switch" );
			$(this).switchButton(self.options.sb_options);
		});
		self.element.find('input.search_primary').each(function(){
			$(this).addClass( "nihfo-object-search-switch" );
			$(this).switchButton(self.options.sb_primary_options);
		});
	},
	
	toggleSearchField: function()
	{
		var self = this;
		var search_or = self.cookie.get(self.options.cookieName) || false;
		
		if(search_or)
		{
			self.element.find('div.search_or').show();
			self.element.find('div.search_or textarea.search_input').removeAttr("disabled");
			self.element.find('div.search_single input.search_input').attr("disabled", "disabled");
			self.element.find('div.search_single').hide();
		}
		else
		{
			self.element.find('div.search_single').show();
			self.element.find('div.search_single input.search_input').removeAttr("disabled");
			self.element.find('div.search_or textarea.search_input').attr("disabled", "disabled");
			self.element.find('div.search_or').hide();
		}
	
	/*
	// see if we need to show/hide the areas
	var search_or = $.cookie(search_cookie_name);
	
	if(!search_or)
	{
	}
	else
	{
	}
	
	// observe the  single/or options
	$('div.form_search a.expand_contract').each(function(){
		var class_name = $( this ).parent().attr("class");
		$(this).on('click', function(event){
			event.preventDefault();
		
		// going to multiple search
		if(class_name == 'search_single')
		{
			$.cookie(search_cookie_name, 'true', { expires: 1, path: '/' });
			$('div.form_search span.search_or').show();
			$('div.form_search span.search_or textarea').removeAttr("disabled");
			$('div.form_search span.search_single search').attr("disabled", "disabled");
			$('div.form_search span.search_single').hide();
		}
		
		// back to single search
		else
		{
			$.removeCookie(search_cookie_name, { expires: 1, path: '/' });
			$('div.form_search span.search_single').show();
			$('div.form_search span.search_single search').removeAttr("disabled");
			$('div.form_search span.search_or textarea').attr("disabled", "disabled");
			$('div.form_search span.search_or').hide();
		}
		
		return false;
	}); // 'div.form_search a.expand_contract'
	});
*/
	}
});

// the default options
$.nihfo.objectSearch.prototype.options = {
	cssFile: 'nihfo.object.search.css',
	cookieName: false,
	sb_options: {
		on_label: 'Exclude',
  		off_label: 'Include',
  		clear: false
	},
	sb_primary_options: {
		on_label: 'Exclude',
  		off_label: 'Include',
  		clear: false
	}
}

})(jQuery);