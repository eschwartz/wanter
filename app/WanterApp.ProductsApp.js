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
			productList: '#product-list'
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
	 * ProductsApp Products Collection
	*/
	var Product = Backbone.Model.extend({
		idAttribute: "googleId",
		parse: function(response) {
			return response.product;
		}
	});
	
	var ProductCollection = Backbone.Paginator.requestPager.extend({
		model: Product,
		
		paginator_core: {
			url: 'https://www.googleapis.com/shopping/search/v1/public/products?'	
		},
		paginator_ui: {
			firstPage	: 0,
			currentPage	: 0,
			perPage		: 20,
			totalPages	: 10			// a default, in case we fail to calculate the total pages
		},
		server_api: {
			'key'			: 'AIzaSyDx-MNjeT9vIXdcKBvVaXZEOeVPRju8fBE',
			'country'		: 'US',
			'q'				: "",
			'startIndex'	: function() { return (this.currentPage * this.perPage) + 1 },		// Google uses 1 based index
			'maxResults'	: function() { return this.perPage }
			
		},
		parse: function(response) {
			// Example JSON: 	
			// https://www.googleapis.com/shopping/search/v1/public/products?key=AIzaSyDx-MNjeT9vIXdcKBvVaXZEOeVPRju8fBE&country=US&language=en&currency=USD
			var tags = response.items;
			
			this.totalPages = Math.ceil(response.totalItems / this.perPage);
			
			return tags;
		},
		
		/**
		 * Search by term
		*/
		search: function(term) {
			this.server_api.q = term;
			
			this.goTo(0);
		}
	});
	
	// Instantiate Products
	ProductsApp.products = new ProductCollection();
	
	
	// Overwrite products.goTo(), to include event triggers
	var goTo_orig = ProductsApp.products.goTo;
	ProductsApp.products.goTo = function(page, callback) {
		var self = this;
		
		callback = callback || function() {};
		
		// Check if we're already running a request
		if(this.loading) return true;
		this.loading = true;
		
		ProductsApp.vent.trigger("goToPage:start", page);
		
		// Call prototype's goTo method
		goTo_orig.call(this, page, {
			success: function(collection, response) {
				// Let everyone know we're all good
				self.loading = false;
				if(collection.length < 1) {
					console.log('no res');
					ProductsApp.vent.trigger("goToPage:noResults");
				}
				else {
					ProductsApp.vent.trigger("goToPage:success", collection);
				}
				
				ProductsApp.vent.trigger("goToPage:complete", collection);
				callback(collection);
			},
			error: function(collection, response) {
				self.loading = false;
				ProductsApp.vent.trigger("goToPage:error", response);
				console.log("fail.");
			}
		});
	}
	
	
	// Handle search
	WanterApp.ProductsApp.vent.on("search:term", function(term) {
		WanterApp.ProductsApp.products.search(term);
	});
});



