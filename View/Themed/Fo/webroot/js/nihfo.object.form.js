(function($) 
{
/**
 * Always attach to the div tag that is the direct parent to the form
 * see http://www.wufoo.com/html5/ for html5 form elements
 */
$.widget( "nihfo.objectForm", $.nihfo.objectBase, 
{
	options: {},
	
	_create: function() 
	{
		var self = this;
		self._super();
		self.element.addClass( "nihfo-object-form" );
		self.refresh();
	},
	
	_destroy: function() 
	{
		var self = this;
		self._super();
		self.element.removeClass( "nihfo-object-form" );
	},
	
	refresh: function() 
	{
		var self = this;
		self.setOptions();
		self.attachObjects();
	},
	
	setOptions: function() 
	{
		var self = this;
		self.options.id = self.element.attr('id');
	},
	
	attachObjects: function()
	{
		var self = this;
			
		// searchable dropdowns
		self.element.find('select.multiselect').chosen(self.options.chosenOptions).addClass('attached-chosen');
		self.element.find('select.chosen').chosen(self.options.chosenOptions).addClass('attached-chosen');
		self.element.find('select.searchable').chosen(self.options.chosenOptions).addClass('attached-chosen');
		// selects with more then 5 options
		self.element.find('select:not(.not-chosen)').each(function(index){
			if($(this).children('option').length > self.options.chosenOptions.disable_search_threshold)
				$(this).chosen(self.options.chosenOptions).addClass('attached-chosen');
		});
		
		// forcing numbers only in the number input fields
		self.element.find('input[type="number"].numeric').numeric({decimal : ".", decimalPlaces : 2 }).addClass('attached-numeric');
		
		self.addAutocompletes();
		self.addToggles();
		self.addClearButtons();
		self.addCalendars();
	},
	
	addAutocompletes: function()
	{
		var self = this;
		
		// add autocompletes
		self.element.find('input[rel].input-autocomplete').each(function(index){
			var input = $(this);
			input.autocomplete({
				serviceUrl: input.attr('rel'),
				preserveInput: true,
				onSearchStart: function(query) {
					// make the search value lowercase
					var value = input.val().toLowerCase();
					query.query = value;
					input.val(value);
				},
				onSelect: function(suggestion) {
					input.val(suggestion.data.value.toLowerCase());
				},
				ajaxSettings: {
					beforeSend: function( jqXHR, settings ) { self.ajaxBeforeSend(jqXHR, settings); },
					success: function( data, textStatus, jqXHR ) { self.ajaxSuccess(data, textStatus, jqXHR); },
					error: function( jqXHR, textStatus, errorThrown ) { self.ajaxError(jqXHR, textStatus, errorThrown); },
					done: function( data, textStatus, jqXHR ){ self.ajaxDone(data, textStatus, jqXHR); }
				}
			});
		});
	},
	
	addToggles: function()
	{
		// add the toggle/switch checkboxes
		var self = this;
		
		self.element.find('input[type="checkbox"]:not(.has-toggle).input-toggle').each(function(index){
			var input = $(this);
			var parent = input.parents('div.input-toggle');
			var icon = parent.find('i.input-toggle').first();
			input.addClass('has-toggle');
			parent.addClass('has-toggle');
			
			if(input.attr("checked"))
			{
				icon.removeClass('fa-toggle-off').addClass('fa-toggle-on');
			}
			else
			{
				icon.removeClass('fa-toggle-on').addClass('fa-toggle-off');
			}
			
			icon.on('click', function(event){
				event.preventDefault();
				if($(this).hasClass('fa-toggle-on'))
				{
					$(this).removeClass('fa-toggle-on').addClass('fa-toggle-off');
					input.removeAttr("checked");
				}
				else
				{
					$(this).removeClass('fa-toggle-off').addClass('fa-toggle-on');
					input.attr("checked", 'checked');
				}
			});
		});
	},
	
	addClearButtons: function()
	{
		var self = this;
		
		var width = false;
		self.element.find('input.clearable').each(function(index)
		{
			$(this).wrap('<div class="clearable-wrapper"></div>').on('change', function() 
			{
				$(this).next().css('display', ($(this).val() !== '') ? 'inline-block' : 'none');
			})
			.parent().css({backgroundSize: '0 0', width: width})
			.append($('<i class="fa fa-times clearable-button" aria-hidden="true"></i>').click(function() {
            	$(this).hide().prev().val('');
            	$(this).prev().parent().parent().find('input.clearable-hidden').val(''); // for the hidden field for dates.
        	}));
        	$(this).next().css('display', ($(this).val() !== '') ? 'inline-block' : 'none');
        });
	},
	
	addCalendars: function()
	{
		var self = this;
		$.datepicker._gotoToday = function(id) {
			var target = $(id);
			var inst = this._getInst(target[0]);
			if (this._get(inst, 'gotoCurrent') && inst.currentDay) {
				inst.selectedDay = inst.currentDay;
				inst.drawMonth = inst.selectedMonth = inst.currentMonth;
				inst.drawYear = inst.selectedYear = inst.currentYear;
			}
			else {
				var date = new Date();
				inst.selectedDay = date.getDate();
				inst.drawMonth = inst.selectedMonth = date.getMonth();
				inst.drawYear = inst.selectedYear = date.getFullYear();
				// the below two lines are new
				this._setDateDatepicker(target, date);
				this._setTime(inst, date);
				this._setDate(inst, date);
				this._selectDate(id, this._getDateDatepicker(target));
			}
			this._notifyChange(inst);
			this._adjustDate(target);
		}
		
		self.element.find('input[data-datepicker-options]').each(function(index){
			var datePickerOptions = $(this).data('datepicker-options');
			if(datePickerOptions.textField !== undefined)
				$(datePickerOptions.textField).datetimepicker(datePickerOptions);
			else
			$(this).datetimepicker(datePickerOptions);
			
		});
	}
});

// the default options
$.nihfo.objectForm.prototype.options = {
	id: false,
	chosenOptions: {
		width:"99%",
		disable_search_threshold: 5,
		search_contains: true
	}
}

})(jQuery);