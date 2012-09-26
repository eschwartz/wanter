/*
 * WanterApp.Flash
 * Handles all application-level flash messages
 *
 * Private methods:
 *		
 *
 * Public methods:
 *		showFlash(key, message)				Shows a flash message
 *		closeFlash(key)						Closes a flash message
 *
 */
WanterApp.module("Flash", function(ProductsApp, WanterApp, Backbone, Marionette, $, _) {
	
	/**
	 * Views
	*/	
	var _FlashView = Backbone.Marionette.ItemView.extend({
		template: '#flash-template',
		className: 'flash',
		
		onRender: function() {
			this.$el.hide().fadeIn();
		},
		
		beforeClose: function(resolve) {
			this.$el.fadeOut(resolve);
			
			return false;
		}
	});
	
	var _Flash = Backbone.Model.extend({
		idAttribute: "key",
		
		defaults: {
			key: null,
			value: "Please wait while something is happening."
		}
	});
	
	var _FlashCollection = Backbone.Collection.extend({
		model: _Flash
	});
	
	
	/**
	 * Private properties
	*/

	// Module-level event aggregator
	var _vent = new Backbone.Marionette.EventAggregator();
	
	// The app region where the flash should be shown
	// Note: should set this as an options
	var _flashRegion = WanterApp.flash;
	
	// A collection of _Flash'es
	var _queue = new _FlashCollection();
	
	var _currentFlashView = null;
	
	
	/**
	 * Private Methods
	*/
	
	// Adds a flash to the queue
	_addFlash = function(key, value) {
		var model = new _Flash({key: key, value: value});
		
		// Check for the existing model
		if(_queue.at(key)) return false;
		
		// Add the model to the queue
		_queue.push(model);
	}
	
	_removeFlash = function(key) {
		var model = _getFlash(key);
		_queue.remove(model);
	}
	
	// Returns the flash model for the given key
	_getFlash = function(key) {
		return _queue.get(key);
	}
	
	// Displays a flashView
	_showFlashView = function(view) {
		_flashRegion.show(view);
		_currentFlashView = view;
	}
	
	// Shows the first flash in the queue, and removes from queue
	_showNextFlash = function() {
		// If no queue, exit
		if(_queue.length < 1) return false;
		
		// Remove the first flash in the queue, and create a view for it
		var model = _queue.shift();
		var view = new _FlashView({ model: model });
		
		_showFlashView(view);
	}
	
	// Unset the current flash view
	_unsetCurrentFlashView = function() {
		_currentFlashView = null;
	}
	
	
	
	/**
	 * Public API
	*/
	_.extend(this, {
		
		// Adds a flash message to the queue
		setFlash: function(key, value, options) {			
			// Add the flash view to the queue
			_addFlash(key, value);
			
			// Show the flash view if there's not one open
			if(!_currentFlashView) {
				_showNextFlash();
			}
		},
		
		// Closes the flash with the given key
			// this is where we need to open the next queue (or in a bound event handler)
		closeFlash: function(key) {
			// See if it's the current view
			if(_currentFlashView && _currentFlashView.model.id === key) {
				_currentFlashView.close(_showNextFlash);
			}
			// Remove from the queue
			else {
				_removeFlash(key);
			}
		}
	});
	
	
	WanterApp.addInitializer(function(){
			console.log('hi');
		WanterApp.Flash.setFlash("loading", "Loading your thing.");
		WanterApp.Flash.setFlash("loadB", "loading B");
		WanterApp.Flash.setFlash("loadC", "loading C");

		window.setTimeout(function() {
			WanterApp.Flash.closeFlash("loading");		
		}, 1200);
		
		window.setTimeout(function() {
			WanterApp.Flash.closeFlash("loadB");		
		}, 500);
		
		window.setTimeout(function() {
			WanterApp.Flash.closeFlash("loadC");		
		}, 3500);
		
	});	
});


