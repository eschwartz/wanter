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
	
	var _cartListView = Backbone.Marionette.CollectionView.extend({
		itemView: _CartItemView,
		tagName:'ul'
	});
	
	// Render and display cart
	this.showCart = function(cartCollection) {
		var listView = new _cartListView({ collection: cartCollection });
		ProductsApp.layout.cart.show(listView);
	}
	
	ProductsApp.vent.on("layout:rendered", function() {
		ProductsApp.ShoppingCart.showCart(ProductsApp.cart);
	});
});
