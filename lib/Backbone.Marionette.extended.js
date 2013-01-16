/**
 * Modified version of Backbone.Marionette.View.delegateEvents
 * Allows to delegate events to a named elements in this.selectors
 * eg. ui: { myButton: '#myButton' }, events: { 'click myButton': 'myCallback'}
*/

// NOTE: should submit a pull request for Marionette. This is pretty sweet, imho.



/* Helper Functions required for delegateEvents */
// Helper function to get a value from a Backbone object as a property
// or as a function.
var getValue = function(object, prop) {
if (!(object && object[prop])) return null;
return _.isFunction(object[prop]) ? object[prop]() : object[prop];
};

var delegateEventSplitter = /^(\S+)\s*(.*)$/;


Backbone.Marionette.View.prototype.delegateEvents = function(events) {
  if (!(events || (events = getValue(this, 'events')))) return;
  this.undelegateEvents();
  for (var key in events) {
	// Determine callback method
	var method = events[key];
	if (!_.isFunction(method)) method = this[events[key]];
	if (!method) throw new Error('Method "' + events[key] + '" does not exist');
	
	// Split up selector and event binding
	var match = key.match(delegateEventSplitter);
	var eventName = match[1];
	
	// Check for named selector
	var	selector = (this.ui && _.has(this.ui, match[2]))? this.ui[match[2]]: match[2];
	
	// Bind the event to the DOM object
	method = _.bind(method, this);
	eventName += '.delegateEvents' + this.cid;
	if (selector === '') {
	  this.$el.bind(eventName, method);
	} else {
	  this.$el.delegate(selector, eventName, method);
	}
  }
}



/**
 * beforeClose, now with deferreds!
 * Allows you to wait to call close until beforeClose has completed.
 * Very useful if beforeClose is calling an animation or async.
 * beforeClose is passed a deferred.resolve method as a parameter.
 * calling the resolve method will allow the closing to continue
 * eg.
 
 beforeClose: function(close) {
	 this.$el.hide().fadeOut(close);
	 return false;				// Must return false to use the deferred
 }
*/

// NOTE: should submit a pull request for Marionette. This is pretty sweet, imho.

window.noop = function() {}

_.extend(Backbone.Marionette.View.prototype, {
	close: function(callback) {
		callback = (callback && _.isFunction(callback))? callback : window.noop;
		
		if (this.beforeClose) {
			
			// if beforeClose returns false, wait for beforeClose to resolve before closing
			// Before close calls `run` parameter to continue with closing element
			var dfd = $.Deferred(), close = dfd.resolve, self = this;
			if(this.beforeClose(close) === false) {
				dfd.done(function() {
					self._closeView();				// call _closeView, making sure our context is still `this`
					callback.call(self);
				});
				return true;
			}
		}
		
		// Run close immediately if beforeClose does not return false
		this._closeView();
		callback();
	},
	
	_closeView: function() {
		this.remove();
	
		if (this.onClose) { this.onClose(); }
		this.trigger('close');
		this.unbindAll();
		this.unbind();		
	}
});


// Handle beforeClose on regions, allowing callbacks
_.extend(Backbone.Marionette.Region.prototype, {
	close: function(callback) {
		var view = this.currentView;
		var self = this;
		
		callback = (callback && _.isFunction(callback))? callback : window.noop;
		
		if (!view){ 
			callback.call(this);
			return; 
		}
	
		if (view.close) { 
			view.close(function() {
				callback();
				self.trigger("view:closed", view);
			});
		}
	
		delete this.currentView;
		
	}
});

/* beforeRender, with deferreds, callbacks */
_.extend(Backbone.Marionette.ItemView.prototype, {
	
	
	render: function(callback) {
		callback = (callback && _.isFunction(callback))? callback: window.noop;
		
		if(this.beforeRender) {
			var dfd = $.Deferred(), render = dfd.resolve, self = this;
			if(this.beforeRender(render) === false) {
				dfd.done(function() {
					self._renderView();
					callback.call(self);
				});
				return true;
			}
		}
		
		var renderResult = this._renderView();
		callback();
		return renderResult;
	},
	
	_renderView: function() {
		this.trigger("before:render", this);
		this.trigger("item:before:render", this);
	
		var data = this.serializeData();
		var template = this.getTemplate();
		var html = Backbone.Marionette.Renderer.render(template, data);
		this.$el.html(html);
		this.bindUIElements();
	
		if (this.onRender){ this.onRender(); }
		this.trigger("render", this);
		this.trigger("item:rendered", this);
		return this;
	}
});


/**
 * IMO, attachview should bind UI elements. Why not?
 */
_.extend(Backbone.Marionette.Region.prototype, {
	attachView: function(view) {
		view.bindUIElements();					// Added line
		this.currentView = view;
	}
});



// Messing around with router. 
	// Seems silly that I have to call `BB.h.nav("route")` on every single one.
	// Can't we do that automatically?
/* This don't work but it would be nice
 Problem: how do you handle arguments sent in route. There's probably a way, but not as simple as I thought.
	_.extend(Backbone.Marionette.AppRouter.prototype, {
		processAppRoutes: function(controller, appRoutes) {
			var method, methodName;
			var route, routesLength, i;
			var routes = [];
			var router = this;
		
			for(route in appRoutes){
			  if (appRoutes.hasOwnProperty(route)){
				routes.unshift([route, appRoutes[route]]);
			  }
			}
		
			routesLength = routes.length;
			for (i = 0; i < routesLength; i++){
				route = routes[i][0];
				methodName = routes[i][1];
				method = controller[methodName];
				
				if (!method){
					var msg = "Method '" + methodName + "' was not found on the controller";
					var err = new Error(msg);
					err.name = "NoMethodError";
					throw err;
				}
				
				
				// Custom shortcut code: Add "Backbone.history.navigate("routeName") to all controller methods.
				controller[methodName] = _.compose(function() {
					Backbone.history.navigate(route);
				}, controller[methodName]);
				// end custom code
				
				method = _.bind(method, controller);
				router.route(route, methodName, method);
			}
		}
	});
*/