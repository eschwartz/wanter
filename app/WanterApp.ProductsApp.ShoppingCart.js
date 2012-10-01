/**
 * WanterApp.ProductsApp.ShoppingCart
*/
WanterApp.module("ProductsApp.ShoppingCart", function(ProductsApp, WanterApp, Backbone, Marionette, $, _){
	var ProductsApp = WanterApp.ProductsApp;
	
	var _CartItemView = Backbone.Marionette.ItemView.extend({
		template: '#cart-item-template',
		tagName: 'li',
		
		templateHelpers: {
			// Returns the src of the products first image
			thumbSrc: function() {
				return this.images[0].link;
			}
		}
	});
	
	var _CartEmptyItemView = Backbone.View.extend({
		tagName: 'li',
		className: 'empty',
		template: function() { return "" }				// So we don't get an error trying to render
	});
	
	var _CartEmptyView = Backbone.Marionette.ItemView.extend({
		template: '#cart-empty-template',
	});
	
	var _CartListView = Backbone.Marionette.CollectionView.extend({
		itemView: _CartItemView,
		emptyView: _CartEmptyView,
		tagName:'ul',
		className: 'clearfix',
		id: "cart_items",
		maxItems: 16,
		
		// Classes to apply to list when item is added or removed
		addedClass: "added",
		removedClass: "removed",
		
		initialize: function() {
			_.bindAll(this);
			
			// Handle add/show classes
			this.bindTo(this.collection, "add", this.showAddedClass);
		},
		
		showAddedClass: function() {
			var self = this;
			this.$el.removeClass(this.removedClass + " " + this.addedClass);
			window.setTimeout(function() {
				self.$el.addClass(self.addedClass);
			}, 15);
		},
		
		showRemovedClass: function() {
			var self = this;
			
			this.$el.removeClass(this.removedClass + " " + this.addedClass);
			window.setTimeOut(function() {
				self.$el.addClass(self.removedClass);
			}, 15);
		},
		
		buildEmptyCells: function() {
			var cellsToAdd = this.maxItems - this.collection.length;
			var emptyView = new _CartEmptyItemView();
			
			// Remove any empty cells, for a clean start
			this.$el.children('.empty').remove();
			
			// Fill up with empty cells
			for(var i=0; i <= cellsToAdd; ++i) {
				this.$el.append(emptyView.$el.clone());
			}
		
		},
		
		// Manage empty cells
		appendHtml: function(collectionView, itemView, index) {
			// Allow for emptyView
			if(this.collection.length === 0) {
				Backbone.Marionette.CollectionView.prototype.appendHtml.apply(this, arguments);
				return;
			}
			
			// build empty cells, if this is the first item
			if(this.collection.length === 1) {
				this.buildEmptyCells();
			}
			
			$itemToReplace = collectionView.$el.children('li').eq(index);
			$itemToReplace.replaceWith(itemView.$el);
		}
	});
	
	// Render and display cart
	this.showCart = function(cartCollection) {
		var cartListView = new _CartListView({ collection: cartCollection });
		ProductsApp.layout.cart.show(cartListView);
	}
	
	ProductsApp.vent.on("layout:rendered", function() {
		ProductsApp.ShoppingCart.showCart(ProductsApp.cart);
	});
});
