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
			'change term'		: 'search',
			'focus term'		: 'clearSearch'
		},
		
		initialize: function() {
			var self = this;
			
			_.bindAll(this);
			
			// Display seach term, if mannualy set
			ProductsApp.vent.on("search:term", function(term) {
				self.ui.term.val(term);
			});
		},
		clearSearch: function() {
			this.ui.term.val("");
		},
		
		search: function() {
			var term = this.ui.term.val().trim();
			
			ProductsApp.vent.trigger("search:term", term);
		}
	});
	
	// Attach searchView to ProductsApp layout
	// And enter default search
	ProductsApp.vent.on("layout:rendered", function() {
		var searchView = new SearchView()	
		ProductsApp.layout.search.attachView(searchView);
		
		ProductsApp.vent.trigger("searchView:rendered");
	});
}); 