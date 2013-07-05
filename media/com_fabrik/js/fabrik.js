/*jshint mootools: true */
/*global Fabrik:true, fconsole:true, Joomla:true, CloneObject:true $H:true,unescape:true,Asset:true,FloatingTips:true,head:true,IconGenerator:true */

/**
 *  This class is temporarily requied until this patch:
 *  https://github.com/joomla/joomla-platform/pull/1209/files
 *  makes it into the CMS code. Its purpose is to queue ajax requests so they are not
 *  all fired at the same time - which result in db session errors.
 *   
 *  Currently this is called from:
 *  fabriktables.js
 *  
 */

RequestQueue = new Class({
	
	queue: {}, // object of xhr objects
	
	initialize: function () {
		this.periodical = this.processQueue.periodical(500, this);
	},
	
	add: function (xhr) {
		var k = xhr.options.url + Object.toQueryString(xhr.options.data) + Math.random();
		if (!this.queue[k]) {
			this.queue[k] = xhr;
		}
	},
	
	processQueue: function () {
		if (Object.keys(this.queue).length === 0) {
			return;
		}
		var xhr = {},
		running = false;

		// Remove successfuly completed xhr
		$H(this.queue).each(function (xhr, k) {
			if (xhr.isSuccess()) {
				delete(this.queue[k]);
				running = false;
			}
		}.bind(this));
		
		// Find first xhr not run and completed to run
		$H(this.queue).each(function (xhr, k) {
			if (!xhr.isRunning() && !xhr.isSuccess() && !running) {
				xhr.send();
				running = true;
			}
		});
	},
	
	empty: function () {
		return Object.keys(this.queue).length === 0;
	}
});

Request.HTML = new Class({

	Extends: Request,
	
	options: {
		update: false,
		append: false,
		evalScripts: true,
		filter: false,
		headers: {
			Accept: 'text/html, application/xml, text/xml, */*'
		}
	},
	
	success: function (text) {
		var options = this.options, response = this.response;

		response.html = text.stripScripts(function (script) {
			response.javascript = script;
		});

		var match = response.html.match(/<body[^>]*>([\s\S]*?)<\/body>/i);
		if (match) {
			response.html = match[1];
		}
		var temp = new Element('div').set('html', response.html);

		response.tree = temp.childNodes;
		response.elements = temp.getElements(options.filter || '*');

		if (options.filter) {
			response.tree = response.elements;
		}
		if (options.update) {
			var update = document.id(options.update).empty();
			if (options.filter) {
				update.adopt(response.elements);
			} else {
				
				update.set('html', response.html);
			}
		} else if (options.append) {
			var append = document.id(options.append);
			if (options.filter) {
				response.elements.reverse().inject(append);
			} else {
				append.adopt(temp.getChildren());
			}
		}
		if (options.evalScripts) {
			Browser.exec(response.javascript);
		}

		this.onSuccess(response.tree, response.elements, response.html, response.javascript);
	}
	
	/*success: function (text) {
		var options = this.options, response = this.response;
		var srcs = text.match(/<script[^>]*>([\s\S]*?)<\/script>/gi);
		console.log(srcs);
		var urls = [];
		if (typeOf(srcs) !== 'null') {
			for (var x = 0; x < srcs.length; x++) {
				if (srcs[x].contains('src="')) {
					var m = srcs[x].match(/src=\"([\s\S]*?)\"/);
					if (m[1]) {
						urls.push(m[1]);
					}
				}
			}
			var scriptadd = "requirejs(['" + urls.join("','") + "'], function () {})";
			Browser.exec(scriptadd);
		}
		response.html = text.stripScripts(function (script) {
			response.javascript = script;
		});
		
		var match = response.html.match(/<body[^>]*>([\s\S]*?)<\/body>/i);
		if (match) {
			response.html = match[1];
		}
		var temp = new Element('div').set('html', response.html);

		response.tree = temp.childNodes;
		response.elements = temp.getElements('*');

		if (options.filter) {
			response.tree = response.elements.filter(options.filter);
		}
		if (options.update) {
			document.id(options.update).empty().set('html', response.html);
		}
		else if (options.append) {
			document.id(options.append).adopt(temp.getChildren());
		}
		if (options.evalScripts) {
			// response.javascript = "(function () {"+response.javascript+"}).delay(6000)";
			console.log(response.javascript);
			//Browser.exec(response.javascript);
			eval(response.javascript);
		}

		this.onSuccess(response.tree, response.elements, response.html, response.javascript);
	}*/
});

