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
	},
	
	_closeView: function() {
		this.remove();
	
		if (this.onClose) { this.onClose(); }
		this.trigger('close');
		this.unbindAll();
		this.unbind();		
	}
});

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

		return this._renderView();
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
