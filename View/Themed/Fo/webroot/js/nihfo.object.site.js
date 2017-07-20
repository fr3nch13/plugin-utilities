(function($) 
{
/**
 * Always attach to the table tag itself
 */
$.widget( "nihfo.objectSite", $.nihfo.objectBase, 
{
	options: {},
	
	// initialize the element
	_create: function() 
	{
		var self = this;
		self._super();
		self.element.addClass( "nihfo-object-site" );
		self.refresh();
	},
	
	_destroy: function() 
	{
		var self = this;
		self._super();
		self.element.removeClass( "nihfo-object-site" );
	},
	
	refresh: function() 
	{
		var self = this;
		self.setOptions();
		self.hideStuff();
		self.fixStuff();
		self.attachObjects();
		self.trackAjax();
		self.element.find('.site').show();
		self.trackTop();
		self.getVersions();
		self.attachSubscribable();
	},
	
	setOptions: function() 
	{
		var self = this;
		self.options.id = self.element.attr('id');
	},
	
	hideStuff: function() 
	{
		$('.notification-javascript').hide();
		$('.notification-loading').hide();
	},
	
	trackAjax: function()
	{
		var self = this;
		$( document ).ajaxStop(function() {
			self.fixStuff();
		});
	},
	
	fixStuff: function()
	{
		var self = this;
		
		// remove any icons from the page options
		self.element.find('div.page_options a').addClass('no-icon');
		
		// we are now using Font Awesome (fortawesome/font-awesome in composer)
		self.element.find('.fa.fa-icon-only').each(function( index )
		{
			var text = $(this).text();
			if(text)
			{
				$(this).html('');
				$(this).attr('title', text);
			}
		});
		
		// add any qtip menus
		self.element.find('.qtip-menu span a').each(function( index )
		{
			var menuAnchor = $(this);
			menuAnchor.qtip({
				overwrite: false,
				show: { event: 'click' },
				hide: { distance: 300, event: 'unfocus', fixed: true },
				style: { classes: 'qtip-menu' },
				position: {
					my: 'top right',  // Position my top left...
					at: 'bottom right', // at the bottom right of...
					target: menuAnchor, // my target
					container: $(this).parent().parent()
				},
				content: {
					text: $(this).parent().next('ul') // Use the "div" element next to this for the content
				},
				events: {
					show: function(event, api) { 
						// find all of the other tool tips, and hide them
						self.element.find('.has-qtip').qtip('api').hide();
						
						menuAnchor.addClass('active'); 
					},
					hide: function(event, api) { menuAnchor.removeClass('active'); }
				}
			})
			.addClass('has-qtip')
			.on('click', function( event ){ event.preventDefault(); return false; });
		});
		
		// make sure an instance of the nihfo-ObjectForm is attached to each form DOM
		var formOptions = {};
		self.element.find('form').not('.nihfo-object-form').objectForm(formOptions);
	},
	
	attachObjects: function()
	{
		var self = this;
		self.element.find("ul.sf-menu").superfish();
		self.element.find("time.page-loaded").timeago();
		self.attachScrollToTop();
	},
	
	getVersions: function()
	{
		var self = this;
		var versionDiv = self.element.find('div.version-info');
		if(!versionDiv.length)
			return true;
		versionsUrl = versionDiv.attr('rel');
		
		self.ajax({
			type: 'GET',
			url: versionsUrl,
			dataType: 'json',
			success: function(data, textStatus, jqXHR) {
				if(!data.versions)
					return;
				var thisVersion = false;
				var thisTimestamp = false;
				$.each( data.versions, function( key, info ) {
					if(info.self)
					{
						thisVersion = info.version;
						thisTimestamp = new Date(info.timestamp * 1000).toISOString();
						return;
					}
				});
				if(thisVersion)
					versionDiv.find('.version-info-version span').html(thisVersion);
				if(thisTimestamp)
					versionDiv.find('.version-info-time time').attr('datetime', thisTimestamp).timeago();
			},
		});
	},
	
	trackTop: function()
	{
		var self = this;
		var topElement = self.element.find(".main-content > .top");
		if(!topElement.length)
			return true;
		var topPos = topElement.offset().top - parseFloat(topElement.css('marginTop').replace(/auto/, 0));
		
		$(window).scroll(function (event) {
			var y = $(this).scrollTop();
			if (y >= topPos) {
				// if so, add the fixed class
      			topElement.addClass('fixed');
      			// show the top holder so the content doesn't jump
      			self.element.find(".main-content > .top-holder").show().height(topElement.outerHeight());
			} else {
				// otherwise remove it
				topElement.removeClass('fixed');
				self.element.find(".main-content > .top-holder").hide();
			}
		});
	},
	
	attachScrollToTop: function()
	{
		$(window).scroll(function() 
		{
			if($(this).scrollTop() >= 50)
			{
				$('#return-to-top').fadeIn(200);
			}
			else
    		{
				$('#return-to-top').fadeOut(200);
			}
		});
		
		$('#return-to-top').click(function()
		{
			$('body,html').animate({
				scrollTop : 0
			}, 500);
		});
	},
	
	attachSubscribable: function()
	{
		var self = this;
		
		var subscribeLink = self.element.find('.top div.page-header a.subscribable');
		if(!subscribeLink)
			return true;
		
		return self.attachSubscribe(subscribeLink);
	},
	
	attachSubscribe: function ( subscribeLink )
	{
		var self = this;
		
		if(!subscribeLink)
			return true;
		
		var options = {
			subscribable: false,
			subscribeurl: false,
			subscribecheck: false,
			subscribetoggle: false,
		};
		
		if(subscribeLink.data('subscribable'))
			options.subscribable = subscribeLink.data('subscribable');
		
		if(subscribeLink.data('subscribeurl'))
			options.subscribeurl = subscribeLink.data('subscribeurl');
			
		if(subscribeLink.data('subscribecheck'))
			options.subscribecheck = subscribeLink.data('subscribecheck');
			
		if(subscribeLink.attr('href'))
			options.subscribetoggle = subscribeLink.attr('href');
		
		if(options.subscribable == false)
			return true;
		
		if(options.subscribeurl == false)
			return true;
		
		if(options.subscribecheck == false)
			return true;
		
		if(options.subscribetoggle == false)
			return true;
		
		// it already has the subscription stuff, bypass it
		if(subscribeLink.data('subscribeAttached'))
			return true;
		
		subscribeLink.attr('data-subscribeAttached', true);
		
		// check to see if this page has been subscribed to
		self.ajax({
			url: options.subscribecheck,
			type: 'POST',
			dataType: 'json',
			data: {subscribeurl: options.subscribeurl},
			success: function(data) {
				if(!data.success)
				{
					console.log(data);
					return true;
				}

				if(data.subscribed == 0)
				{
					subscribeLink.find('i.fa').removeClass('fa-envelope');
					subscribeLink.find('i.fa').addClass('fa-envelope-open');
					subscribeLink.attr('title', 'Subscribe to this page.');
				}
				else if(data.subscribed == 1)
				{
					subscribeLink.find('i.fa').removeClass('fa-envelope-open');
					subscribeLink.find('i.fa').addClass('fa-envelope');
					subscribeLink.attr('title', 'Unsubscribe to this page.');
				}
				else if(data.subscribed == 2)
				{
					subscribeLink.find('i.fa').removeClass('fa-envelope');
					subscribeLink.find('i.fa').addClass('fa-envelope-open');
					subscribeLink.attr('title', 'Resubscribe to this page.');
				}
				subscribeLink.attr('data-subscribed', data.subscribed);
				subscribeLink.show();
			}
		});
		
		// attach the toggle
		subscribeLink.on("click", function (event)
		{
			event.preventDefault();
		
			if(options.subscribetoggle == false)
				return true;
			
			var subscribed = subscribeLink.data('subscribed');
			var name = subscribeLink.parent().find('h1').text();
			// page subtitle
			var subtitle = subscribeLink.parents('.page-header').find('h2').text();
			if(subtitle)
				name = name+' - '+subtitle;
			// if it's a filtered/searched index page
			var nameAdd = subscribeLink.parents('.page-header').find('.search-title-query').text();
			if(nameAdd)
				name = name+' - '+nameAdd;
			
			var data = { 
				subscribeurl: options.subscribeurl, 
				subscribed: subscribed, 
				name: name,
				redirect: window.location.href,
			};
			
			self.ajax({
				url: options.subscribetoggle,
				type: 'POST',
				dataType: 'json',
				data: data,
				success: function(data) {
					if(!data.success)
					{
						console.log(data);
						return true;
					}
					if(data.redirect)
					{
						location.href = data.redirect;
						return false;
						}
					if(data.subscribed == 0)
					{
						subscribeLink.find('i.fa').removeClass('fa-envelope');
						subscribeLink.find('i.fa').addClass('fa-envelope-open');
						subscribeLink.attr('title', 'Subscribe to this page.');
					}
					else if(data.subscribed == 1)
					{
						subscribeLink.find('i.fa').removeClass('fa-envelope-open');
						subscribeLink.find('i.fa').addClass('fa-envelope');
						subscribeLink.attr('title', 'Unsubscribe to this page.');
					}
					else if(data.subscribed == 2)
					{
						subscribeLink.find('i.fa').removeClass('fa-envelope');
						subscribeLink.find('i.fa').addClass('fa-envelope-open');
						subscribeLink.attr('title', 'Resubscribe to this page.');
					}
					subscribeLink.attr('data-subscribed', data.subscribed);
				}
			});
			return false;
		});
	},
	
	checkSubscribable: function( id )
	{
		var self = this;
		
		if(self.options.subscribable == false)
			return true;
		
		if(self.options.subscribeurl == false)
			return true;
		
		if(self.options.subscribableUrl == false)
			return true;
		
		self.ajax({
			url: self.options.subscribableUrl+'/'+id,
			dataType: 'json',
			data: { subscribeurl: self.options.subscribeurl },
			success: function(data) {
				if(data.bookmarked)
				{
					self.element.find('#'+id+' a.subscribable.subscribable_remove').show();
					self.element.find('#'+id+' a.subscribable.subscribable_add').hide();
				}
				else
				{
					self.element.find('#'+id+' a.subscribable.subscribable_remove').hide();
					self.element.find('#'+id+' a.subscribable.subscribable_add').show();
				}
			}
		});
	}
});

// the default options
$.nihfo.objectSite.prototype.options = {
	subscribable: false,
	subscribeurl: false,
	subscribecheck: false,
	subscribeurlAdd: false,
	subscribeurlToggle: false

}

})(jQuery);