/**
 * Keeps the element posisiton in the center even when scroll/resizing
 */

Element.implement({
	keepCenter: function () {
		this.makeCenter();
		window.addEvent('scroll', function () {
			this.makeCenter();
		}.bind(this));
		window.addEvent('resize', function () {
			this.makeCenter();
		}.bind(this));
	},
	makeCenter: function () {
		var l = window.getWidth() / 2 - this.getWidth() / 2;
		var t = window.getScrollTop() + (window.getHeight() / 2 - this.getHeight() / 2);
		this.setStyles({left: l, top: t});
	}
});

/**
 * Loading aninimation class, either inline next to an element or 
 * full screen
 */

var Loader = new Class({
	
	initialize: function (options) {
		this.spinners = {};			
	},
	
	getSpinner: function (inline, msg) {
		msg = msg ? msg : Joomla.JText._('COM_FABRIK_LOADING');
		if (typeOf(document.id(inline)) === 'null') {
			inline = false;
		}
		inline = inline ? inline : false;
		var target = inline ? inline : document.body;
		if (!this.spinners[inline]) {
			this.spinners[inline] = new Spinner(target, {'message': msg});
		}
		return this.spinners[inline];
	},
	
	start: function (inline, msg) {
		this.getSpinner(inline, msg).position().show();
	},
	
	stop: function (inline, msg, keepOverlay) {
		var s = this.getSpinner(inline, msg);
		
		// Dont keep the spinner once stop is called - causes issue when loading ajax form for 2nd time
		if (Browser.ie && Browser.version < 9) {
			
			// Well ok we have to in ie8 ;( otherwise it give a js error somewhere in FX
			s.clearChain(); // Tried this to remove FX but didnt seem to achieve anything
			s.hide();
		} else {
			s.destroy();
		}
		delete this.spinners[inline];
	}
});

/**
 * Create the Fabrik name space
 */

