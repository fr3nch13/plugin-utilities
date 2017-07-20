/*
 * Originally copied from:
 *
 * Purl (A JavaScript URL parser) v2.3.1
 * Developed and maintanined by Mark Perkins, mark@allmarkedup.com
 * Source repository: https://github.com/allmarkedup/jQuery-URL-Parser
 * Licensed under an MIT-style license. See https://github.com/allmarkedup/jQuery-URL-Parser/blob/master/LICENSE for details.
 */

;(function(factory) {
	if (typeof define === 'function' && define.amd) {
		define(factory);
	} else {
		window.purl = factory();
	}
})(function() {

	var tag2attr = {
			a	   : 'href',
			img	 : 'src',
			form	: 'action',
			base	: 'href',
			script  : 'src',
			iframe  : 'src',
			link	: 'href',
			embed   : 'src',
			object  : 'data'
		},

		key = ['source', 'protocol', 'authority', 'userInfo', 'user', 'password', 'host', 'port', 'relative', 'path', 'directory', 'file', 'query', 'fragment', 'named', 'pass', 'passedArgs'], // keys available to query

		aliases = { 'anchor' : 'fragment' }, // aliases for backwards compatability

		parser = {
			strict : /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,  //less intuitive, more accurate to the specs
			loose :  /^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/)?((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/ // more intuitive, fails on relative paths and deviates from specs
		},

		isint = /^[0-9]+$/;

	function parseUri( url, strictMode ) {
		var str = decodeURI( url ),
			res   = parser[ strictMode || false ? 'strict' : 'loose' ].exec( str ),
			uri = { attr : {}, param : {}, seg : {}, named : {}, pass: {}, passedArgs: {}, query: {} },
			i   = 14;

		while ( i-- ) {
			uri.attr[ key[i] ] = res[i] || '';
		}

		// build query and fragment parameters
		uri.param['query'] = parseString(uri.attr['query']);
		uri.param['fragment'] = parseString(uri.attr['fragment']);

		// split path and fragement into segments
		uri.seg['path'] = uri.attr.path.replace(/^\/+|\/+$/g,'').split('/');
		uri.seg['fragment'] = uri.attr.fragment.replace(/^\/+|\/+$/g,'').split('/');
	
		// get and parse the named parts (field:value)
		i = uri.seg.path.length
		while ( i-- ) {
			if(/:/.test(uri.seg.path[i]))
			{
				named = uri.seg.path[i];
				
				// remove named from directory
				uri.attr.directory = uri.attr.directory.replace('/'+named, '');
				
				named_parts = named.split(':');
				uri.named[named_parts[0]] = named_parts[1];
			}
		}
		
		// append to the base attribute
		uri.attr['base'] = [];
		var devBases = ['1.0', '2.0', 'trunk', 'trunk.2'];
		if(uri.seg.path.length > 1)
		{
			var indev = false;
			if(typeof uri.seg.path[0] !== 'undefined') 
			{
				uri.attr['base'].push(uri.seg.path[0]);
				if(devBases.indexOf(uri.seg.path[0]) !== -1)
				{
					indev = true;
				}
				if(indev && typeof uri.seg.path[1] !== 'undefined')
				{
					uri.attr['base'].push(uri.seg.path[1]);
				}
			}
		}
		uri.attr['base'] = '/'+uri.attr['base'].join('/')+'';
		
		// try to figure out the controller and action
		uri.attr['controller'] = '';
		uri.attr['action'] = 'index'; // usually always the default
		
		uri.attr.directory = uri.attr.directory.replace(uri.attr['base'], '');
		var leftover = uri.attr.directory.replace(/(^\s*\/+|\/+\s*$)/, '').split('/');
		if(leftover.length)
		{
			uri.attr['controller'] = leftover[0];
			if(leftover.length > 1)
			{
				uri.attr['action'] = leftover[1];
				leftover.splice(1, 1);
			}
			leftover.splice(0, 1);
		}
		
		var i = 0;
		while ( i < leftover.length ) {
			uri.pass['"'+i+'"'] = leftover[i];
			i++;
		}
		
		for (var i in uri.pass) {
			uri.passedArgs[i] = uri.pass[i];
		}
		for (var i in uri.named) {
			uri.passedArgs[i] = uri.named[i];
		}
		// compile a 'base' domain attribute
		uri.attr['base_absolute'] = (uri.attr.host ? (uri.attr.protocol ?  uri.attr.protocol+'://'+uri.attr.host : uri.attr.host) + (uri.attr.port ? ':'+uri.attr.port : '') : '') + uri.attr.base;
		
		return uri;
	}

	function getAttrName( elm ) {
		var tn = elm.tagName;
		if ( typeof tn !== 'undefined' ) return tag2attr[tn.toLowerCase()];
		return tn;
	}

	function promote(parent, key) {
		if (parent[key].length === 0) return parent[key] = {};
		var t = {};
		for (var i in parent[key]) t[i] = parent[key][i];
		parent[key] = t;
		return t;
	}

	function parse(parts, parent, key, val) {
		var part = parts.shift();
		if (!part) {
			if (isArray(parent[key])) {
				parent[key].push(val);
			} else if ('object' == typeof parent[key]) {
				parent[key] = val;
			} else if ('undefined' == typeof parent[key]) {
				parent[key] = val;
			} else {
				parent[key] = [parent[key], val];
			}
		} else {
			var obj = parent[key] = parent[key] || [];
			if (']' == part) {
				if (isArray(obj)) {
					if ('' !== val) obj.push(val);
				} else if ('object' == typeof obj) {
					obj[keys(obj).length] = val;
				} else {
					obj = parent[key] = [parent[key], val];
				}
			} else if (~part.indexOf(']')) {
				part = part.substr(0, part.length - 1);
				if (!isint.test(part) && isArray(obj)) obj = promote(parent, key);
				parse(parts, obj, part, val);
				// key
			} else {
				if (!isint.test(part) && isArray(obj)) obj = promote(parent, key);
				parse(parts, obj, part, val);
			}
		}
	}

	function merge(parent, key, val) {
		if (~key.indexOf(']')) {
			var parts = key.split('[');
			parse(parts, parent, 'base', val);
		} else {
			if (!isint.test(key) && isArray(parent.base)) {
				var t = {};
				for (var k in parent.base) t[k] = parent.base[k];
				parent.base = t;
			}
			if (key !== '') {
				set(parent.base, key, val);
			}
		}
		return parent;
	}

	function parseString(str) {
		return reduce(String(str).split(/&|;/), function(ret, pair) {
			try {
				pair = decodeURIComponent(pair.replace(/\+/g, ' '));
			} catch(e) {
				// ignore
			}
			var eql = pair.indexOf('='),
				brace = lastBraceInKey(pair),
				key = pair.substr(0, brace || eql),
				val = pair.substr(brace || eql, pair.length);

			val = val.substr(val.indexOf('=') + 1, val.length);

			if (key === '') {
				key = pair;
				val = '';
			}

			return merge(ret, key, val);
		}, { base: {} }).base;
	}

	function set(obj, key, val) {
		var v = obj[key];
		if (typeof v === 'undefined') {
			obj[key] = val;
		} else if (isArray(v)) {
			v.push(val);
		} else {
			obj[key] = [v, val];
		}
	}

	function lastBraceInKey(str) {
		var len = str.length,
			brace,
			c;
		for (var i = 0; i < len; ++i) {
			c = str[i];
			if (']' == c) brace = false;
			if ('[' == c) brace = true;
			if ('=' == c && !brace) return i;
		}
	}

	function reduce(obj, accumulator){
		var i = 0,
			l = obj.length >> 0,
			curr = arguments[2];
		while (i < l) {
			if (i in obj) curr = accumulator.call(undefined, curr, obj[i], i, obj);
			++i;
		}
		return curr;
	}

	function isArray(vArg) {
		return Object.prototype.toString.call(vArg) === "[object Array]";
	}

	function keys(obj) {
		var key_array = [];
		for ( var prop in obj ) {
			if ( obj.hasOwnProperty(prop) ) key_array.push(prop);
		}
		return key_array;
	}

	function purl( url, strictMode ) {
		if ( arguments.length === 1 && url === true ) {
			strictMode = true;
			url = undefined;
		}
		strictMode = strictMode || false;
		url = url || window.location.toString();

		return {

			data : parseUri(url, strictMode),

			// get various attributes from the URI
			attr : function( key, value ) {
				key = aliases[key] || key;
				if(key && typeof value !== 'undefined')
				{
					if(!value) 
					{
						if(this.data.attr[key]) delete this.data.attr[key];
					}
					else
					{
						this.data.attr[key] = value;
					}
				}
				return typeof key !== 'undefined' ? this.data.attr[key] : this.data.attr;
			},

			// return query string parameters
			param : function( param, value, remove ) {
				if(param && typeof value !== 'undefined')
				{
					if(!value && remove) 
					{
						if(this.data.param.query[param]) delete this.data.param.query[param];
					}
					else
					{
						this.data.param.query[param] = value;
					}
				}
				return typeof param !== 'undefined' ? this.data.param.query[param] : this.data.param.query;
			},
			// alias for param
			query: function( param, value ) {
				return this.param( param, value );
			},
			
			queryCompiled: function () {
				var out = [];
				var queries = this.query();
				if(queries)
				{
					i=0;
					$.each( queries, function( key, value ) {
						out[i] = encodeURIComponent(key)+'='+encodeURIComponent(value);
						i++;
					});
				}
				return out.join('&');
			},

			// return fragment parameters
			fparam : function( param, value ) {
				if(param && typeof value !== 'undefined')
				{
					if(!value)
					{ 
						if(this.data.param.fragment[param]) delete this.data.param.fragment[param];
					}
					else
					{
						this.data.param.fragment[param] = value;
					}
				}
				return typeof param !== 'undefined' ? this.data.param.fragment[param] : this.data.param.fragment;
			},
			
			// alias for fparam
			hash: function( param, value ) {
				return this.fparam( param, value );
			},
			
			hashCompiled: function () {
				var out = [];
				var hashes = this.hash();
				if(hashes)
				{
					i = 0;
					$.each( hashes, function( key, value ) {
						if(value)
							out[i] = encodeURIComponent(key)+'='+encodeURIComponent(value);
						else
							out[i] = encodeURIComponent(key);
						i++;
					});
				}
				return out.join('&');
			},

			// return path segments
			segment : function( seg ) {
				if ( typeof seg === 'undefined' ) {
					return this.data.seg.path;
				} else {
					seg = seg < 0 ? this.data.seg.path.length + seg : seg - 1; // negative segments count from the end
					return this.data.seg.path[seg];
				}
			},

			// return fragment segments
			fsegment : function( seg ) {
				if ( typeof seg === 'undefined' ) {
					return this.data.seg.fragment;
				} else {
					seg = seg < 0 ? this.data.seg.fragment.length + seg : seg - 1; // negative segments count from the end
					return this.data.seg.fragment[seg];
				}
			},

			// return query string parameters
			named : function( named, value ) {
				if(named && typeof value !== 'undefined')
				{
					if(!value) 
					{
						if(this.data.named[named]) delete this.data.named[named];
					}
					else
					{
						this.data.named[named] = value;
					}
				}
				return typeof named !== 'undefined' ? this.data.named[named] : this.data.named;
			},
			
			namedCompiled: function () {
				var out = [];
				var named = this.named();
				if(named)
				{
					i = 0;
					$.each( named, function( key, value ) {
						if(key == 'q')
						{
							out[i] = encodeURIComponent(key)+':'+value;
						}
						else
						{
							out[i] = encodeURIComponent(key)+':'+encodeURIComponent(value);
						}
						i++;
					});
				}
				return out.join('/');
			},

			// return query string parameters
			pass : function( key, value ) {
				if(key && typeof value !== 'undefined')
				{
					if(!value) 
					{
						if(this.data.pass[key]) delete this.data.pass[key];
					}
					else
					{
						this.data.pass[key] = value;
					}
				}
				return typeof key !== 'undefined' ? this.data.pass[key] : this.data.pass;
			},

			// return query string parameters
			passedArgs : function( key, value ) {
				if(key && typeof value !== 'undefined')
				{
					if(!value) 
					{
						if(this.data.passedArgs[key]) delete this.data.passedArgs[key];
					}
					else
					{
						this.data.passedArgs[key] = value;
					}
				}
				return typeof key !== 'undefined' ? this.data.pass[key] : this.data.passedArgs;
			},
			
			compiled : function() {
			
				var compiled = '';
				var compiled_parts = [this.data.attr['base'], this.data.attr['directory'].replace(/(^\/|\/$)/g, '')];
				
				// add the named paramaters
				compiled = compiled_parts.join('/');
				compiled = compiled.replace(/\/+/g, "/");
				
				// named paramaters that cakephp uses
				var namedCompiled = this.namedCompiled();
				if(namedCompiled)
					compiled += '/'+namedCompiled;
				
				// the query string ex: ?this=that&you=notme
				var queryCompiled = this.queryCompiled();
				if(queryCompiled)
					compiled += '?'+queryCompiled;
				
				// that hash tag with paramaters
				var hashCompiled = this.hashCompiled();
				if(hashCompiled)
					compiled += '#'+hashCompiled;
				
				return compiled;
			},
			
			updateHash: function(){
				var hashesCompiled = this.hashCompiled();
				if(hashesCompiled)
					window.location.hash = '#'+hashesCompiled;
				
//				window.history.pushState(null, null, this.compiled());
			},
			
			updateQuery: function(){
				var compiled = this.compiled();
				window.history.pushState(null, null, compiled);
			},
			
			updateHistory: function(){
				var compiled = this.compiled();
				window.history.pushState(null, null, compiled);
			}
		};
	}
	
	purl.jQuery = function($){
		if ($ != null) {
			$.fn.url = function( strictMode ) {
				var url = '';
				if ( this.length ) {
					url = $(this).attr( getAttrName(this[0]) ) || '';
				}
				return purl( url, strictMode );
			};

			$.url = purl;
		}
	};

	purl.jQuery(window.jQuery);

	return purl;

});