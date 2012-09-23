// WanterApp.ProductsApp.ProductSearch

WanterApp.module("ProductsApp.ProductSearch", function(ProductSearch, WanterApp, Backbone, Marionette, $, _) {
	
	var SearchView = Backbone.Marionette.ItemView.extend({
		el: '#search',
		
		ui: {
			term: '#term'
		},
		
		events: {
			'change term'		: 'search'
		},
		
		initialize: function() {
			_.bindAll(this, 'search');
		},
		
		search: function() {
			var term = this.ui.term.val().trim();
			
			WanterApp.ProductsApp.vent.trigger("search:term", term);
		}
	});
	
	// Attach searchView to ProductsApp layout
	WanterApp.ProductsApp.vent.on("layout:rendered", function() {
		var searchView = new SearchView()	
		WanterApp.ProductsApp.layout.search.attachView(searchView);
	});
}); 