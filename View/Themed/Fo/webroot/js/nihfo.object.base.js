(function($) 
{

$.widget( "nihfo.objectBase", 
{
	options: {},
	
	_create: function() 
	{
		this.element.addClass( "nihfo-object" );
		this.options =  $.extend( {}, $.nihfo.objectBase.prototype.options, this.options );
		this.ajaxSetup();
		this.urlSetup();
		this.cookieSetup();
		// find the different possible elements we are apart of
		this.options =  $.extend( {}, $.nihfo.objectBase.prototype.options, this.options );
		this.attachBaseObjects();
		this.addTopProgressBar();
		
		return this;
	},
	
	_destroy: function() 
	{
		this.element.removeClass( "nihfo-object" )
	},
	
	ajaxSetup: function() 
	{
		var self = this;
		
		var startTime = 0;
		// force all ajax calls to show/hide loading
		$( document ).ajaxStart(function() 
		{
			$( "#loading" ).show();
			window.ajaxstartTime = Date.now();
			$('div.ajax-times span.ajax-times-start').html(window.ajaxstartTime);
			console.time('ajaxTime');
			$(".nihfo-object-site").find(".nihfo-object-top-progressbar").show();
		});
		$( document ).ajaxStop(function() 
		{
			$( "#loading" ).hide();
			if(window.ajaxstartTime)
			{
				window.ajaxendTime = Date.now();
				window.ajaxdiffTime = window.ajaxendTime - window.ajaxstartTime;
				$('div.ajax-times span.ajax-times-end').html(window.ajaxendTime);
				$('div.ajax-times span.ajax-times-diff').html(window.ajaxdiffTime);
			}
			
			$(".nihfo-object-site").find(".nihfo-object-top-progressbar").hide();
			console.timeEnd('ajaxTime');
		});
	},
	
	urlSetup: function() 
	{
		if(!this.url)
			this.url = $.url;
		
		this.options.here = this.url(window.location);
	},
	
	cookieSetup: function() 
	{
		var url = this.parseUrl();
		
		this.options.cookies.path = url.attr('base');
		this.options.cookies.domain = url.attr('host');
		
		Cookies.defaults = this.options.cookies;
		this.cookie = Cookies;
	},
	
	attachBaseObjects: function()
	{
		if(!this.option('objectMessageObject'))
			this._setOption('objectMessageObject', $(".notification-flash"));
		this.options.objectMessageObject.objectMessage();
		
		if(!this.option('objectTabsObject'))
		{
			this._setOption('objectTabsObject', this.element.parents('.nihfo-object-tabs'));
		}
		this.element.find("div.cachetime time").timeago();
	},
	
	updateHashToLinks: function() 
	{
		var self = this;
		var hash = window.location.hash;
		
		var url = self.parseUrl(); // url in the location bar
		
	// tracks all links. when the link is pushed, add the # (hash) from the url, as a paramater hash:
		$(document).find('a').each(function(){
			var href = $(this).attr('href');
			var url = self.parseUrl(href);
			
			if(!url.attr('directory'))// a local link like "#overview"
				return true;
			hash = url.hashCompiled();
			if(hash)
				url.named('hash', hash);
			else
				url.named('hash', false);
			
			$(this).attr('href', url.compiled());
			
//			if(parsedUrl.named('directory'));
			
			
/*
			if (/^#/.test(href)) 
				return true;
			
			if(hash)
			{
				
				var href_parts = href.split('#');
				if(href_parts[1])
					href_parts[2] = href_parts[1];
			
				// check if href already has a hash variable
				if (/\/hash\:/.test(href))
				{
				}
				//
				else
				{
					// add the has to the href
					if (! /\/$/.test(href)) 
						href+'/';
					href+'hash:'+hash;
				}
				$(this).attr('href', href);		
			}
*/
		});
	},
	
	parseUrl: function( url ) 
	{
		if(typeof url == 'string')
			return this.url(url);
		else if(typeof url == 'object')
			return $(url).url();
		else
			return this.url();
	},
	
	ajax: function(ajax_options) 
	{
		var self = this;
		
		ajax_options = $.extend( this.options.ajaxOptions, ajax_options );
		
		beforeSendOption = ajax_options.beforeSend;
		ajax_options.beforeSend = function( jqXHR, settings ) 
		{
			self.ajaxBeforeSend(jqXHR, settings); 
			if(beforeSendOption)
			{
				beforeSendOption(jqXHR, settings); 
			}
		}
		
		jqxhr = $.ajax(ajax_options);
		
//		jqxhr.beforeSend();
		jqxhr.complete(function( jqXHR, textStatus ) { self.ajaxComplete(jqXHR, textStatus); });
		jqxhr.success(function( data, textStatus, jqXHR ) { self.ajaxSuccess(data, textStatus, jqXHR); });
		jqxhr.error(function( jqXHR, textStatus, errorThrown ) { self.ajaxError(jqXHR, textStatus, errorThrown); });
		jqxhr.done(function(data, textStatus, jqXHR){ self.ajaxDone(data, textStatus, jqXHR); });
		
		ajax_options.beforeSend = beforeSendOption;
		
		return jqxhr;
	},
	
	ajaxBeforeSend: function(jqXHR, settings)
	{
		var self = this;
		// track them so I can kill them if needed
		self.options.xhrPool.push(jqXHR);
		var self = this;
		if(window.ajaxTotalStartCount === undefined)
		{
			window.ajaxTotalStartCount = 0;
		}
		window.ajaxTotalStartCount++;
		$('div.ajax-counts span.ajax-counts-start').html(window.ajaxTotalStartCount);
		return true;
	},
	
	ajaxComplete: function(jqXHR, textStatus)
	{
		var self = this;
		if(window.ajaxTotalCompleteCount === undefined)
		{
			window.ajaxTotalCompleteCount = 0;
		}
		window.ajaxTotalCompleteCount++;
		$('div.ajax-counts span.ajax-counts-complete').html(window.ajaxTotalCompleteCount);
		self.updateTopProgressBar();
	},
	
	ajaxSuccess: function(data, textStatus, jqXHR)
	{
		var self = this;
		if(window.ajaxTotalSuccessCount === undefined)
		{
			window.ajaxTotalSuccessCount = 0;
		}
		window.ajaxTotalSuccessCount++;
		$('div.ajax-counts span.ajax-counts-success').html(window.ajaxTotalSuccessCount);
	},
	
	ajaxDone: function(data, textStatus, jqXHR)
	{ 
		var self = this;
		var i = self.options.xhrPool.indexOf(jqXHR);
		self.options.xhrPool.splice(i, 1);
		this.ajaxCheckSession(textStatus);
		this.objectMessageObject_update(data, jqXHR);
		
		if(window.ajaxTotalDoneCount === undefined)
		{
			window.ajaxTotalDoneCount = 0;
		}
		window.ajaxTotalDoneCount++;
		$('div.ajax-counts span.ajax-counts-done').html(window.ajaxTotalDoneCount);
	},
	
	ajaxError: function(jqXHR, textStatus, errorThrown)
	{
		var self = this;
		var i = self.options.xhrPool.indexOf(jqXHR);
		self.options.xhrPool.splice(i, 1);
		this.ajaxCheckSession(textStatus, errorThrown);
		this.objectMessageObject_update(false, jqXHR);
		
		if(window.ajaxTotalErrorCount === undefined)
		{
			window.ajaxTotalErrorCount = 0;
		}
		window.ajaxTotalErrorCount++;
		$('div.ajax-counts span.ajax-counts-error').html(window.ajaxTotalErrorCount);
	},
	
	ajaxAbortAll: function() 
	{
		var self = this;
		
		if(window.ajaxTotalAbortCount === undefined)
		{
			window.ajaxTotalAbortCount = 0;
		}
		window.ajaxTotalAbortCount = self.options.xhrPool.length;
		$('div.ajax-counts span.ajax-counts-abort').html(window.ajaxTotalAbortCount);
		
		$.each(self.options.xhrPool, function(i, jqXHR) {
			if(jqXHR)
				jqXHR.abort();
			self.options.xhrPool.splice(i, 1);
		});
	},
	
	ajaxCheckSession: function(textStatus, errorThrown) 
	{
		try {
			if(textStatus == 403)
			{
				location.reload();
			}
		}
		// returned regular html content
		catch(error) {}
		
		if(errorThrown == 'Forbidden')
			location.reload();
	},
	
	objectMessageObject_update: function(data, jqXHR) 
	{
		var flashMessage = false;
		var flashType = jqXHR.status;
		if(data && typeof data == 'string') {
			try {
				data = JSON.parse(data);
			}
			// returned regular html/text content
			catch(error) {
			}
		}
		if(typeof(data.message) !== 'undefined')
		{
			flashMessage = data.message
		}
		
		if(!flashMessage && typeof(data.results) !== 'undefined' && typeof(data.results.message) !== 'undefined')
		{
			flashMessage = data.results.message
		}
		
		if(!flashMessage && jqXHR.responseText)
		{
			responseJson = {message: false};
			try {
				responseJson = $.parseJSON(jqXHR.responseText);
			}
			// returned regular html/text content
			catch(error) {
			}
			
			if(responseJson.message)
				flashMessage = responseJson.message;
		}
		if(flashMessage)
			if(this.option('objectMessageObject'))
				this.option('objectMessageObject').objectMessage('update', flashMessage, flashType);
	},
	
	snapOnScroll: function( element )
	{
	// snaps an element to the top of the page when scrolled past
	// otherwise releases if the scroll goes above the original position
		element_top = element.offset();
		element_top = Math.ceil(element_top.top);
		$(window).scroll(function () {
			var d = $(document).scrollTop();
			
			if(d >= element_top)
				element.addClass('stuck');
			else
				element.removeClass('stuck');
		});
	},
	
	addTopProgressBar: function()
	{
		var self = this;
		var siteElement = $(".nihfo-object-site");
		if(!siteElement.length)
			return true;
		
		var progressBarElement = siteElement.find(".nihfo-object-top-progressbar");
		if(progressBarElement.length)
			return true;
		
		var progressBarElement = $('<div></div>')
			.addClass('nihfo-object-top-progressbar');
		var progressLabelElement = $('<div></div>')
			.addClass('nihfo-object-top-progressbar-label')
			.html('Loading...')
			.appendTo(progressBarElement);
		
		progressBarElement.progressbar({
			value: false,
			change: function() {
				progressLabelElement.text( progressBarElement.progressbar( "value" ) + "%" );
			},
			complete: function() {
				progressLabelElement.text( "Complete!" );
				progressBarElement.hide();
			}
		});
		
		siteElement.prepend(progressBarElement);
	},
	
	updateTopProgressBar: function()
	{
		var self = this;
		var progressBarElement = $(".nihfo-object-site > .nihfo-object-top-progressbar");
		if(!progressBarElement.length)
			return true;
		
		var progressLabel = progressBarElement.find( ".nihfo-object-top-progressbar-label" );
		
		window.ajaxTotalStartCount
		
		window.ajaxTotalCompleteCount
		
		var percent = (window.ajaxTotalCompleteCount / window.ajaxTotalStartCount * 100);
		percent = Math.ceil(percent);
		
		progressBarElement.show();
		progressBarElement.progressbar( "value", percent );
	}
});

// the default options
$.nihfo.objectBase.prototype.options = {
	id: false,
	here: false,
	cssFile: 'nihfo.object.base.css',
	objectMessageObject: false,
	objectTabsObject: false,
	tooltipsterObject: false,
	ajaxOptions: {
		type: 'GET',
		dataType: 'html',
		beforeSend: false,
		async: true,
	},
	cookies: {
		expires: 365
	},
	xhrPool: []
}

})(jQuery);