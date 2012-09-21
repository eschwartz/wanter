<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Wanter - Modular</title>

<link href="wanter.css" type="text/css" rel="stylesheet" />

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


<div id="products" class="container"></div>



<!-- TEMPLATES
 ================== -->
<script id="loading-template" type="text/html">
	<div class="loading">Loading...</div>
</script>

<script id="products-layout" type="text/html">
	<div id="search"></div>
	<div id="product-list"></div>
</script>

<script id="product-list-template" type="text/html">
	<div class="productList">
		<div id="clearList" class="clear"></div>
	</div>
</script>

<script id="product-template" type="text/html">
	<img src="<%=thumbSrc() %>" alt="<%=title %>" />
</script>

<script id="product-details-template" type="text/html">
	<div class="details-container">
		<div class="left image">
			<img src="<%=imageSrc() %>" alt="<%=title %>" />
		</div>
		<div class="left content">
			<h2><%=title %></h2>
			<h5><%=brand %></h5>
			<button class="btn">Add to Gift</button>
			<p><%=description %></p>
			<p><a href="#">Learn more</a></p>
		</div>   
		<div class="clear"></div>
	</div>
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
	
});

// Go to first page when the app loads
WanterApp.addInitializer(function() {
	WanterApp.ProductsApp.products.goTo(0);
});

// just testing
WanterApp.vent.on("goToPage:success", function(collection) {
	console.log("products: ", collection);
});
</script>


<script type="text/javascript">
/* WanterApp.ProductsApp.ProductList
 * Controller for product list views
 * 
 * Public properties:
 *
 *
 * Public methods:
 *		showProducts(collection) - renders a collection of products in the `productList` region
 *	   ~showDetail(model, itemView) - displays a detailView in the row under the specfied itemView. 
*/ 
//WanterApp.ProductsApp.ProductList = function() {
WanterApp.module("ProductsApp.ProductList", function(ProductList, WanterApp, Backbone, Marionette, $, _){
	var _activeDetailView = null;
	var _perRow = 4;					// Items per row, so we know where to put the detail view
	
	var DetailView = Backbone.Marionette.ItemView.extend({
		template	: '#product-details-template',
		className	: 'productDetails item',
		elHeight	: null,
		
		ui: {
			container: '.details-container'
		},
		
		templateHelpers: {
			imageSrc: function() {
				return (!this.images)? false: this.images[0].link;
			}
		},
		
		initialize: function() {
			// Refresh on model change
			this.bindTo(this.model, "change", this.render);
		},
		
		beforeRender: function(render) {
			// Save the elements height, so we can do height-change animations
			if(this.ui.container instanceof $) {
				this.elHeight = this.ui.container.height();
				// Fix height
				//this.ui.container.height(this.ui.container.height());
				this.ui.container.fadeTo(300, 0, render); 
				
				return false;
			}
			
			this.elHeight = 0;
		},
		
		onRender: function() {
			// Calculate new height
			var self = this,
				newHeight = this.ui.container.height();
						
			// Fix to old height
			this.ui.container.height(this.elHeight);
			
			// Hide container content
			this.ui.container.fadeTo(0,0);
			
			// Animate to new height
			this.ui.container.animate({height: newHeight}, function() {
				self.ui.container.fadeTo(300, 1);
			});
		},
			
		beforeClose: function(close) {
			this.$el.slideUp(close);
			
			return false;
		}
	});
	
	var ProductView = Backbone.Marionette.ItemView.extend({
		template: '#product-template',
		className: 'item',
		
		templateHelpers: {
			// Returns the src of the products first image
			thumbSrc: function() {
				return this.images[0].link;
			}
		},
		
		events: {
			'click'			: 'handleReqDetail'
		},
		
		initialize: function() {
			_.bindAll(this, 'handleReqDetail');
		},
		
		handleReqDetail: function() {
			ProductList.vent.trigger('detail:request', this.model, this);
		},
		
		// Fade in element
		onRender: function() {
			var self = this;
			
			// Hide, then fadeIn view
			self.$el.fadeTo(0,0, function() {
				self.$el.imagesLoaded(function() {
					self.$el.delay(100).fadeTo(800, 1);
				});
			});
		}
	});
	
	var ProductListView = Backbone.Marionette.CompositeView.extend({
		template	: '#product-list-template',
		itemView	: ProductView,
		
		ui: {
			'clearFix'		: '#clearList'
		},
		
		appendHtml: function(collectionView, itemView, index) {
			// Add the view before our clearfix
			itemView.$el.insertBefore(collectionView.ui.clearFix);
		}
	});
	
	// Return the last item in the itemView's row
	// NOTE: not handling if our row is half full at the end. Need a quick conditional to fix
	var _getLastRowItem = function(itemView) {
		var row = Math.ceil((itemView.$el.index() + 1) / _perRow),
			lastItemIndex = parseInt(row * _perRow - 1),
			$lastItemInRow = ProductList.listView.$el.find('.item:eq('+lastItemIndex +')');

		return $lastItemInRow;
	}
	
	
	// current instance of the ProductListView
	ProductList.listView = null;			
	
	// Module-level event aggregator
	ProductList.vent = new Backbone.Marionette.EventAggregator();	
	
	ProductList.showProducts = function(collection) {
		ProductList.listView = new ProductListView({ collection: collection });
		WanterApp.ProductsApp.layout.productList.show(ProductList.listView);
	};
	
	// Show a specified detail view
	ProductList.showDetail = function(model, itemView) {
		var isSameRow = (!_activeDetailView)? false: (_getLastRowItem(itemView)[0] === _activeDetailView.$el.prev('.item')[0]);
		
		var render = function() {
			_activeDetailView = new DetailView({model: model});
			_activeDetailView.$el.insertAfter(_getLastRowItem(itemView));
			_activeDetailView.render();
		}
		
		// No detailView open --> render a new one
		if(!_activeDetailView) {
			render();
		}
		
		// We're in the same row --> refresh the detailView
		else if(isSameRow) {
			_activeDetailView.model = model;
			_activeDetailView.render();
		}
		
		// Close the detail view, and rerender
		else {
			_activeDetailView.close(render);
		}
	};
	
	
	// Handle detail request
	ProductList.vent.on("detail:request", this.showDetail);
});

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