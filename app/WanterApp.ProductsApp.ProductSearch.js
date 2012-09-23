// WanterApp.ProductsApp.ProductSearch

WanterApp.module("ProductsApp.ProductSearch", function(ProductSearch, WanterApp, Backbone, Marionette, $, _) {
	var ProductsApp = WanterApp.ProductsApp;
	
	var SearchView = Backbone.Marionette.ItemView.extend({
		el: '#search',
		
		ui: {
			term: '#term',
			loading: '.loader'
		},
		
		events: {
			'change term'		: 'search'
		},
		
		initialize: function() {
			_.bindAll(this);
			
			// Manage loading spinner
			ProductsApp.vent.on("goToPage:start", this.showLoading);
			ProductsApp.vent.on("goToPage:success", this.hideLoading);
		},
		
		showLoading: function() {
			this.ui.loading.fadeIn();
		},
		
		hideLoading: function() {
			this.ui.loading.delay(400).fadeOut();
		},
		
		search: function() {
			var term = this.ui.term.val().trim();
			
			ProductsApp.vent.trigger("search:term", term);
		}
	});
	
	// Attach searchView to ProductsApp layout
	ProductsApp.vent.on("layout:rendered", function() {
		var searchView = new SearchView()	
		WanterApp.ProductsApp.layout.search.attachView(searchView);
	});
}); 