// WanterApp.Flash

WanterApp.module("Flash", function(ProductsApp, WanterApp, Backbone, Marionette, $, _) {
	
	var options = {
		queue: true
	}
	
	var _vent = new Backbone.Marionette.EventAggregator();
	
	var _currentView = null;
	
	/**
	 * Views
	*/
	var _FlashView = Backbone.Marionette.ItemView.extend({
		template: '#flash-template',
		className: 'flash',
		
		initialize: function() {
			var collection  = this.model.collection;
			var self = this;
			
			_.bindAll(this, "showFirstItem");
			
			this.bindTo(collection, "remove", this.showFirstItem);
		},
		
		// Returns true if this is the first in the queue (collection view)
		isFirst: function() {
			return (this.$el.index() === 0);
		},

		// Show the first item in the queue
		showFirstItem: function() {
			if(this.isFirst()) {
				this.$el.show();
			}
			else {
				this.$el.hide();
			}
		},
		
		beforeClose: function(resolve) {
			var self = this;
			
			// Move to the end, so I'm not messing up queue logic
			this.$el.appendTo(this.$el.parent());
			
			// Close (give some time to prevent jumpiness)
			window.setTimeout(resolve, 800);
			
			return false;
		}
	});
	
	var _FlashListView = Backbone.Marionette.CollectionView.extend({
		id: 'flash-container',
		itemView: _FlashView,
		
		appendHtml: function(collectionView, itemView, index) {
			itemView.$el.appendTo(collectionView.$el);
			this.$el.children().hide();
			
			// Show only if it's first
			if(index === 0) {
				itemView.$el.show();
			}
		}
	});
	
	var _Flash = Backbone.Model.extend({
		idAttribute: "key"
	});
	
	var _FlashCollection = Backbone.Collection.extend({
		model: _Flash
	});
	
	var _flashCollection = new _FlashCollection();
	var _flashListView = new _FlashListView({ collection: _flashCollection });
	var _flashRegion = WanterApp.flashRegion;
	
	// Show in region
	_flashRegion.show(_flashListView);

	
	
	_.extend(this, {
		setFlash: function(key, value) {
			var flash = new _Flash({ key: key, value: value });
			_flashCollection.add(flash);
		},
		
		closeFlash: function(key) {
			var flash = _flashCollection.get(key);
			_flashCollection.remove(flash);
		}
	});
	
	
	/*
	WanterApp.Flash.setFlash("a", "looking at a");
	WanterApp.Flash.setFlash("b", "looking at b");
	WanterApp.Flash.setFlash("c", "looking at c");
	WanterApp.Flash.setFlash("d", "looking at d");
	
	window.setTimeout(function() {
		WanterApp.Flash.closeFlash("a");
	}, 1000);
	
	window.setTimeout(function() {
		WanterApp.Flash.closeFlash("b");
	}, 500);
	
	window.setTimeout(function() {
		WanterApp.Flash.closeFlash("c");
	}, 2500);*/
});

