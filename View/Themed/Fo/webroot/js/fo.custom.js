
			$.cookie = Cookies;
			// so we can use the jquery-ui version with tags.
			// this is temporary, as i'll move tags over to use the one below
			$.fn.autocompleteUI = $.fn.autocomplete;
jQuery.uaMatch = function( ua ) {
    ua = ua.toLowerCase();
    var match = /(chrome)[ \/]([\w.]+)/.exec( ua ) ||
        /(webkit)[ \/]([\w.]+)/.exec( ua ) ||
        /(opera)(?:.*version|)[ \/]([\w.]+)/.exec( ua ) ||
        /(msie) ([\w.]+)/.exec( ua ) ||
        ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec( ua ) || [];
    return {
        browser: match[ 1 ] || "",
        version: match[ 2 ] || "0"
    };
};
if ( !jQuery.browser ) {
    var 
    matched = jQuery.uaMatch( navigator.userAgent ),
    browser = {};
    if ( matched.browser ) {
        browser[ matched.browser ] = true;
        browser.version = matched.version;
    }
    // Chrome is Webkit, but Webkit is also Safari.
    if ( browser.chrome ) {
        browser.webkit = true;
    } else if ( browser.webkit ) {
        browser.safari = true;
    }
    jQuery.browser = browser;
}


function addCssRule(name, content) 
{ 
	var cssrules =  $("<style type='text/css'> </style>").appendTo("head");
	cssrules.append(name+" { "+content+" }"); 
}

function updateStatsValues( data )
{
	data = JSON.parse(data);
	// try to update any counts that may be present on the page
	$.each(data, function(key, value) {
		$( "#stat_value_item_state_"+key ).html(value);
	});
}


