// WanterApp.ProductsApp.Router.js

WanterApp.module("ProductsApp.Router", function(ProductsApp, WanterApp, Backbone, Marionette, $, _){
	var ProductsApp = WanterApp.ProductsApp;
	
	var _router;
	
	var _ProductRouter = Backbone.Marionette.AppRouter.extend({
		appRoutes: {
			""					: "defaultSearch",
			"search/:term"		: "navSearch"
		}
	});
	
	// Initialize Router
	ProductsApp.addInitializer(function() {
		_router = new _ProductRouter({
			controller: ProductsApp
		});
		
		ProductsApp.vent.trigger("routing:started");
	});
	
	
	// Show search in navigation
	ProductsApp.vent.on("search:term", function(term) {
		Backbone.history.navigate("search/" + term);
	});
	
});