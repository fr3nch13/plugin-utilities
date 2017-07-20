(function($) 
{
/**
 * Always attach to the table tag itself
 */
$.widget( "nihfo.objectTable", $.nihfo.objectBase, 
{
	options: {},
	
	// initialize the element
	_create: function() 
	{
		this._super();
		this.element.addClass( "nihfo-object-table" );
		this.refresh();
	},
	
	_destroy: function() 
	{
		this._super();
		this.element.removeClass( "nihfo-object-table" );
	},
	
	table: false,
	
	refresh: function() 
	{
		var self = this;
		self.table = self.element.find('table.actual-table');
		self.setOptions();
		self.hideStuff();
		self.uniqueIds();
		self.setDividers();
		self.iconize();
		self.attachDragtable();
		self.attachMultiselectTriggers();
		self.attacheMultiselectSubmit();
		self.attachRowHovering();
		self.attachColumnHovering();
		self.attachRowHighlighting();
		self.attachTableScrolling();
		self.attachJsExporting();
		self.attacheCollapsibleColumns();
		self.attachFloatHead();
		self.attachGridEditing();
		self.attachSortable();
		self.attachJsOrdering();
		self.attachJsSearch();
		self.attachTextareas();
		self.watchToggles();
		self.watchMiniViews();
		self.setColors();
		self.attachAjaxReload();
		
		// if we have a startup instance to run
		if(typeof self.options.setup != 'function')
		{
			var setupOption = self.options.setup;
			self.options.setup = function(self){ eval(setupOption); }
		}
		self.options.setup(self);
	},
	
	setOptions: function() 
	{
		var self = this;
		self.options.id = self.element.attr('id');
		
		var controller = self.options.here.attr('controller');
		var action = self.options.here.attr('action');
		
		self.options.orderingCookieName = 'jsordering_'+CryptoJS.MD5(self.table.attr('id')).toString();
	},
	
	hideStuff: function() 
	{
		var self = this;
	},
	
	uniqueIds: function() 
	{
		var self = this;
		var table_id = this.element.attr('id');
	
		// give each row an unique id
		var trid = 0;
		self.element.find('tr').each(function()
		{
			if(!$(this).attr('id'))
			{
				var this_trid = table_id+'_'+trid;
				$(this).attr('id', this_trid);
				trid++;
			}
		});
	},
	
	setDividers: function()
	{
		var self = this;
		self.table.find('th a.divider').closest('th').addClass('divider');
	},
	
	iconize: function()
	{
		// we are now using Font Awesome (fortawesome/font-awesom in composer)
		var self = this;
		
		// transform all of the edit icons
		var texts = ['edit', 'delete', 'view', 'save', 'cancel', 'inline edit', 'yes', 'no', 'download', 'add', 'add new item', 'rescan'];
		self.table.find('td.actions a').each(function(index)
		{
			var text = $(this).text().toLowerCase();
			if(jQuery.inArray( text, texts ) > -1)
			{
				$(this).addClass('fa');
				
				$(this).attr('title', $(this).text());
				$(this).html('');
				
				if(text == 'edit')
					$(this).addClass('fa-pencil');
				
				if(text == 'view')
					$(this).addClass('fa-eye');
				
				if(text == 'save')
					$(this).addClass('fa-floppy-o');
				
				if(text == 'delete')
					$(this).addClass('fa-trash');
				
				if(text == 'cancel')
					$(this).addClass('fa-ban');
				
				if(text == 'inline edit')
					$(this).addClass('fa-pencil-square-o');
				
				if(text == 'yes')
					$(this).addClass('fa-check');
				
				if(text == 'no')
					$(this).addClass('fa-remove');
				
				if(text == 'download')
					$(this).addClass('fa-download');
				
				if(text == 'add')
					$(this).addClass('fa-plus');
				
				if(text == 'add new item')
					$(this).addClass('fa-plus');
				
				if(text == 'rescan')
					$(this).addClass('fa-refresh');
					
			}
		});
		
	},
	
	attachDragtable: function()
	{
		var self = this;
		
		if(!self.options.useDragtable)
			return true;
		
		var options =  $.extend( {}, self.options.dragtableOptions, {
			
			beforeStop: function(_table) {
				table = self.table;
				var url = self.parseUrl();
				var cookieName = 'DragtableOrder.'+url.attr('controller')+'.'+url.attr('action');
				var sortOrder = {};
				table.find('th').each(function(index) { 
					var key = $(this).data('column-key');
					if(key) { sortOrder[index] = key; } 
				}); 
				self.cookie.set(cookieName, sortOrder);
				table.trigger('reflow');
				return true;
			},
			restoreState: function(dragtableObject) {
				table = self.table;
				var url = self.parseUrl();
				var cookieName = 'DragtableOrder.'+url.attr('controller')+'.'+url.attr('action');
				var sortOrder = self.cookie.getJSON(cookieName) || {};
				dragtableObject.restoreState(sortOrder);
				table.trigger('reflow'); 
			}
		});
		
		var handler = $('<i></i>')
			.addClass('table-handle').addClass('fa').addClass('fa-arrows-h');
		self.table.find('th').prepend(handler);
		
		self.table.dragtable(options).addClass('dragtable');
		
	},
	
	attachRowHovering: function()
	{
		var self = this;
		
		self.table.find('td.owner').parent().addClass('owner');
		
		self.table.find('td').on('mouseenter', function(event) {
			$(this).parent().addClass('hovered');
		});
		self.table.find('td').on('mouseleave', function(event) {
			$(this).parent().removeClass('hovered');
		});
		self.table.find('td.actions select').on('change', function(event) {
			$(this).parent().addClass('hovered');
		});
	},
	
	attachColumnHovering: function()
	{
		var self = this;
		
		self.table.find('th').on('mouseenter', function(event) {
			$(this).addClass('hovered');
			var columnKey = $(this).data('column-key');
			self.table.find('td[data-column-key="' + columnKey +'"]').addClass('hovered');
		});
		
		self.table.find('th').on('mouseleave', function(event) {
			$(this).removeClass('hovered');
			var columnKey = $(this).data('column-key');
			self.table.find('td[data-column-key="' + columnKey +'"]').removeClass('hovered');
		});
	},
	
	attachRowHighlighting: function() 
	{
		var self = this;
		
		if(!self.options.useRowHighlighting)
			return true;
		
		// watch for when a td is clicked
		self.table.find('td').each(function()
		{
			$(this).on('click', function(event) 
			{
				if(event.target.nodeName == 'INPUT') return;
				if(event.target.nodeName == 'SELECT') return;
			
				$(this).parents('tr').toggleClass('highlight_click');
				var highlighted = $(this).parents('tr').hasClass('highlight_click');
				var ms_checkbox = $(this).parents('tr').find('input.multiselect_item[type="checkbox"]');
				// if this table has multiselect, toggle that as well
				if(ms_checkbox)
				{
					ms_checkbox.prop( "checked", highlighted );
			
					// make sure the select all is unchecked
					if(!highlighted)
						self.element.find('th[data-column-key="multiselect"] input[type="checkbox"]').prop('checked', false);
				}
			});
		});
	},
	
	attachTableScrolling: function() 
	{
		var self = this;
		
		// find all of the elements that we want to have scroll with the table
		self.element.find('.form_search, .listings_options, .plugin_filter, .multiselect_options, .multiselect_submit, .listings_table_wrapper_scrollers').addClass('stay_with_table');
		
		var leftOffset = self.element.position().left;

		$(window).scroll(function(){
			self.element.find('.stay_with_table').css({
				'left': $(this).scrollLeft() + leftOffset //Use it later
			});
		});
	},
	
	attachJsExporting: function()
	{
		var self = this;
		
		if(!self.options.useJsExporting)
			return true;
		
		var table = self.table;
		
		self.element.find('div.table-options a.js-export').on('click', function(event)
		{
			var jsExportButton = $(this);
			var totalCount = false;
			var totalCountText = false;
			var totalCountObject = self.element.find('.paginate_counter-total').first();
			if(totalCountObject)
			{
				totalCount = totalCountObject.text();
				totalCountText = totalCount+' ';
			}
			
			var pageLast = 1;
			var pageLastObject = self.element.find('.paginate_counter-page-last').first();
			if(pageLastObject)
				pageLast = parseInt(pageLastObject.text());
			
			if(pageLast > 1)
			{
				var isGood=confirm("This will only export rows and columns that are currently visible and loaded.\nIf you would like to load all of the "+totalCountText+"results, select the [Show All] button.");
				if(!isGood)
					return false;
			}
			
			// build the csv export
			var headers = table.find('tr:has(th)'),
				rows = table.find('tr:has(td)'),
				tmpColDelim = String.fromCharCode(11),
				tmpRowDelim = String.fromCharCode(0),
				colDelim = '","',
				rowDelim = '"\r\n"';
			
			var csv = '"' + headers.map(function (i, row) {
				var row = $(row),
					cols = row.find('th:visible').not( '[data-column-key="actions"], [data-column-key="editable"]' );

				return cols.map(function (j, col) {
					var col = $(col),
						text = col.text();
					text.replace(/"/g, '""'); // escape double quotes
					text.replace('&nbsp;', '');
					
					output = '';
					for (var i=0; i<text.length; i++) {
						if (text.charCodeAt(i) <= 127) {
							output += text.charAt(i);
						}
					}
					text = output;

					return text;

				}).get().join(tmpColDelim);

			}).get().join(tmpRowDelim)
				.split(tmpRowDelim).join(rowDelim)
				.split(tmpColDelim).join(colDelim);
				
			csv += rowDelim + rows.map(function (i, row) {
				var row = $(row),
					cols = row.find('td:visible').not( ".actions" );

				return cols.map(function (j, col) {
					var col = $(col);
					var text = col.text();
					if(col.find('.editable-content').length)
					{
						text = col.find('.editable-content').text();
					}
					text = text.replace(/^Never$/i, '');
					text.replace(/"/g, '""'); // escape double quotes
					text.replace('&nbsp;', '');
					
					output = '';
					for (var i=0; i<text.length; i++) {
						if (text.charCodeAt(i) <= 127) {
							output += text.charAt(i);
						}
					}
					text = output;

					return text;

				}).get().join(tmpColDelim);

			}).get().join(tmpRowDelim)
				.split(tmpRowDelim).join(rowDelim)
				.split(tmpColDelim).join(colDelim) + '"';

            // Data URI
			var csvData = 'data:application/csv;charset=utf-8,' + encodeURIComponent(csv);
			var url = self.parseUrl();
			var fileName = url.attr('controller')+'-'+url.attr('action')+'.csv';
			if(jsExportButton.data('export_name'))
			{
				fileName = jsExportButton.data('export_name')+'.csv';
			}
			
			$(this)
            	.attr({
					'download': fileName,
					'href': csvData,
					'target': '_blank'
        	});
		});
	},
	
	attacheCollapsibleColumns: function()
	{
		var self = this;
		
		if(!self.options.useCollapsibleColumns)
			return true;
		
		var scroll_wrapper = self.element.find('div.table-options');
		
		var selector = $("<select></select>")
			.addClass('collapsible').addClass('not-chosen');
		var option = $('<option></option>')
				.val('0')
				.text('Show/Hide Table Columns')
				.appendTo(selector);
		
		self.table.find('th').each(function()
		{
			var thisTH = $(this);
			var columnKey = thisTH.data('column-key');
			
			// ignore some of the columns
			if(columnKey == 'multiselect') return;
			if(columnKey == 'actions') return;
			if(columnKey == 'editable') return;
			
			var thText = '';
			if(thisTH.find('span.paging_link').length)
				thText = thisTH.find('span.paging_link').text();
			else
				thText = thisTH.text();
			
			var option = $('<option></option>')
				.val(columnKey)
				.text(thText)
				.appendTo(selector);
			thisTH.addClass('column-collapsible');
		});
		
		self.table.find('td').each(function()
		{
			var thisTD = $(this);
			var columnKey = thisTD.data('column-key');
			
			// ignore some of the columns
			if(columnKey == 'multiselect') return;
			if(columnKey == 'actions') return;
			if(columnKey == 'editable') return;
			thisTD.addClass('column-collapsible');
		});
		
		selector.on('change', function(event)
		{
			event.preventDefault();
			var value = $(this).val();
			self.toggleColumn(value);
			$(this).val('0');
		});
		
		scroll_wrapper.prepend(selector);
		
		if(!self.option('cookies'))
			return true;
			
		// check the cookie
		var url = self.parseUrl();
		var cookieName = 'CollapsibleColumns.'+url.attr('controller')+'.'+url.attr('action');
		var cookieVars = self.cookie.getJSON(cookieName) || {};
		
		// collapse any columns that are true in the cookieVars
		self.element.find('div.listings_table_wrapper_scrollers select.collapsible option').each(function()
		{
			columnKey = $(this).val();
			if(columnKey)
			{
				if( columnKey in cookieVars )
				{
					if(cookieVars[columnKey] === true)
					{
						self.toggleColumn(columnKey, true);
					}
				}
			}
		});
		
	},
	
	attachMultiselectTriggers: function() 
	{
		var self = this;
		self.table.find('th input.multiselect_all').parents('th').addClass('multiselect').addClass('multiselect_all');
		self.table.find('th input.multiselect_all').on('click', function(event) {
			
			ischecked = $(this).prop('checked') || false;
			self.table.find('td input.multiselect_item').each( function() { 
				$(this).prop("checked",ischecked);
				
				if(ischecked)
					$(this).parents('tr').addClass('highlight_click'); 
				else
					$(this).parents('tr').removeClass('highlight_click'); 
			});
		});
		
		// if one if them get unchecked, uncheck the select all
		self.table.find('td input.multiselect_item').each( function() {
			// add the classname to the th
			$(this).parents('td').addClass('multiselect');
			
			$(this).on('click', function(event) {
				ischecked = $(this).prop('checked') || false;
				
				if(ischecked)
					$(this).parents('tr').addClass('highlight_click'); 
				else
					$(this).parents('tr').removeClass('highlight_click');
					
				if(!ischecked)
				{
					self.table.find('th input.multiselect_all').prop("checked",false); 
				}
				// see if all of them are checked
				else
				{
					var total = 0;
					var checked = 0;
					self.table.find('td input.multiselect_item').each( function() {
						total++;
						ischecked = $(this).prop('checked') || false;
						if(ischecked)
							checked++;
					});
					if(total == checked)
						self.table.find('th input.multiselect_all').prop("checked",'checked'); 
					else
						self.table.find('th input.multiselect_all').prop("checked",false); 
				}
			});
		});
		
		self.element.find('#multiselect_options').on('change', function( event ) {
			$('#multiselect_options_hidden').val($(this).val());
		});
	},
	
	attacheMultiselectSubmit: function()
	{
		var self = this;
		
		// find the form
		var multiselectForm = self.element.find('form.multiselect_form');
		
		if(!multiselectForm.length)
			return true;
		
		multiselectForm.on('submit', function(event)
		{
//			event.preventDefault();
			var form = $(this);
			var inputs = form.find(':input');
			
			multiselectValues = {};
			
			inputs.each(function(index)
			{
				var input = $(this);
				
				// if it has no name, it's not relevant to us
				if(!input.attr('name'))
					input.attr('disabled', true);
					
				// see if it's an inline edit input, ignore it
				if(input.parents('div.editable-input').length)
					input.attr('disabled', true);
				
				// ignore inline edit hidden fields
				if(input.parents('div.editable-actions').length)
					input.attr('disabled', true);
				
				// see if it's a checkbox, if so, see if it's checked
				if(input.is(':checkbox'))
					if(!input.is(':checked'))
						input.attr('disabled', true);
				
			});
			
			return true;
		});
	},
	
	attachFloatHead: function()
	{
		var self = this;
		
		if(!self.options.floatHead)
			return true;
		
		if(self.table.hasClass('has-floathead'))
		{
			self.table.floatHead('destroy').removeClass('has-floathead');
		}
		
		self.options.floatHeadOptions.scrollingTop = 0;
		
		// float below the top of the page
		var topElement = $(".main-content > .top");
		if(topElement.length)
		{
			var topElementTop = topElement.offset().top - parseFloat(topElement.css('marginTop').replace(/auto/, 0));
			var topElementbottom = topElementTop + topElement.height();
			
			var scrollingTop = topElement.outerHeight();
			self.options.floatHeadOptions.scrollingTop = scrollingTop;
			self.options.floatHeadOptions.scrollingBottom = scrollingTop;
		}
		
		self.table.floatThead(self.options.floatHeadOptions).addClass('has-floathead');
	},
	
	attachGridEditing: function()
	{
		var self = this;
		
		if(!self.options.useGridedit)
			return true;
		
		// initially disable the hidden inline edit fields
//		self.table.find('tr td div.editable-input input').prop('disabled', 'disabled');
//		self.table.find('tr td div.editable-input select').prop('disabled', 'disabled').trigger('chosen:updated');
//		self.table.find('tr td div.editable-input textarea').prop('disabled', 'disabled');
		
		//// Track the edit button
		self.table.find('a.editable-button-edit').on('click', function (event) 
		{
			event.preventDefault();
			self.table.find('a.editable-button-cancel').trigger( "click" );
			
			// hide my parent div
			$(this).parents('tr').find('.editable-actions-off').hide();
			$(this).parents('tr').find('.editable-actions-on').show();
			
			$(this).parents('tr').find('td.editable span.editable-content').hide();
			$(this).parents('tr').find('td.editable div.editable-input').show();
			
//			$(this).parents('tr').find('div.editable-input input').prop('disabled', false);
//			$(this).parents('tr').find('div.editable-input select').prop('disabled', false).trigger('chosen:updated');
//			$(this).parents('tr').find('div.editable-input textarea').prop('disabled', false);
			
			// monitor all of the input fields, if any change, trigger a save
			$(this).parents('tr').find('div.editable-input input').each(function( index ) 
			{
				if($(this).parent().hasClass('chosen-search'))
					$(this).addClass('chosen-search-field');
				
				// see if the clear button is pushed
				if($(this).hasClass('clearable'))
				{
					// find the clear button
					var clearButton = $(this).parent().find('.clearable-button');
					if(clearButton.length)
					{
						clearButton.on('click', function(event){
							// allow the clear button to do it's thing
							setTimeout(function(){
								clearButton.parents('tr').find('a.editable-button-save').trigger( "click" );
							}, 200);
						});
					}
				}
				
				// text fields
				var typeAttr = $(this).attr('type');
				if($(this).is(':text') || typeAttr == 'number')
				{
					// try to fix the stupid html encoded stuff
					var decoded = $("<div/>").html($(this).val()).text();
					$(this).val(decoded);
					
					// ignore the chosen search text
					var watchMe = true;
					
					if($(this).hasClass('chosen-search-field'))
						watchMe = false;
					
					if(watchMe)
					{
						$(this).on('focusout', function() {
							$(this).parents('tr').find('a.editable-button-save').trigger( "click" );
						});
					}
				}
				
				// checkboxes
				if($(this).is(':checkbox'))
				{
					$(this).on('change', function() {
						$(this).parents('tr').find('a.editable-button-save').trigger( "click" );
					});
				}
				
			});
			
			$(this).parents('tr').find('div.editable-input select').each(function( index ) 
			{
				$(this).on('change', function() {
					$(this).parents('tr').find('a.editable-button-save').trigger( "click" );
				});
			});
			
			$(this).parents('tr').find('div.editable-input textarea').each(function( index ) 
			{
				$(this).on('focusout', function() {
					$(this).parents('tr').find('a.editable-button-save').trigger( "click" );
				});
			});
			
			return false;
		});
		
		//// Track the cancel button
		self.table.find('a.editable-button-cancel').on("click", function (event) 
		{
			event.preventDefault();
			var trueThis = $(this);
				// hide my parent div
				trueThis.parents('tr').find('.editable-actions-on').hide();
				trueThis.parents('tr').find('.editable-actions-off').show();
				
//				trueThis.parents('tr').find('div.editable-input input').prop('disabled', 'disabled');
//				trueThis.parents('tr').find('div.editable-input select').prop('disabled', 'disabled').trigger('chosen:updated');
//				trueThis.parents('tr').find('div.editable-input textarea').prop('disabled', 'disabled');
				
				trueThis.parents('tr').find('td.editable span.editable-content').show();
				trueThis.parents('tr').find('td.editable div.editable-input').hide();
			
			return false;
		});
		
		//// Track the save button
		self.table.find('a.editable-button-save').on("click", function (event) 
		{
			event.preventDefault();
			
			/// find all of the inputs
			var inline_button = $(this);
			
			setTimeout(function(){
			
			var require_fail = false;
			
			/// check values if required
			inline_button.parents('tr').find('td:not(.column-collapsed) input').each(function( index )
			{
				if($(this).attr('data-editable-required'))
				{
					var required_message = $(this).parent().find('div.required_message');
					if(!$.trim($(this).val()))
					{
						$(this).focus();
						required_message.html('Can not be empty.');
						required_message.show();
						required_message.fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100);
						require_fail = true;
					}
					else
					{
						required_message.hide();
					}
				}
			});
			
			inline_button.parents('tr').find('td:not(.column-collapsed) select').each(function( index ) 
			{
				if($(this).attr('data-editable-required'))
				{
					var required_message = $(this).parent().find('div.required_message');
					if(!$.trim($(this).val()))
					{
						$(this).focus();
						required_message.html('<?php echo __("Please select an option."); ?>');
						required_message.show();
						required_message.fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100);
						require_fail = true;
					}
					else
					{
						required_message.hide();
					}
				}
			});
			
			/// check values if required
			inline_button.parents('tr').find('td:not(.column-collapsed) textarea').each(function( index )
			{
				if($(this).attr('data-editable-required'))
				{
					var required_message = $(this).parent().find('div.required_message');
					if(!$.trim($(this).val()))
					{
						$(this).focus();
						required_message.html('Can not be empty.');
						required_message.show();
						required_message.fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100);
						require_fail = true;
					}
					else
					{
						required_message.hide();
					}
				}
			});
			
			if(require_fail)
			{
				return false;
			}
			
			var inline_serialized = '';
			inline_button.parents('tr').find('td:not(.column-collapsed) input').each(function( index )
			{
				var decoded = $("<div/>").html($(this).val()).text();
				$(this).val(decoded);
				inline_serialized += $(this).serialize()+'&';
			});
			inline_button.parents('tr').find('td:not(.column-collapsed) select').each(function( index )
			{
				inline_serialized += $(this).serialize()+'&';
			});
			inline_button.parents('tr').find('td:not(.column-collapsed) textarea').each(function( index )
			{
				inline_serialized += $(this).serialize()+'&';
			});
		
			self.ajax({
				type: 'POST',
				url: self.options.grideditOptions.editUri,
				data: inline_serialized,
				dataType: 'json',
				success: function(data, textStatus, jqXHR) 
				{
					var table_refresh = false; // if we should force a table refresh
					
					// update the text for each of these fields
					inline_button.parents('tr').find('td:not(.column-collapsed) div.editable-input input').not('[type="hidden"]').each(function( index )
					{	
						var thisValue = $(this).val();
						
						if($(this).attr('data-highlight-toggle'))
						{
							if($(this).attr('data-highlight-toggle') == $(this).val())
							{
								$(this).parents('tr').find('td').addClass('highlight');
							}
							else
							{
								$(this).parents('tr').find('td').removeClass('highlight');
							}
						}
						
						// if this is a checkbox
						if($(this).is(':checkbox'))
						{
							if($(this).is(':checked'))
							{
								$(this).parents('td').find('span.editable-content').text($(this).attr('data-editable-checked-text'));
								if($(this).attr('data-highlight-toggle'))
								{
									$(this).parents('tr').find('td').addClass('highlight');
								}
							}
							else
							{
								$(this).parents('td').find('span.editable-content').text($(this).attr('data-editable-unchecked-text'));
								if($(this).attr('data-highlight-toggle'))
								{
									$(this).parents('tr').find('td').removeClass('highlight');
								}
							}
						}
						// the rest
						else
						{
							if(!thisValue && $(this).parents('td').is('.editable-date, .editable-datetime', 'editable-time'))
								thisValue = 'Never';
							
							if($(this).parents('td').data('editable-type') == 'price')
								thisValue = '$'+thisValue;
							
							if($(this).parents('td').data('editable-type') == 'text')
							{
								var editableKey = $(this).parents('td').data('editable-key');
								var editableKeyParts = editableKey.split('.');
								[model, field] = editableKeyParts;
								var serverValue = false;
								if (data[model] !== undefined)
									if (data[model][field] !== undefined)
										serverValue = data[model][field];
								if(serverValue)
								{
									thisValue = serverValue;
									$(this).val(thisValue);
								}
							}
							
							$(this).parents('td').find('span.editable-content').not('[type="hidden"]').text(thisValue);
						}
					});
					
					inline_button.parents('tr').find('td:not(.column-collapsed) div.editable-input select').each(function( index )
					{
						if($('option:selected', $(this)).val() == '')
						{
							$(this).parents('td').find('span.editable-content').text('');
						}
						else
						{
							$(this).parents('td').find('span.editable-content').text($('option:selected', $(this)).text());
						}
					});
					
					inline_button.parents('tr').find('td:not(.column-collapsed) div.editable-input textarea').each(function( index )
					{
						var thisValue = $(this).val();
						
						if($(this).parents('td').data('editable-type') == 'textarea')
						{
							var editableKey = $(this).parents('td').data('editable-key');
							var editableKeyParts = editableKey.split('.');
							[model, field] = editableKeyParts;
							var serverValue = false;
							if (data[model] !== undefined)
								if (data[model][field] !== undefined)
									serverValue = data[model][field];
							if(serverValue)
							{
								thisValue = serverValue;
							}
						}
						
						$(this).val(thisValue);
						
						textLimit = false;
						if($(this).parents('td').data('limit'))
							textLimit = $(this).parents('td').data('limit');
						
						thisValue = self.truncateText(thisValue, textLimit);
						$(this).parents('td').find('span.editable-content').not('[type="hidden"]').text(thisValue);
					});
					
					self.ajaxDone(data, textStatus, jqXHR);
				},
			});
			
			}, 500); // setTimeout
			return false;
		});
		
		//// Track the delete button
		self.table.find('a.editable-button-delete').on('click', function (event)
		{
			// force a confirm if they want to delete
			if (confirm("<?php echo __('Are you sure you want to delete this row?'); ?>") == false)
			{
				return false;
			}
			
			/// find all of the inputs
			var inline_button = $(this);
			
			var inline_serialized = '';
			inline_button.parents('tr').find('td input').each(function( index )
			{
				inline_serialized += $(this).serialize()+'&';
			});
		
			self.ajax({
				type: 'POST',
				url: self.options.grideditOptions.deleteUri,
				data: inline_serialized,
				success: function(data) {
					inline_button.parents('tr').remove();
				}
			});
			
			return false;
		});
	
		//// Track the add button
		self.table.find('a.editable-button-add').on('click', function (event)
		{
			/// find all of the inputs
			var inline_button = $(this);
			
			var require_fail = false;
			
			/// check values if required
			inline_button.parents('tr').find('td input').each(function( index )
			{
				if($(this).attr('data-editable-required'))
				{
					var required_message = $(this).parent().find('div.required_message');
					if(!$.trim($(this).val()))
					{
						$(this).focus();
						required_message.html('Can not be empty.');
						required_message.show();
						required_message.fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100);
						require_fail = true;
					}
					else
					{
						required_message.hide();
					}
				}
			});
			
			inline_button.parents('tr').find('td select').each(function( index )
			{
				if($(this).attr('data-editable-required'))
				{
					var required_message = $(this).parent().find('div.required_message');
					if(!$.trim($(this).val()))
					{
						$(this).focus();
						required_message.html('Please select an option.');
						required_message.show();
						required_message.fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100);
						require_fail = true;
					}
					else
					{
						required_message.hide();
					}
				}
			});
			
			if(require_fail)
			{
				return false;
			}
			
			var inline_serialized = '';
			inline_button.parents('tr').find('td input').each(function( index )
			{
				inline_serialized += $(this).serialize()+'&';
			});
			inline_button.parents('tr').find('td select').each(function( index )
			{
				inline_serialized += $(this).serialize()+'&';
			});
			
			self.ajax({
				type: 'POST',
				url: self.options.grideditOptions.addUri,
				data: inline_serialized,
				success: function(data) {
					// we're in a tab, so refresh the tab
					var panel = inline_button.parents('section.panel');
					if(panel.length)
					{
						var tabId = panel.attr('aria-labelledby');
						if(tabId)
						{
							var tab = panel.parents('.nihfo-object-tabs').find('nav.tabs a#'+tabId);
							if(tab)
							{
								tab.trigger('click');
								return true;
							}
						}
					}
					
					location.reload(true);
				}
			});
			
			return false;
		});
		
		self.table.find('td.editable').dblclick(function(event) 
		{
			if($(event.target).is("input,select")) return;
			
			var me = $(this);
			// submit, and close any open ones
			self.table.find('div.editable-actions-on:visible').each(function(index){
				if(me.parents('tr').attr('id') != $(this).parents('tr').attr('id'))
				{
					$(this).parents('tr').find('a.editable-button-save').trigger( "click" );
					$(this).parents('tr').find('a.editable-button-cancel').trigger( "click" );
				}
			});
			
			if ( me.parents('tr').find('div.editable-actions-on').is(":visible") )
			{
				me.parents('tr').find('a.editable-button-save').trigger( "click" );
				me.parents('tr').find('a.editable-button-cancel').trigger( "click" );
			}
			else
			{
				me.parents('tr').find('a.editable-button-edit').trigger( "click" );
			}
		});
		
		$(document).keyup(function(e) {
			if (e.keyCode === 27) self.table.find('a.editable-button-cancel').trigger( "click" );   // esc
		});
	},
	
	attachTextareas: function()
	{
		var self = this;
		var table = self.table;
		
		table.find('tbody tr td.textarea').not('.no-truncate').each(function( index )
		{
			var cellObject = $(this).find('div.cell-content').first();
			if(!cellObject.length)
				return;
			
			var cellEditableObject = cellObject.find('span.editable-content').first();
			
			var updateObject = false;
			if(cellEditableObject.length)
				updateObject = cellEditableObject;
			else
				updateObject = cellObject;
			
			updateObject.truncate(self.options.truncate);
		});
	},
	
	truncateText: function(text)
	{
		var text = typeof text !== 'undefined' ?  text : '';
		return text;
	},
	
	attachJsSearch: function()
	{
		var self = this;
		
		if(!self.options.useJsSearch)
			return true;
		
		var table = self.table;
		self.table.addClass('js-search');
		var rows = self.table.find('tbody tr');
		var scroll_wrapper = self.element.find('div.table-options');
		
		var searchField = $('<input type="search" placeholder="Search current table."></input>')
			.addClass('js-search');
			
		searchField.keyup(function() {
			var val = $.trim($(this).val()).replace(/\s+/g, ' ').toLowerCase();
			
			self.element.find('input.js-search').val(val);
			if(val)
				self.element.find('input.js-search').addClass('search_active');
			else
				self.element.find('input.js-search').removeClass('search_active');
    		
			rows.show().filter(function() {
				var rowText = [];
				$(this).find('td:not(.actions)').each(function(index){
					var text = '';
					if($(this).hasClass('editable'))
					{
						text = $(this).find('.editable-content').text()
					}
					else
					{
						text = $(this).text();
					}
					text = text.replace(/\s+/g, ' ').toLowerCase();
					rowText.push(text);
				});
				rowText = rowText.join(' ');
				return !~rowText.indexOf(val);
			}).hide();
			table.trigger('reflow');
		});
		scroll_wrapper.prepend(searchField);
	},
	
	attachJsOrdering: function()
	{
		var self = this;
		
		if(!self.options.useJsOrdering)
			return true;
		
		var ths = self.table.find('th');
		var table = self.table;
		self.table.addClass('jsordering');
		
		ths.each(function(index)
		{
			var columnKey = $(this).data('column-key');
			$(this).on('click', function(event)
			{
				var totals_row = table.find('td.totals_row').parent();
				// remove it, we'll add it back after sorting
				if(totals_row)
				{
					table.find('td.totals_row').parent().remove();
				}
		
				var rows = table.find('tr:gt(0)').toArray().sort(self.orderingComparer(index));
				this.asc = !this.asc;
				if (this.asc)
				{
					rows = rows.reverse();
				}
				for (var i = 0; i < rows.length; i++)
				{
					table.append(rows[i]);
				}
				
				if(totals_row)
				{
					table.append(totals_row);
				}
				
				// update the url
				self.options.here.query('jsindex-'+self.options.orderingCookieName, columnKey);
				self.options.here.query('jsorder-'+self.options.orderingCookieName, this.asc);
				self.options.here.updateQuery();
				
				// update the cookie
				self.cookie.set(self.options.orderingCookieName, { jsindex: columnKey, jsorder: this.asc } );
				
				ths.removeClass('asc');
				ths.removeClass('desc');
				
				if(this.asc)
					$(this).addClass('asc');
				else
					$(this).addClass('desc');
				table.trigger('reflow');
			});
		});
		
		// see if we need to trigger a click on one of the ths
		// go by the url first
		var jsindex = self.options.here.query('jsindex-'+self.options.orderingCookieName) || false;
		var initialJsorder = self.options.here.query('jsorder-'+self.options.orderingCookieName) || false;
		var initialJsorder = (initialJsorder == 'false' ? false : true);
		
		// go by the cookie next
		if(!jsindex)
		{
			var orderingCookie = self.cookie.getJSON(self.options.orderingCookieName) || false;
			if(orderingCookie)
			{
				jsindex = orderingCookie.jsindex;
				initialJsorder = orderingCookie.jsorder;
			}
		}
		
		if(jsindex)
		{
			ths.each(function(index)
			{
				var columnKey = $(this).data('column-key');
				if(columnKey == jsindex)
				{
					$(this).trigger('click');
					if(!initialJsorder) 
						$(this).trigger('click');
				}
			});
		}
	},
	
	orderingComparer: function(index)
	{
		var self = this;
		return function(a, b) 
		{
			var valA = self.getCellValue(a, index), valB = self.getCellValue(b, index)
			return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.localeCompare(valB)
		}
	},
	
	getCellValue: function(row, index)
	{
		return $(row).children('td').eq(index).find('div.cell-content').text();
	},
	
	attachSortable: function()
	{
		var self = this;
		
		if(!self.options.sortable)
			return true;
		
		self.options.sortableOptions.onDrop = function ($item, container, _super, event) 
			{ self.sortableOnDrop($item, container, _super, event); };
		
		self.table.find('table.sorted_table').sortable(self.options.sortableOptions);
	},
	
	sortableOnDrop: function ($item, container, _super, event) 
	{
		var self = this;
		
		// make sure the default code is ran first
		$item.removeClass("dragged").removeAttr("style")
		$("body").removeClass("dragging")
		
		if(!self.options.sortableOptions.sorted_url)
			return true;
		
		// serialize the td ids
		var data = {};
		var i =0;
		self.table.find('tr').each(function()
		{
			var useId = true;
			$(this).find('input.editable_id').each(function()
			{
				var field = $(this).data('field');
				if(!field)
					return;
				var key = field+'.'+$(this).val();
				data[key] = i;
				useId = false;
			});
			
			if($(this).attr("id") && useId)
			{
				data[$(this).attr("id")] = i;
			}
			i++;
		});
		
		if(self.options.sortableOptions.sorted_url)
		{
			self.ajax({
				type	: 'POST',
				dataType: 'json',
				url		: self.options.sortableOptions.sorted_url,
				data	: data,
			});
		}
	},
	
	watchToggles: function()
	{
		var self = this;
		
		if(!self.options.watchToggles)
			return true;
		
		// track the toggle buttons
		self.table.find('td.actions a, table.actual-table td a[data-confirm="1"].link-toggle').each(function( index )
		{
			if($(this).attr("href").indexOf("toggle") > 0)
			{
				var onclick = function() { if (confirm("Are you sure?")) { return true; } return false; };
				if($(this).attr('onclick'))
				{
					eval('onclick = function(){ '+$(this).attr('onclick')+' }');
					$(this).attr('onclick', null);
				}
				
				$(this).click(function (event) {
					event.preventDefault();
					if(onclick())
					{
						self.ajax({
							type: 'GET',
							dataType: 'html',
							url: $(this).attr("href")+'/ajaxhijack:1',
							success: function(data) {
							
								// see if we're in a tab, if so, reload the tab
								var panel = self.element.parents('section[role="panel"]');
								if(panel.length)
								{
									var tabId = panel.attr('aria-labelledby');
									var tab = panel.parents('div.nihfo-object-tabs').find('nav.tabs a#'+tabId).first();
									if(tab.length)
										tab.trigger('click');
								}
								// otherwise, reload the page
								else
								{
									location.reload(true);
								}
							}
						});
					}
					return false;
				});
			}
		});
	},
	
	watchMiniViews: function()
	{
		var self = this;
		
		//// Track the mini-view buttons
		self.table.find('a.mini-view').on("click", function (event)
		{
			event.preventDefault();
			var link = $(this);
			var tr_id = link.parents( "tr" ).attr('id');
			
			// hide the mini-view if this one has one
			if(link.data('mini-view-id'))
			{
				$(link.data('mini-view-id')).hide();
				$(link.data('mini-view-id')).remove();
				link.data('mini-view-id', false);
			}
			// otherwise, show the mini-view
			else
			{
				var url = $(this).attr("href");
				
				self.ajax({
					url: url,
					success: function(data) {
						var this_tr_id = tr_id+'_mini_view';
						var tr_wrapper = $('<tr class="mini-view-results" id="'+this_tr_id+'">');
						link.data('mini-view-id', '#'+this_tr_id);
						var colCount = 0;
						link.parents( "tr" ).find('td').each(function () {
							if ($(this).attr('colspan')) {
								colCount += +$(this).attr('colspan');
							} else {
								colCount++;
							}
						});
						var td_wrapper = $('<td>');
						if(colCount)
						{
							td_wrapper.attr('colspan', colCount);
						}
						td_wrapper.html(data);
						tr_wrapper.append(td_wrapper);
						link.parents( "tr" ).after(tr_wrapper);
					}
				});
			}
			
			return false;
		});
	},
	
	attachAjaxReload: function()
	{
		var self = this;
		
		if(self.options.autoLoadAjax)
		{
			self.getAjaxCounts();
			self.getAjaxContent();
		}
		
		var reloadButton = self.element.find('div.table-options a.js-auto_load_ajax');
		
		if(self.table.find('td.ajax-count').length)
			reloadButton.show();
		if(self.table.find('td.ajax-content').length)
			reloadButton.show();
		
		reloadButton.on('click', function(event)
		{
			event.preventDefault();
			
			$.each( self.options.ajaxCountPool, function( index, jqXHR ){
				jqXHR.abort();
				self.options.ajaxCountPool.splice(index, 1);
			});
			
			self.getAjaxCounts();
			self.getAjaxContent();
		});
	},
	
	getAjaxCounts: function()
	{
		var self = this;
		
		// track the ajax count requests
		self.table.find('.ajax-count-link').each(function( index )
		{
			var update_element = $(this);
			var cell = update_element.parents('td');
			var url = update_element.data('ajax_count_url');
			if(!url) return true;
		
			cell.removeClass('ajax-count-highlight');
			if(cell.data('highlight-class'))
				cell.removeClass(cell.data('highlight-class'));
							
			cell.removeClass('ajax-count-no-highlight');
			if(cell.data('no-highlight-class'))
				cell.removeClass(cell.data('no-highlight-class'));
			
			self.ajax({
				type: 'GET',
				url: url,
				beforeSend: function(jqXHR, settings) {
					update_element.html('...');
					self.options.ajaxCountPool.push(jqXHR);
				},
				complete: function(jqXHR, textStatus) {
					var index = self.options.ajaxCountPool.indexOf(jqXHR);
					if (index > -1) self.options.ajaxCountPool.splice(index, 1);
				},
				success: function(data, textStatus, jqXHR) {
					update_element.html(data);
					update_element.attr('title', 'Count: '+data);
					if(data != '0')
					{
						cell.removeClass('ajax-count-no-highlight');
						if(cell.data('no-highlight-class'))
							cell.removeClass(cell.data('no-highlight-class'));
						
						cell.addClass('ajax-count-highlight');
						if(cell.data('highlight-class'))
							cell.addClass(cell.data('highlight-class'));
						self.setColor(cell);
					}
					else
					{
						cell.removeClass('ajax-count-highlight');
						if(cell.data('highlight-class'))
							cell.removeClass(cell.data('highlight-class'));
					
						cell.addClass('ajax-count-no-highlight');
						if(cell.data('no-highlight-class'))
							cell.addClass(cell.data('no-highlight-class'));
					}
					update_element.data('ajax_loaded', 1);
				},
				error: function(jqXHR, textStatus, errorThrown) {
					var statHtml = 'ER';
					var statTitle = errorThrown;
					if(textStatus == 'abort')
					{
						statHtml = 'AB';
						statTitle = 'Request was aborted.';
					}
					else if(textStatus == 'error' && errorThrown == '')
					{
						statHtml = 'AB';
						statTitle = 'Request was aborted.';
					}
					else if(textStatus == 'error' && errorThrown == 'Not Found')
					{
						statHtml = 'NF';
						statTitle = 'Not Found';
					}
				
					update_element.html(statHtml);
					update_element.attr('title', statTitle);
				}
			});
		});
	},
	
	getAjaxContent: function()
	{
		var self = this;
		
		// track the ajax count requests
		self.table.find('td.ajax-content').each(function( index )
		{
			var cell = $(this);
			var url = $(this).data('ajax_content_url');
			if(!url) return true;
			var update_element = $(this).find('.cell-content').first();
			if(!update_element.length)
				update_element = $(this);
			
			self.ajax({
				type: 'GET',
				dataType: 'html',
				url: url,
				beforeSend: function(jqXHR, settings) {
					update_element.html('...');
				},
				success: function(data, textStatus, jqXHR) {
					update_element.html(data);
					self.element.trigger('reflow');
				},
				error: function(jqXHR, textStatus, errorThrown) {
					var statHtml = 'ER';
					var statTitle = errorThrown;
					if(textStatus == 'abort')
					{
						statHtml = 'AB';
						statTitle = 'Request was aborted.';
					}
					else if(textStatus == 'error' && errorThrown == '')
					{
						statHtml = 'AB';
						statTitle = 'Request was aborted.';
					}
					else if(textStatus == 'error' && errorThrown == 'Not Found')
					{
						statHtml = 'NF';
						statTitle = 'Not Found';
					}
					
					update_element.html(statHtml);
					update_element.attr('title', statTitle);
				}
			});
		});
	},
	
	toggleColumn: function($value, forceToggle)
	{
		var self = this;
		
		var url = self.parseUrl();
		var cookieName = 'CollapsibleColumns.'+url.attr('controller')+'.'+url.attr('action');
		var cookieVars = self.cookie.getJSON(cookieName) || {};
		
		var selector = self.element.find('div.listings_table_wrapper_scrollers select.collapsible');
		var option = selector.find('option[value="'+$value+'"]');
		
		if(typeof forceToggle !== 'undefined')
		{
			if(forceToggle)
				option.removeClass('collapsed');
			else
				option.addClass('collapsed');
		}
		
		// expand the column
		if(option.hasClass('collapsed'))
		{
			option.removeClass('collapsed');
			// update the cookie
			cookieVars[$value] = false;
			// show the column
			self.table.find('th[data-column-key="' + $value +'"]').removeClass('column-collapsed');
			self.table.find('td[data-column-key="' + $value +'"]').removeClass('column-collapsed');
			self.table.find('td[data-column-key="' + $value +'"] input:disabled.temp-disabled').prop( "disabled", false ).removeClass('temp-disabled');
			self.table.find('td[data-column-key="' + $value +'"] select:disabled.temp-disabled').prop( "disabled", false ).removeClass('temp-disabled');
			
		}
		// collapse the column
		else
		{
			selector.addClass('collapsed');
			option.addClass('collapsed');
			// update the cookie
			cookieVars[$value] = true;
			// hide the column
			self.table.find('th[data-column-key="' + $value +'"]').addClass('column-collapsed');
			self.table.find('td[data-column-key="' + $value +'"]').addClass('column-collapsed');
			self.table.find('td[data-column-key="' + $value +'"] input').addClass('temp-disabled').prop( "disabled", true );
			self.table.find('td[data-column-key="' + $value +'"] select').addClass('temp-disabled').prop( "disabled", true );
		}
		
		self.cookie.set(cookieName, cookieVars);
		self.element.trigger('reflow');
	},
	
	setColors: function()
	{
		var self = this;
		self.table.find('td[data-color-show="1"]').each(function( index )
		{
			self.setColor($(this));
		});
	},
	
	setColor: function(cell)
	{
		var self = this;
		if(cell.data('color-hex'))
			cell.attr('style', function(i,s) { return (s||'') + 'border-color: '+cell.data('color-hex')+' !important;' });
		
		if(cell.data('color-rgb'))
			cell.attr('style', function(i,s) { return (s||'') + ' background-color: '+cell.data('color-rgb')+' !important;' });
	}
});

// the default options
$.nihfo.objectTable.prototype.options = {
	cookieName: false,
	useJsExporting: true,
	useRowHighlighting: true,
	useCollapsibleColumns: true,
	floatHead: false,
	floatHeadOptions: {},
	sortable: false,
	sortableOptions: {
		sorted_url: false,
		containerSelector: 'table',
		itemPath: '> tbody',
		itemSelector: 'tr',
		placeholder: '<tr class="placeholder"/>',
		onDrop: function ($item, container, _super, event) { $.nihfo.objectTable.prototype.sortableOnDrop($item, container, _super, event); }
	},
	useGridedit: false,
	grideditOptions: {
		addUri: false,
		editUri: false,
		deleteUri: false
	},
	watchToggles: true,
	useJsOrdering: false,
	useJsSearch: false,
	orderingLastCell: false,
	orderingCookieName: false,
	useDragtable: true,
	dragtableOptions: {
	},
	setup: function(){},
	truncate: {
		'showText': 'Read More',
		'truncateString': '&hellip;'
	},
	autoLoadAjax: true,
	ajaxCountPool: []
}

})(jQuery);