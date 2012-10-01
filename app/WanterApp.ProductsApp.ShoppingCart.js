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
	
	var _CartEmptyView = Backbone.Marionette.ItemView.extend({
		template: '#cart-empty-template',
	});
	
	var _cartListView = Backbone.Marionette.CollectionView.extend({
		itemView: _CartItemView,
		emptyView: _CartEmptyView,
		tagName:'ul',
		id: "cart_item",
		
		// Classes to apply to list when item is added or removed
		addedClass: "added",
		removedClass: "removed"
	});
	
	// Render and display cart
	this.showCart = function(cartCollection) {
		var cartListView = new _cartListView({ collection: cartCollection });
		ProductsApp.layout.cart.show(cartListView);
	}
	
	ProductsApp.vent.on("layout:rendered", function() {
		ProductsApp.ShoppingCart.showCart(ProductsApp.cart);
	});
});
