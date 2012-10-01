/* WanterApp.ProductsApp.js
 * Products application object
 * Acts as controller for product listings
 *
 * Public methods: 
 *  initializeLayout: shows a products layout in the products region of the app
 *
 * Public properties:
 * 	products: a paginated collection of products
 * 	layout: the layout for products
*/

WanterApp.module("ProductsApp", function(ProductsApp, WanterApp, Backbone, Marionette, $, _){
	ProductsApp.vent = new Backbone.Marionette.EventAggregator();	
	
	/**
	 * ProductsApp Layout
	*/
	var Layout = Backbone.Marionette.Layout.extend({
		template: '#products-layout',
		regions: {
			search: '#search',
			productList: '#product-list',
			cart: '#cart'
		}
	});
	
	ProductsApp.initializeLayout = function() {
		
		// Instantiate Layout
		ProductsApp.layout = new Layout();
		
		// Trigger rendered event on show
		ProductsApp.layout.on("show", function() {
			ProductsApp.vent.trigger("layout:rendered");
		});
		
		// Show layout in WanterApp's products region
		WanterApp.products.show(ProductsApp.layout);
	}
	
	// Initialize ProductsApp layout when the parent app loads
	WanterApp.addInitializer(function() {
		ProductsApp.initializeLayout();
	});

	
	

	/**
	 * Event Handling
	*/
	
	// Run search on term change
	this.vent.on("search:term", function(term) {
		WanterApp.ProductsApp.products.search(term);
	});
	
	// Handle Search flash messages
	this.vent.on("search:start", function(term) {
		WanterApp.Flash.setFlash("product:search", "Searching for " + term + "...");
	});
	this.vent.on("search:complete", function() {
		WanterApp.Flash.closeFlash("product:search");
	});
	
	// Handle infinite scroll flash messages
	this.vent.on("addNextPage:start", function() {
		WanterApp.Flash.setFlash("product:addNextPage", "Loading more products...");
	});
	this.vent.on("addNextPage:complete", function() {
		WanterApp.Flash.closeFlash("product:addNextPage");
	});
	
	// Handle Shopping Cart events
	this.vent.on("cart:add", function(model) {
		ProductsApp.cart.add(model);
		
		WanterApp.Flash.timedFlash("cart:saving", "Saving...");						// Should actually run a sync here...
		WanterApp.Flash.timedFlash("cart:added", "Product added to cart");
	});
});

WanterApp.ProductsApp.addInitializer(function() {
	WanterApp.ProductsApp.vent.trigger("search:term", "men's blazer");
});



