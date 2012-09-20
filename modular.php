<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Wanter - Modular</title>
<script type="text/javascript" src="../lib/jquery.js"></script>
<script type="text/javascript" src="../lib/jquery.masonry.min.js"></script>
<script type="text/javascript" src="../lib/underscore.js"></script>
<script type="text/javascript" src="../lib/backbone.js"></script>
<script type="text/javascript" src="../lib/backbone.paginator.js"></script>
<script type="text/javascript" src="../lib/backbone.marionette.js"></script>
<script type="text/javascript" src="Backbone.Marionette.extended.js"></script>

</head>

<body>

<h2>Following the <a href="http://davidsulc.com/blog/2012/05/06/tutorial-a-full-backbone-marionette-application-part-1/" target="_blank">Marionette App Tutorial</a></h2>


<div id="products"></div>



<!-- TEMPLATES
 ================== -->
<script id="products-layout" type="text/html">
	<div id="search"></div>
	<div id="product-list"></div>
</script>

<script id="product-template" type="text/html">
<%=title %>
</script>

<!-- END TEMPLATES
 ================== -->


<script type="text/javascript">
// WanterApp.js
// Umbrella application object

var WanterApp = new Backbone.Marionette.Application();

// Set App Regions
WanterApp.addRegions({
	products: '#products'
});



</script>


<script type="text/javascript">
// WanterApp.ProductsApp.js
// Products application object
// Acts as controller for product listings

WanterApp.ProductsApp = function() {
	var ProductsApp = {};
	
	/**
	 * ProductsApp Layout
	*/
	var Layout = Backbone.Marionette.Layout.extend({
		template: '#products-layout',
		regions: {
			search: '#search',
			products: '#product-list'
		}
	});
	
	ProductsApp.initializeLayout = function() {
		
		// Instantiate Layout
		ProductsApp.layout = new Layout();
		
		// Trigger rendered event on show
		ProductsApp.layout.on("show", function() {
			WanterApp.vent.trigger("layout:rendered");
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
			'q'				: "business casual blazer men's",
			'startIndex'	: function() { return (this.currentPage * this.perPage) + 1 },		// Google uses 1 based index
			'maxResults'	: function() { return this.perPage }
			
		},
		parse: function(response) {
			// Example JSON: 	
			// https://www.googleapis.com/shopping/search/v1/public/products?key=AIzaSyDx-MNjeT9vIXdcKBvVaXZEOeVPRju8fBE&country=US&language=en&currency=USD
			var tags = response.items;
			
			this.totalPages = Math.ceil(response.totalItems / this.perPage);
			
			return tags;
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
		
		WanterApp.vent.trigger("goToPage:start", page);
		
		// Call prototype's goTo method
		goTo_orig.call(this, page, {
			success: function(collection, response) {
				// Let everyone know we're all good
				self.loading = false;
				WanterApp.vent.trigger("goToPage:success", collection);
				callback(collection);
			},
			error: function(collection, response) {
				self.loading = false;
				WanterApp.vent.trigger("goToPage:error", response);
				console.log("fail.");
			}
		});
	}
	
	 
	
	
	return ProductsApp;
}();

// Go to first page when the app loads
WanterApp.addInitializer(function() {
	WanterApp.ProductsApp.products.goTo(0);
});

// just testing
WanterApp.vent.on("goToPage:success", function(collection) {
	console.log(collection);
});
</script>


<script type="text/javascript">
// WanterApp.ProductsApp.ProductList
// Controller for product list views
WanterApp.ProductsApp.ProductList = function() {
	var ProductList = {};
	
	var ProductView = Backbone.Marionette.ItemView.extend({
		template: '#product-template',
		
		/*
		 * view triggers: "reqDetails"
		 * App.vent.on("reqDetails", ProductList.showDetails);
		 * ProductList.showDetails(itemModel, itemView) --> this should handle the logic
		*/
	});
	
	var ProductListView = Backbone.Marionette.CollectionView.extend({
		itemView: ProductView
	});
	
	ProductList.showProducts = function(collection) {
		var listView = new ProductListView({ collection: collection });
		WanterApp.ProductsApp.layout.products.show(listView);
	};	
	
	return ProductList;
}();

// Show Products on init
WanterApp.vent.on("layout:rendered", function() {
	WanterApp.ProductsApp.ProductList.showProducts(WanterApp.ProductsApp.products);
});


</script>


<script type="text/javascript">
// bootstrap (in my html file)

// Important: this needs to be the very last thing, or everything's messed up with order of operations
WanterApp.start();
</script>


</body>
</html>