(function($, undefined){
	$.fn.foFieldSwitcher = function( options ) {
	
		var settings = $.extend({
			// These are the defaults.
			DefaultShow: 		'BInput',			// which to Show (the other is hidden)  AInput, or BInput
			HiddenSwitchId:		'#RuleUseSrcFog',	// #RuleUseSrcFog <-- hidden field that tracks the switching for the backend (A is 1, B is 0)
			AInput: {
				FieldID:		'#RuleSrcFogId',	// #RuleSrcIp <-- actual input id
				FieldDiv:		'#RuleSrcFogIdDiv',	// #RuleSrcIpDiv <-- div around input id
				FieldSwitcher:	'#UseRuleSrcIpId',	// #RuleUseSrcFog <-- link id that causes the switch
				HiddenSwitchValue: 1
			},
			BInput: {
				FieldID:		'#RuleSrcIp',		// #RuleSrcIp <-- actual input id
				FieldDiv:		'#RuleSrcIpDiv',	// #RuleSrcIpDiv <-- div around input id
				FieldSwitcher:	'#UseRuleSrcFogId',	// #RuleUseSrcFog <-- link id that causes the switch
				HiddenSwitchValue: 0
			}
			
		}, options );
		
		var activate = false;
		var ShowGroup = {};
		var ShowGroup_ids = {};
		var HideGroup = {};
		var HideGroup_ids = {};
		
		if (settings.DefaultShow == 'AInput')
		{
			activate = true;
			ShowGroup_ids = settings.AInput;
			HideGroup_ids = settings.BInput;
		}
		else if (settings.DefaultShow == 'BInput')
		{
			activate = true;
			ShowGroup_ids = settings.BInput;
			HideGroup_ids = settings.AInput;
		}
		if(!activate)
		{
			return false;
		}
		
		var HiddenSwitch = $( settings.HiddenSwitchId );
		
		ShowGroup = {
			FieldID: $( ShowGroup_ids.FieldID ),
			FieldDiv: $( ShowGroup_ids.FieldDiv ),
			FieldSwitcher: $( ShowGroup_ids.FieldSwitcher ),
			HiddenSwitchValue: ShowGroup_ids.HiddenSwitchValue
		};
		
		HideGroup = {
			FieldID: $( HideGroup_ids.FieldID ),
			FieldDiv: $( HideGroup_ids.FieldDiv ),
			FieldSwitcher: $( HideGroup_ids.FieldSwitcher ),
			HiddenSwitchValue: HideGroup_ids.HiddenSwitchValue
		};
		
		// set the initial hidden switch value
		HiddenSwitch.val(ShowGroup.HiddenSwitchValue);
		
		// hide the hide group
		HideGroup.FieldDiv.hide(); 
		HideGroup.FieldID.attr("disabled", "disabled");
		
		// show the show group
		ShowGroup.FieldDiv.show(); 
		ShowGroup.FieldID.removeAttr("disabled");
		
		// show the hidden group
		ShowGroup.FieldSwitcher.click(function(){
	
			HideGroup.FieldDiv.show(); 
			HideGroup.FieldID.removeAttr("disabled");
			ShowGroup.FieldDiv.hide(); 
			ShowGroup.FieldID.attr("disabled", "disabled");
			HiddenSwitch.val(HideGroup.HiddenSwitchValue);
			return false;
		});
		// switch back to the show group
		HideGroup.FieldSwitcher.click(function(){
	
			ShowGroup.FieldDiv.show(); 
			ShowGroup.FieldID.removeAttr("disabled");
			
			HideGroup.FieldDiv.hide(); 
			HideGroup.FieldID.attr("disabled", "disabled");
			HiddenSwitch.val(ShowGroup.HiddenSwitchValue);
			return false;
		});
		
		return;
	};
    
    $.fn.removePrefixedClasses = function (prefix) {
		var classNames = $(this).attr('class').split(' '),
			className,
			newClassNames = [],
			i;
		
		//loop class names
		for(i = 0; i < classNames.length; i++) {
			className = classNames[i];
			
			// if prefix not found at the beggining of class name
			if(className.indexOf(prefix) !== 0) {
				newClassNames.push(className);
				continue;
			}
		}
		
		// write new list excluding filtered classNames
		$(this).attr('class', newClassNames.join(' '));
	};
    
    //Function to convert hex format to a rgb color
	$.fn.rgb2hex = function (rgb)
	{
		rgb = rgb.match(/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i);
		return (rgb && rgb.length === 4) ? "#" +
			("0" + parseInt(rgb[1],10).toString(16)).slice(-2) +
			("0" + parseInt(rgb[2],10).toString(16)).slice(-2) +
			("0" + parseInt(rgb[3],10).toString(16)).slice(-2) : '';
	};
     
	$.fn.scrollFix = function(test) 
	{
		var object = $(this);
		$(window).scroll(function() {
			object.visible(false) ? object.removeClass("fixed") : object.addClass("fixed");
		});
	};
	
        
    $.fn.visible = function(partial,hidden,direction){
    
    		var $w = $(window);
            
            if (this.length < 1)
                return;
    
            var $t        = this.length > 1 ? this.eq(0) : this,
                t         = $t.get(0),
                vpWidth   = $w.width(),
                vpHeight  = $w.height(),
                direction = (direction) ? direction : 'both',
                clientSize = hidden === true ? t.offsetWidth * t.offsetHeight : true;
    
            if (typeof t.getBoundingClientRect === 'function'){
    
                // Use this native browser method, if available.
                var rec = t.getBoundingClientRect(),
                    tViz = rec.top    >= 0 && rec.top    <  vpHeight,
                    bViz = rec.bottom >  0 && rec.bottom <= vpHeight,
                    lViz = rec.left   >= 0 && rec.left   <  vpWidth,
                    rViz = rec.right  >  0 && rec.right  <= vpWidth,
                    vVisible   = partial ? tViz || bViz : tViz && bViz,
                    hVisible   = partial ? lViz || rViz : lViz && rViz;
    
                if(direction === 'both')
                    return clientSize && vVisible && hVisible;
                else if(direction === 'vertical')
                    return clientSize && vVisible;
                else if(direction === 'horizontal')
                    return clientSize && hVisible;
            } else {
    
                var viewTop         = $w.scrollTop(),
                    viewBottom      = viewTop + vpHeight,
                    viewLeft        = $w.scrollLeft(),
                    viewRight       = viewLeft + vpWidth,
                    offset          = $t.offset(),
                    _top            = offset.top,
                    _bottom         = _top + $t.height(),
                    _left           = offset.left,
                    _right          = _left + $t.width(),
                    compareTop      = partial === true ? _bottom : _top,
                    compareBottom   = partial === true ? _top : _bottom,
                    compareLeft     = partial === true ? _right : _left,
                    compareRight    = partial === true ? _left : _right;
    
                if(direction === 'both')
                    return !!clientSize && ((compareBottom <= viewBottom) && (compareTop >= viewTop)) && ((compareRight <= viewRight) && (compareLeft >= viewLeft));
                else if(direction === 'vertical')
                    return !!clientSize && ((compareBottom <= viewBottom) && (compareTop >= viewTop));
                else if(direction === 'horizontal')
                    return !!clientSize && ((compareRight <= viewRight) && (compareLeft >= viewLeft));
            }
        };
	
})(jQuery);