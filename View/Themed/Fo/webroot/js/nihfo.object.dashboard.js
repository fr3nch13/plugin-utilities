(function($) 
{
/**
 * Always attach to the table tag itself
 */
$.widget( "nihfo.objectDashboard", $.nihfo.objectBase, 
{
	options: {},
	
	// initialize the element
	_create: function() 
	{
		var self = this;
		self._super();
		self.element.addClass( "nihfo-object-dashboard" );
		self.refresh();
	},
	
	_destroy: function() 
	{
		var self = this;
		self._super();
		self.element.removeClass( "nihfo-object-dashboard" );
	},
	
	refresh: function() 
	{
		var self = this;
		self.setOptions();
		self.fixStuff();
		self.addShapeshift();
		self.loadBlocks();
	},
	
	setOptions: function() 
	{
		var self = this;
		self.options.id = self.element.attr('id');
	},
	
	afterAjaxUpdate: function(id)
	{
		var self = this;
		
		self.attachViewToggles(id);
		self.attachRefresher(id);
		self.watchForHighlight(id);
		self.hideBlockLoading(id);
		self.attacheBlockToggle(id);
		self.attacheBlockDescriptionToggle(id);
		self.attachBlockOptionsHijack(id);
		self.watchForLineChartToggle(id);
		self.element.find('#'+id).find("div.cachetime time").timeago();
		self.checkBookmarker(id);
		self.attachBookmarkers(id);
		self.updateShapeShift();
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
	
	addShapeshift: function()
	{
		var self = this;
		
		var url = self.parseUrl();
		var cookieName = 'dashboardSortable.'+url.attr('controller')+'.'+url.attr('action');
		var order = self.cookie.getJSON(cookieName) || {};
		var values = $.map(order, function(v) { return v; });
		
		if(values.length)
		{
			Object.keys(order).forEach(function (blockKey)
			{
				var block = self.element.find('#'+blockKey);
				block.remove();
				self.element.find('.dashboard-blocks').append(block);
			});
		}
		
		var shapeshifter = self.element.find('.dashboard-blocks').shapeshift({
			minColumns: 2,
			handle: '.sort-handle',
			gutterX: 5,
			gutterY: 5,
			paddingX: 0,
			paddingY: 0
		});
		
		shapeshifter.on('ss-rearranged', function(event, selected)
		{
			order = {};
			self.element.find('.dashboard-blocks .dashboard-block').each(function( index )
			{
				var block = $(this);
				order[block.attr('id')] = block.attr('id');
			});
			
			self.cookie.set(cookieName, order);
		});
	},
	
	sortable: function()
	{
		var self = this;
		var url = self.parseUrl();
		
		// dashboard-blocks
		var sortableOptions = {
			items: ".dashboard-block",
			connectWith: '.dashboard-column',
			handle: '.sort-handle',
			update: function (event, ui)
			{
				var order = {};
				self.element.find('.dashboard-blocks .dashboard-column').each(function( index )
				{
					var column = $(this)
					order[column.attr('id')] = {};
					$(this).find('.dashboard-block').each(function( index )
					{
						var block = $(this)
						order[column.attr('id')][block.attr('id')] = block.attr('id');
					});
				});
				
				self.cookie.set(cookieName, order);
			}
		};
		
		self.element.find('.dashboard-blocks .dashboard-column').each(function( index )
		{
			if(!$(this).attr('id'))
			{
				$(this).attr('id', 'dashboard-column-'+index);
			}
		});
		
		var cookieName = 'dashboardSortable.'+url.attr('controller')+'.'+url.attr('action');
		var order = self.cookie.getJSON(cookieName) || {};
		var values = $.map(order, function(v) { return v; });
		
		if(values.length)
		{
			Object.keys(order).forEach(function (columnKey)
			{
				var column = self.element.find('#'+columnKey);
				Object.keys(order[columnKey]).forEach(function (blockKey)
				{
					var block = self.element.find('#'+blockKey);
					block.remove();
					column.append(block);
				});
			});
		}
		
		self.element.find('.dashboard-blocks .dashboard-column').sortable(sortableOptions);
		
		// incase we loose this
		$(window).on('resize', function()
		{
				self.element.find('.dashboard-blocks .dashboard-column').each(function( index )
				{
					$(this).sortable(sortableOptions);
				});
		});
	},
	
	updateShapeShift: function()
	{
		var self = this;
		self.element.find('.dashboard-blocks').trigger("ss-rearrange");
	},
	
	loadBlocks: function()
	{
		var self = this;
		self.element.find('.dashboard-block').each(function( index )
		{
			var block = $(this);
			if(block.attr('href'))
			{
				self.showBlockLoading(block.attr('id'));
				self.attacheBlockToggle(block.attr('id'));
				self.ajax({
					url: block.attr('href'),
					dataType: 'html',
					success: function(data) {
						block.find('.dashboard-block-inside').html(data);
						self.afterAjaxUpdate(block.attr('id'));
					}
				});
			}
		});
	},
	
	attachViewToggles: function( id )
	{
		var self = this;
		
		self.element.find('#'+id+' a.button-hide').on("click", function (event)
		{
			event.preventDefault();
			$(this).hide();
			self.element.find('#'+id+' a.button-show').show();
			self.element.find('#'+id+' .dashboard-block-content').hide();
			
			return false;
		});
		
		self.element.find('#'+id+' a.button-show').on("click", function (event)
		{
			event.preventDefault();
			$(this).hide();
			self.element.find('#'+id+' a.button-hide').show();
			self.element.find('#'+id+' .dashboard-block-content').show();
			
			return false;
		});
	},
	
	attacheBlockDescriptionToggle: function( id )
	{
		var self = this;
		self.element.find('#'+id+' .dashboard-block-options a.button-description').on("click", function (event)
		{
			event.preventDefault();
			if($(this).hasClass('visible'))
			{
				$(this).removeClass('visible');
				self.element.find('#'+id+' .dashboard-block-description').hide();
				self.updateShapeShift();
			}
			else
			{
				$(this).addClass('visible');
				self.element.find('#'+id+' .dashboard-block-description').show();
				self.updateShapeShift();
			}
		});
	},
	
	attacheBlockToggle: function( id )
	{
		var self = this;
		var selector = self.element.find('.dashboard-options .dashboard-block-toggler select');
		
		var block = self.element.find('#'+id);
		var blockId = block.attr('id');
		var blockTitle = block.find('.dashboard-block-title').text().trim();
		
		// check to see if this block is listed as an option
		var option = selector.find("option[value='"+blockId+"']");
		if(option.length == 0)
		{
			var option = $('<option></option>')
				.val(blockId)
				.text(blockTitle)
				.appendTo(selector);
		}
		// update the title
		else
		{
			option.text(blockTitle);
		}
		
		selector.on('change', function(event)
		{
			event.preventDefault();
			var blockId = $(this).val();
			self.toggleBlock(blockId);
			$(this).val('');
		});
		
		if(!self.option('cookies'))
		{
			return true;
		}
		
		// check the cookie
		var url = self.parseUrl();
		var cookieName = 'DashboardBlocksToggle.'+url.attr('controller')+'.'+url.attr('action');
		var cookieVars = self.cookie.getJSON(cookieName) || {};
		
		// collapse any columns that are true in the cookieVars
		selector.find('option').each(function()
		{
			blockId = $(this).val();
			if(blockId)
			{
				if( blockId in cookieVars )
				{
					if(cookieVars[blockId] === true)
					{
						self.toggleBlock(blockId, true);
					}
				}
			}
		});
	},
	
	toggleBlock: function(value, forceToggle)
	{
		var self = this;
		
		if(!value)
			return true;
		
		var url = self.parseUrl();
		var cookieName = 'DashboardBlocksToggle.'+url.attr('controller')+'.'+url.attr('action');
		var cookieVars = self.cookie.getJSON(cookieName) || {};
		
		var selector = self.element.find('.dashboard-options .dashboard-block-toggler select');
		var option = selector.find('option[value="'+value+'"]');
		
		if(typeof forceToggle !== 'undefined')
		{
			if(forceToggle)
				option.removeClass('collapsed');
			else
				option.addClass('collapsed');
		}
		
		if(option.hasClass('collapsed'))
		{
			option.removeClass('collapsed');
			// update the cookie
			cookieVars[value] = false;
			// show the column
			self.element.find('.dashboard-blocks #' + value).show();
			self.updateShapeShift();
		}
		// collapse the column
		else
		{
			selector.addClass('collapsed');
			option.addClass('collapsed');
			// update the cookie
			cookieVars[value] = true;
			// hide the column
			self.element.find('.dashboard-blocks #' + value).hide();
			self.updateShapeShift();
			
		}
		
		// see if none are selected, then remove the collapsed from the selector
		if(!selector.find('option.collapsed').length)
		{
			selector.removeClass('collapsed');
		}
		
		self.cookie.set(cookieName, cookieVars);
	},
	
	attachBookmarkers: function( id, type )
	{
		var self = this;
		
		if(self.options.bookmarkerUrl == false)
			return true;
			
		if(!type)
			type = 'block';
		
		self.element.find('#'+id+' a.bookmarker').on("click", function (event)
		{
			event.preventDefault();
			var uri = jQuery.parseJSON($(this).parents('.dashboard-block-wrapper').attr("data-href"));
			var action = '';
			if($(this).hasClass('bookmarker_add'))
				action = 'add';
			else if($(this).hasClass('bookmarker_remove'))
				action = 'remove';
			
			var data = { key: id, action: action, uri: uri, type: type };
			
			self.ajax({
				url: self.options.bookmarkerUrl+'/'+id,
				type: 'POST',
				dataType: 'json',
				data: data,
				success: function(data) {
					if(data.bookmarked)
					{
						if(action == 'add')
						{
							self.element.find('#'+id+' a.bookmarker.bookmarker_remove').show();
							self.element.find('#'+id+' a.bookmarker.bookmarker_add').hide();
						}
						else if(action == 'remove')
						{
							self.element.find('#'+id+' a.bookmarker.bookmarker_remove').hide();
							self.element.find('#'+id+' a.bookmarker.bookmarker_add').show();
						}
					}
				}
			});
			return false;
		});
	},
	
	checkBookmarker: function( id )
	{
		var self = this;
		
		if(self.options.bookmarkerUrl == false)
			return true;
		
		self.ajax({
			url: self.options.bookmarkerUrl+'/'+id,
			dataType: 'json',
			data: { key: id },
			success: function(data) {
				if(data.bookmarked)
				{
					self.element.find('#'+id+' a.bookmarker.bookmarker_remove').show();
					self.element.find('#'+id+' a.bookmarker.bookmarker_add').hide();
				}
				else
				{
					self.element.find('#'+id+' a.bookmarker.bookmarker_remove').hide();
					self.element.find('#'+id+' a.bookmarker.bookmarker_add').show();
				}
			}
		});
	},
	
	attachRefresher: function( id )
	{
		var self = this;
		var block = self.element.find('#'+id);
		if(!block)
			return;
		
		block.find('a.dashboard-block-refresh').each(function(index)
		{
			var refreshUri = block.attr('href');
				refreshUri = refreshUri  + '?recache=1';
				
			$(this).attr('href', refreshUri);
			$(this).on("click", function (event)
			{
				event.preventDefault();
				self.showBlockLoading(block.attr('id'));
				
				self.ajax({
					url: refreshUri,
					dataType: 'html',
					success: function(data) {
						block.find('.dashboard-block-inside').html(data);
						self.afterAjaxUpdate(block.attr('id'));
					}
				});
			});
		});
	},
	
	attachBlockOptionsHijack: function( id )
	{
		var self = this;
		var block = self.element.find('#'+id);
		
		block.find('.dashboard-block-content-options a.block-hijack').on("click", function (event) 
		{
			event.preventDefault();
			self.showBlockLoading(block.attr('id'));
			self.ajax({
				url: $(this).attr('href'),
				dataType: 'html',
				success: function(data) {
					block.find('.dashboard-block-inside').html(data);
					self.afterAjaxUpdate(block.attr('id'));
				}
			});
		});
	},
	
	watchForLineChartToggle: function( id )
	{
		var self = this;
		var block = self.element.find('#'+id);
		
		var selector = block.find('.dashboard-chart-line-options select.dashboard-chart-line-toggle');
		
		selector.on('change', function(event)
		{
			event.preventDefault();
			var lineId = $(this).val();
			$(this).val('0');
			
			// find the line chart
			var googleChart = $(this).parents('.dashboard-block-content').find('.google_chart_svg');
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
	},
	
	watchForHighlight: function( id )
	{
		var self = this;
		var block = self.element.find('#'+id);
		block.find('.dashboard-stats .dashboard-stat').each(function( index )
		{
			$(this).on("click", function (event) 
			{
				event.preventDefault();
				
				$(this).parents('.dashboard-stats').find('.dashboard-stat').removeClass('highlighted');
				
				// find the pie chart
				var googleChart = $(this).parents('.dashboard-block-content').find('.google_chart_svg');
				if (!googleChart.length) 
					return false;
				var googleChartId = googleChart.attr('id');
				
				var googleChartObject = false;
				if(window.googleChartCharts[googleChartId])
					googleChartObject = window.googleChartCharts[googleChartId];
				
				if(!googleChartObject)
					return false;
				
				var pieIndex = false;
				if($(this).hasClass('pie-indexed'))
				{
					pieIndex = parseInt($(this).attr('data-pie-index'));
				}
				else
				{
					googleChartObject.getChart().selectedSlice = -1;
				}
				
				var selection = null;
				if (pieIndex !== false) 
				{
					selection = [{row: pieIndex, column: null}];
					
				}
				
				googleChartObject.getChart().setSelection(selection);
				if(typeof window.googleChartEventSelect[googleChartId] === 'function')
				{
					window.googleChartEventSelect[googleChartId]();
				}
				
			});
		});
	},
	
	showBlockLoading: function( id )
	{
		var self = this;
		var block = self.element.find('#'+id);
		block.find('.dashboard-block-loading').show();
	},
	
	hideBlockLoading: function( id )
	{
		var self = this;
		var block = self.element.find('#'+id);
		block.find('.dashboard-block-loading').hide();
	}
});

// the default options
$.nihfo.objectDashboard.prototype.options = {
	id: false,
	bookmarkerUrl: false
}

})(jQuery);