/** WanterApp.ProductsApp.products
 * Define Products collections and models for ProductsApp module
 * Provides `products` and `cart` collection instances
*/

WanterApp.module("ProductsApp", function(ProductsApp, WanterApp, Backbone, Marionette, $, _){
	var Product = Backbone.Model.extend({
		idAttribute: "googleId",
		parse: function(response) {
			return response.product;
		},
		defaults: {
			inCart: false
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
			perPage		: 50,
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
		
		initialize: function() {
			_.bindAll(this, "search", "addNextPage");
			Backbone.Paginator.requestPager.prototype.initialize.call(this);
		},
		
		/**
		 * Search by term
		*/
		search: function(term, callback) {
			var self = this;
			var options = {};								// ajax fetch options
			callback = callback || function() {};
			
			// Set search term
			this.server_api.q = term;
			
			// Prevent searches from stacking up
			if(this.loading) return true;
			this.loading = true;
			
			ProductsApp.vent.trigger("search:start", term);
			
			options.success = function(collection, response) {
				// Check for no results
				if(collection.length < 1) {
					ProductsApp.vent.trigger("search:noResults");
				}
				else {
					ProductsApp.vent.trigger("search:success", collection, response);
				}
			};
			
			options.error = function(collection, response) {
				ProductsApp.vent.trigger("search:error", collection, response);
				console.log('products search failed. aw man...');
			};
			
			options.complete = function(jqXHR, textResponse) {
				self.loading = false;
				ProductsApp.vent.trigger("search:complete", jqXHR, textResponse);
			};
			
			// Request first page of results, using Backbone.Paginator.goTo()
			this.goTo(0, options);
		},
		
		// Adds the next page's items to the collection
		addNextPage: function() {
			var options = {};
			var self = this;
			
			// Prevent stacking
			if(this.loading) return true;
			this.loading = true;
			
			ProductsApp.vent.trigger("addNextPage:start");
			
			// Adds collection, instead of replacing
			options.add = true;
			
			options.success = function(collection, response) {
				if(collection.length < 1) {
					ProductsApp.vent.trigger("addNextPage:noResults");
				}
				else {
					ProductsApp.vent.trigger("addNextPage:success");
				}
			}
			
			options.error = function(collection, response) {
				ProductsApp.vent.trigger("addNextPage:error", collection, response);
				console.log("products.addNextPage failed");
			}
			
			options.complete = function(jqXHR, textResponse) {
				self.loading = false;
				ProductsApp.vent.trigger("addNextPage:complete");
			}

			this.requestNextPage(options);
		}
	});
	

	// Instantiate Products
	this.products = new ProductCollection();	
	
	
	/**
	 * Shopping Cart Collections
	*/
	
	var CartCollection = Backbone.Collection.extend({
		model: Product
	});

	this.cart = new CartCollection();
});
