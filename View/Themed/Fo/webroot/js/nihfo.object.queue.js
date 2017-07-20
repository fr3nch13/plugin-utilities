(function($) 
{
/**
 * Always attach to the div tag that is the direct parent to the queue
 * see http://www.wufoo.com/html5/ for html5 queue elements
 */
$.widget( "nihfo.objectQueue", $.nihfo.objectBase, 
{
	options: {},
	
	_create: function() 
	{
		var self = this;
		self._super();
		self.element.addClass( "nihfo-object-queue" );
		self.refresh();
	},
	
	_destroy: function() 
	{
		var self = this;
		self._super();
		self.element.removeClass( "nihfo-object-queue" );
	},
	
	refresh: function() 
	{
		var self = this;
		self.setOptions();
		self.getProgress();
	},
	
	setOptions: function() 
	{
		var self = this;
		self.options.id = self.element.attr('id');
	}, 
	
	getProgress: function()
	{
		var self = this;
		if(!self.options.checkUrl)
			return true;
		
		var progressElement = self.element;
		
		var queueId = progressElement.data('queueid');
		if(!queueId)
			return true;
		
		var progressLabelElement = progressElement.find( ".progress-label" );
		var progressBarElement = progressElement.find( ".progress-bar" );
		
		progressElement.progressbar({
			value: false,
			change: function() {
				
			},
			complete: function() {
				progressBarElement.hide();
			}
		});
		
		// click the bar to refresh the progress. this is temporary until the timeout refresh can be added
		progressElement.on('click', function(event){
			event.preventDefault();
			self.updateProgress();
		});
		
		self.updateProgress();
	},
	
	updateProgress: function()
	{
		var self = this;
		var progressElement = self.element;
		var queueId = progressElement.data('queueid');
		var progressLabelElement = progressElement.find( ".progress-label" );
		var progressBarElement = progressElement.find( ".progress-bar" );
		
		var data = { queueId: queueId };
		
		setTimeout(function() {
			$.ajax({
				async: true,
				type: 'POST',
				url: self.options.checkUrl,
				data: data,
				beforeSend: function(jqXHR, settings) 
				{
					progressLabelElement.text('...');
				},
				success: function(content, textStatus, jqXHR)
				{
					progressElement.progressbar("value", (content.progress.progress * 100));
					progressLabelElement.text( content.progress.status_label );
					progressLabelElement.attr("title", progressElement.progressbar( "value" ) + "%");
					progressElement.addClass(content.progress.status);
					if (content.progress.progress < 1) {
						self.updateProgress();
					}
				},
				error: function(jqXHR, textStatus, errorThrown)
				{
					progressElement.hide();
				}
			});
		}, 5000);
	}
	
});

// the default options
$.nihfo.objectQueue.prototype.options = {
	id: false,
	checkUrl: false,
}

})(jQuery);