if (typeof(Fabrik) === "undefined") {
	
	if (typeof(jQuery) !== 'undefined') {
		document.addEvent('click:relay(.popover button.close)', function (event, target) {
			var popover = '#' + target.get('data-popover');
			var pEl = document.getElement(popover);
			jQuery(popover).popover('hide');
			
			if (typeOf(pEl) !== 'null' && pEl.get('tag') === 'input') {
				pEl.checked = false;
			}
		});
	}
	Fabrik = {};
	Fabrik.events = {};
	Fabrik.Windows = {};
	Fabrik.loader = new Loader();
	Fabrik.blocks = {};
	Fabrik.addBlock = function (blockid, block) {
		Fabrik.blocks[blockid] = block;
		Fabrik.fireEvent('fabrik.block.added', [block, blockid]);
	};
	document.addEvent('click:relay(.fabrik_delete a, .fabrik_action a.delete, .btn.delete)', function (e, target) {
		if (e.rightClick) {
			return;
		}
		Fabrik.watchDelete(e, target);
	});
	document.addEvent('click:relay(.fabrik_edit a, a.fabrik_edit)', function (e, target) {
		if (e.rightClick) {
			return;
		}
		Fabrik.watchEdit(e, target);
	});
	document.addEvent('click:relay(.fabrik_view a, a.fabrik_view)', function (e, target) {
		if (e.rightClick) {
			return;
		}
		Fabrik.watchView(e, target);
	});
	
	Fabrik.removeEvent = function (type, fn) {
		if (Fabrik.events[type]) {
			var index = Fabrik.events[type].indexOf(fn);
			if (index !== -1) {
				delete Fabrik.events[type][index];
			}
		}
	};
	
	// Events test: replacing window.addEvents as they are reset when you reload mootools in ajax window.
	// need to load mootools in ajax window otherwise Fabrik classes dont correctly load
	Fabrik.addEvent = function (type, fn) {
		if (!Fabrik.events[type]) {
			Fabrik.events[type] = [];
		}
		if (!Fabrik.events[type].contains(fn)) {
			Fabrik.events[type].push(fn);
		}
	};
	
	Fabrik.addEvents = function (events) {
		for (var event in events) {
			Fabrik.addEvent(event, events[event]);
		}
		return this;
	};
	
	Fabrik.fireEvent = function (type, args, delay) {
		var events = Fabrik.events;
		
		// An array of returned values from all events.
		this.eventResults = [];
		if (!events || !events[type]) {
			return this;
		}
		args = Array.from(args);
		events[type].each(function (fn) {
			if (delay) {
				this.eventResults.push(fn.delay(delay, this, args));
			} else {
				this.eventResults.push(fn.apply(this, args));
			}
		}, this);
		return this;
	};
	
	Fabrik.requestQueue = new RequestQueue();
	
	Fabrik.cbQueue = {'google': []};
	
	/**
	 * Load the google maps API once 
	 * 
	 * @param  bool   s   Sensor
	 * @param  mixed  cb  Callback method function or function name (assinged to window)
	 * 
	 */
	
	Fabrik.loadGoogleMap = function (s, cb) {
		
		var prefix = document.location.protocol === 'https:' ? 'https:' : 'http:';
		var src = prefix + '//maps.googleapis.com/maps/api/js?&sensor=' + s + '&callback=Fabrik.mapCb';
		
		// Have we previously started to load the Googlemaps script?
		var gmapScripts = Array.from(document.scripts).filter(function (f) {
			return f.src === src;
		});
		
		if (gmapScripts.length === 0) {
			// Not yet loaded so create a script dom node and inject it into the page.
			var script = document.createElement("script");
			script.type = "text/javascript";
			script.src = src;
			document.body.appendChild(script);
			
			// Store the callback into the cbQueue, which will be processed after gmaps is loaded.
			Fabrik.cbQueue.google.push(cb);
		} else {
			// We've already added the Google maps js script to the document
			if (Fabrik.googleMap) {
				window[cb]();
				
				// $$$ hugh - need to fire these by hand, otherwise when re-using a map object, like
				// opening a popup edit for the second time, the map JS will never get these events.
				
				//window.fireEvent('google.map.loaded');
				//window.fireEvent('google.radius.loaded');
				
			} else {
				// We've started to load the Google Map code but the callback has not been fired.
				// Cache the call back (it will be fired when Fabrik.mapCb is run.
				Fabrik.cbQueue.google.push(cb);

			}
		}
	};
	
	/**
	 * Called once the google maps script has loaded, will run through any queued callback methods and 
	 * fire them.
	 */
	Fabrik.mapCb = function () {
		Fabrik.googleMap = true;
		var fn;
		for (var i = 0; i < Fabrik.cbQueue.google.length; i ++) {
			fn = Fabrik.cbQueue.google[i];
			if (typeOf(fn) === 'function') {
				fn();
			} else {
				window[fn]();
			}
		}
		Fabrik.cbQueue.google = [];
	};
	
	/** Globally observe delete links **/
	
	Fabrik.watchDelete = function (e, target) {
		var l, ref, r;
		r = e.target.getParent('.fabrik_row');
		if (!r) {
			r = Fabrik.activeRow;
		}
		if (r) {
			var chx = r.getElement('input[type=checkbox][name*=id]');
			if (typeOf(chx) !== 'null') {
				chx.checked = true;
			}
			ref = r.id.split('_');
			ref = ref.splice(0, ref.length - 2).join('_');
			l = Fabrik.blocks[ref];
		} else {
			// CheckAll
			ref = e.target.getParent('.fabrikList');
			if (typeOf(ref) !== 'null') {
				// Embedded in list
				ref = ref.id;
				l = Fabrik.blocks[ref];
			} else {
				// Floating
				var wrapper = target.getParent('.floating-tip-wrapper');
				if (wrapper) {
					var refList = wrapper.retrieve('list');
					ref = refList.id;
				} else {
					ref = target.get('data-listRef');
				}
				
				l = Fabrik.blocks[ref];
				// Depreacted in 3.1
				if (l.options.actionMethod === 'floating' && !this.bootstrapped) { // should only check all for floating tips
					l.form.getElements('input[type=checkbox][name*=id], input[type=checkbox][name=checkAll]').each(function (c) {
						c.checked = true;
					});
				}
			}
		}
		// Get correct list block
		if (!l.submit('list.delete')) {
			e.stop();
		}
	};
	
	/**
	 * Globally watch list edit links
	 * 
	 * @param   event    e       relayed click event
	 * @param   domnode  target  <a> link
	 * 
	 * @since 3.0.7
	 */
	Fabrik.watchEdit = function (e, target) {
		var listRef = target.get('data-list');
		var list = Fabrik.blocks[listRef];
		var row = list.getActiveRow(e);
		if (!list.options.ajax_links) {
			return;
		}
		e.preventDefault();
		if (!row) {
			return;
		}
		list.setActive(row);
		var rowid = row.id.split('_').getLast();
		if (list.options.links.edit === '') {
			url = Fabrik.liveSite + "index.php?option=com_fabrik&view=form&formid=" + list.options.formid + '&rowid=' + rowid + '&tmpl=component&ajax=1';
			loadMethod = 'xhr';
		} else {
			if (e.target.get('tag') === 'a') {
				a = e.target;
			} else {
				a = typeOf(e.target.getElement('a')) !== 'null' ? e.target.getElement('a') : e.target.getParent('a');
			}
			url = a.get('href');
			loadMethod = 'iframe';
		}
		// Only one edit window open at the same time.
		$H(Fabrik.Windows).each(function (win, key) {
			win.close();
		});
		
		var winOpts = {
			'id': 'add.' + listRef + '.' + rowid,
			'title': list.options.popup_edit_label,
			'loadMethod': loadMethod,
			'contentURL': url,
			'width': list.options.popup_width,
			'height': list.options.popup_height,
			'onClose': function (win) {
				try {
					var k = 'form_' + list.options.formid + '_' + rowid;
					Fabrik.blocks[k].destroyElements();
					Fabrik.blocks[k].formElements = null;
					Fabrik.blocks[k] = null;
					delete(Fabrik.blocks[k]);
				} catch (e) {}
			}
		};
		if (typeOf(list.options.popup_offset_x) !== 'null') {
			winOpts.offset_x = list.options.popup_offset_x;
		}
		if (typeOf(list.options.popup_offset_y) !== 'null') {
			winOpts.offset_y = list.options.popup_offset_y;
		}
		Fabrik.getWindow(winOpts);
	};
	
	/**
	 * Globally watch list view links
	 * 
	 * @param   event    e       relayed click event
	 * @param   domnode  target  <a> link
	 * 
	 * @since 3.0.7
	 */
	
	Fabrik.watchView = function (e, target) {
		var listRef = target.get('data-list');
		var list = Fabrik.blocks[listRef];
		if (!list.options.ajax_links) {
			return;
		}
		e.preventDefault();
		var row = list.getActiveRow(e);
		if (!row) {
			return;
		}
		list.setActive(row);
		var rowid = row.id.split('_').getLast();
		if (list.options.links.detail === '') {
			url = Fabrik.liveSite + "index.php?option=com_fabrik&view=details&formid=" + list.options.formid + '&rowid=' + rowid + '&tmpl=component&ajax=1';
			loadMethod = 'xhr';
		} else {
			if (e.target.get('tag') === 'a') {
				a = e.target;
			} else {
				a = typeOf(e.target.getElement('a')) !== 'null' ? e.target.getElement('a') : e.target.getParent('a');
			}
			url = a.get('href');
			loadMethod = 'iframe';
		}
		// Only one edit window open at the same time.
		$H(Fabrik.Windows).each(function (win, key) {
			win.close();
		});
		
		var winOpts = {
			'id': 'view.' + '.' + listRef + '.' + rowid,
			'title': list.options.popup_view_label,
			'loadMethod': loadMethod,
			'contentURL': url,
			'width': list.options.popup_width,
			'height': list.options.popup_height,
			'onClose': function (win) {
				var k = 'details_' + list.options.formid + '_' + rowid;
				try {
					Fabrik.blocks[k].destroyElements();
					Fabrik.blocks[k].formElements = null;
					Fabrik.blocks[k] = null;
					delete(Fabrik.blocks[k]);	
				} catch (e) {
					console.log(e);
				}
				
			}
		};
		if (typeOf(list.options.popup_offset_x) !== 'null') {
			winOpts.offset_x = list.options.popup_offset_x;
		}
		if (typeOf(list.options.popup_offset_y) !== 'null') {
			winOpts.offset_y = list.options.popup_offset_y;
		}
		Fabrik.getWindow(winOpts);
	};
	
	Fabrik.form = function (ref, id, opts) {
		var form = new FbForm(id, opts);
		Fabrik.addBlock(ref, form);
		return form;
	};
	
	window.fireEvent('fabrik.loaded');
}
