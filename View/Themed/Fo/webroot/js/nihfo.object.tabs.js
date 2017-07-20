(function($) 
{
/**
 * Always attach to the table tag itself
 */
$.widget( "nihfo.objectTabs", $.nihfo.objectBase, 
{
	options: {},
	
	trackHistory: true,
	
	// initialize the element
	_create: function() 
	{
		var self = this;
		self._super();
		self.element.addClass( "nihfo-object-tabs" );
		self.refresh();
	},
	_destroy: function() 
	{
		var self = this;
		self._super();
		self.element.removeClass( "nihfo-object-tabs" );
	},
	
	refresh: function() 
	{
		var self = this;
		self.setOptions();
		self.hideStuff();
		self.attachUiTabs();
		self.attachTabs();
		self.trackTabLinks();
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
	
	attachTabs: function()
	{
	/* the new tabs */
		var self = this;
		
		if(self.options.useUiTabs)
			return true;
		
		// first make sure nothing is active/selected
		self.hideAllTabs();
		
		// watch the tabs for when they're clicked
		self.element.find('nav[role="tablist"] a[role="tab"]').on('click', function(event) {
			event.preventDefault();
			
			// the tab was actually clicked
			if(event.hasOwnProperty('originalEvent'))
			{
				self.showTab($(this).attr('id'), $(this).attr('href'));
			}
			// the tab's click event was triggered pragmatically
			else
			{
				self.showTab($(this).attr('id'));
			}
		});
		
		var initialTabActivated = false;
		var pageUrl = self.parseUrl();
		
		// check to see if we have an old tab link
		if(Object.keys(pageUrl.hash()).length)
		{
			var hash = Object.keys(pageUrl.hash())[0];
			// we have an hash tab
			if (/^ui-tabs/.test(hash))
			{
				var tab_index = hash.split('-')[2];
				var activeTab = self.element.find('nav[role="tablist"] a[role="tab"]:eq('+tab_index+')');
				if(activeTab.length)
				{
					activeTab.trigger('click');
					initialTabActivated = true;
				}
			}
		}
		
		// see if we need to load a tab
		var tabId = pageUrl.query('tab');
		if(tabId && !initialTabActivated)
		{
			// make sure this tab exists
			var activeTab = self.element.find('nav[role="tablist"] a[role="tab"]#'+tabId);
			if(activeTab.length)
			{
				self.trackHistory = false;
				activeTab.trigger('click');
				initialTabActivated = true;
			}
		}
		
		// otherwise load the first tab
		if(!initialTabActivated)
		{
			var firstTab = self.element.find('nav[role="tablist"] a[role="tab"]').first();
			if(firstTab)
				firstTab.trigger('click');
		}
	},
	
	showTab: function(tabId, newUrl, newDataType, newRequestType, newData)
	{
		var self = this;
		
		if(!tabId)
		{
			console.error('nihfo.objectTabs - showTab: the "tabId" is not set');
			return true;
		}
		
		// find this tab
		var tab = self.element.find('nav[role="tablist"] a[role="tab"]#'+tabId);
		if(!tab.length)
		{
			console.error('nihfo.objectTabs - showTab: unable to find the tab with tabId:'+tabId);
			return true;
		}
		
		var panelId = tab.attr("aria-controls");
		if(!panelId)
		{
			console.error('nihfo.objectTabs - showTab: the "panelId" is not set');
			return true;
		}
		var panel = self.element.find('div[role="panellist"] section[role="panel"]#'+panelId);
		if(!panelId)
		{
			console.error('nihfo.objectTabs - showTab: unable to find the panel with tabId:'+panelId);
			return true;
		}
		
		// first make sure nothing is active/selected
		self.hideAllTabs();
		
		// make the tab active
		tab.addClass('active');
		panel.attr("aria-hidden","false").removeClass('hidden');
		
		// update the url string
		var pageUrl = self.parseUrl();
		pageUrl.query('tab', tabId);
		
		if(self.trackHistory)
			pageUrl.updateHistory();
		else
			self.trackHistory = true; // only the initial load with the tab defined doesn't get stored in the history.
		
		// see if it's an ajax tab
		var url = newUrl||false;
		if(url)
		{
			panel.attr('data-current-url', url);
			panel.data("current-url", url);
		}
		else
		{
			url = panel.data("current-url");
		}
		
		// nothing to load, so we're done here
		if(!url) return true;
		
		// check to see if the url is a hash
		if (/^#/.test(url))
			return true;
		
		updatedUrlObj = self.parseUrl(url);
		updatedUrlObj.query('tab', tabId);
		url = updatedUrlObj.compiled();
		
		var requestType = newRequestType;
		if(!requestType)
			requestType = panel.data("requestType");
		if(!requestType)
			requestType = "GET";
		
		var dataType = newDataType;
		if(!dataType)
			dataType = panel.data("request-dataType");
		if(!dataType)
			dataType = "html";
		
		var ajaxData = false;
		if(newData)
			ajaxData = newData;
		
		panel.attr("data-ajax-state", "pending"); // track the ajax state on the panel
		
		$.ajax({
			type: requestType,
			dataType: dataType,
			url: url,
			data: ajaxData,
			beforeSend: function(jqXHR, settings) {
				panel.html(''); //clear the html
				panel.attr("data-ajax-state", "beforeSend");
				self.element.find('div.panel-loading').show();
				self.ajaxBeforeSend(jqXHR, settings);
			},
			success: function(data, textStatus, jqXHR) {
				panel.attr("data-ajax-state", "success");
				panel.html(data);
				self.ajaxSuccess(data, textStatus, jqXHR);
			},
			error: function(jqXHR, textStatus, errorThrown) {
				panel.attr("data-ajax-state", "error");
				panel.attr("data-ajax-textStatus", textStatus);
				panel.attr("data-ajax-errorThrown", errorThrown);
//				panel.html(jqXHR.responseText);
				self.ajaxError(jqXHR, textStatus, errorThrown);
			},
			done: function(data, textStatus, jqXHR) {
				self.ajaxDone(data, textStatus, jqXHR);
			},
			complete: function( jqXHR, textStatus ) {
				// no metter what the outcome, we need to hijack the content
				setTimeout(function() { // allow everything to get set
					self.element.find('div.panel-loading').hide();
					
					// remove the permalink in the search forms
					panel.find('form.tabform .permalink_button, form.advanced_search .permalink_button').remove();
					
					// hijack the paging links, the search forms' buttons, and any links specifically designated
					panel.find('.paging_link a, form.tabform a.button, .tab-hijack').on('click', function(event) {
						event.preventDefault(); 
						self.showTab(tabId, $(this).attr('href'));
					});
					
					// hijack the search forms
					panel.find('form.tabform, form.advanced_search').on('submit', function(event) {
						event.preventDefault(); 
						
						var formDataType = 'html';
						if($(this).data("request-dataType"))
							formDataType = $(this).data("request-dataType");
						
						var formRequestType = 'GET';
						if($(this).attr( 'method' ) == 'post')
							formRequestType = 'POST';
						
						var formData = $(this).serialize();
						
						self.showTab(tabId, $(this).attr('action'), formDataType, formRequestType, formData);
					});
					
					// look for and add the subscribe stuff to ones that don't have one
					var subscribeLink = panel.find('.top div.page-header a.subscribable');
					if(subscribeLink.length)
					{
						panel.parents('body').first().objectSite('attachSubscribe', subscribeLink);
					}
					
					panel.find("div.cachetime time").timeago();
				}, 50); // setTimeout 
				self.ajaxComplete(jqXHR, textStatus);
			}
		});
	},
	
	hideAllTabs: function()
	{
		var self = this;
		
		// deselect all of the tabs
		self.element.find('nav[role="tablist"] a[role="tab"]').attr("aria-selected","false").removeClass('active');
		// hide all of the panels
		self.element.find('div[role="panellist"] section[role="panel"]').attr("aria-hidden","true").addClass('hidden');
	},
	
	attachUiTabs: function()
	{
	/* the old tabs */
		var self = this;
		
		if(!self.options.useUiTabs)
			return true;
		
		var $tabs = self.element.tabs({
			cache:false, 
			load:function (event, ui) {
			
				// hijack the paging links
				$(ui.panel).delegate('.paging_link a', 'click', function(event) {
					event.preventDefault(); 
					$(ui.panel).load(this.href);
				});
			
				// hijack the search form
				$(ui.panel).delegate('form.tabform, .tabs form.advanced_search', 'submit', function(event) {
					event.preventDefault();
					// submit the form
					if($(this).attr( 'method' ) == 'post')
					{
						$.post($(this).attr( 'action' ), $(this).serialize(), function( data ) {
								$(ui.panel).html(data);
						});
					}
					else
					{
						$.get($(this).attr( 'action' ), $(this).serialize(), function( data ) {
								$(ui.panel).html(data);
						});
					}
				});
			
				// hijack the search form's clear button
				$(ui.panel).delegate('form.tabform a.button', 'click', function(event) {
					event.preventDefault(); 
					$(ui.panel).load(this.href);
				});
			
				// hijack links that are marked for hijacking
				$(ui.panel).delegate('.tab-hijack', 'click', function(event) {
					event.preventDefault(); 
					$(ui.panel).load(this.href);
				});
			}, 
			select:function (event, ui) {
				window.location.hash = ui.tab.hash;
			}
		});
		
		self.element.parents('body').find('a.tabslink').on('click', function(event) {
			event.preventDefault();
			
			if($(this).hasClass('ignore-me'))
				return false;
				
			var tab_index = $(this).attr("href").split('-')[1];
			
			// find the tab itself, and trigger a click
			var tabsMainElement = tabLink.parents('.nihfo-object-tabs');
			var tabLink = $('li[aria-controls="ui-tabs-'+tab_index+'"] a.ui-tabs-anchor');
			tabLink.trigger('click');
			var currentIndex = tabsMainElement.tabs("option","active");
			
			history.pushState(null, null, "#ui-tabs-"+tab_index);
		});
		
		var tabsAjaxTries = 0;
		// reload the selected tab if it fails on an ajax request
		$(document).ajaxError(function( event, jqxhr, settings, thrownError ) 
		{
			if(jqxhr.status == 403 && tabsAjaxTries < 15)
			{
				var current_index = self.element.tabs("option", "active");
				self.element.tabs('load',current_index);
				tabsAjaxTries++;
			}
		});
	
		self.element.on( "tabsactivate", function( event, ui ) {
				history.pushState(null, null, ui.newPanel.selector);
		});
	},
	
	trackTabLinks: function()
	{
		var self = this;
		self.element.find('ul.tabs-nav li.tabs-nav-item a').each(function(index){
			$(this).on('click', function(event){
				
				var current_index = self.element.tabs("option","active");
				
				// trigger a reload of this tab
				if(index == current_index)
				{
					self.element.tabs('load', current_index);
				}
			});
		});
	}
});

// the default options
$.nihfo.objectTabs.prototype.options = {
	id: false,
	tabObject: false,
	useUiTabs: true
}

})(jQuery);