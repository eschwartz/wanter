<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<link href="wanter.css" type="text/css" rel="stylesheet" />

<title>Wanter - Backbone Clone</title>

<script type="text/javascript" src="../lib/jquery.js"></script>
<script type="text/javascript" src="../lib/jquery.masonry.min.js"></script>
<script type="text/javascript" src="../lib/underscore.js"></script>
<script type="text/javascript" src="../lib/backbone.js"></script>
<script type="text/javascript" src="../lib/backbone.paginator.js"></script>
<script type="text/javascript" src="../lib/backbone.marionette.js"></script>
<script type="text/javascript" src="Backbone.Marionette.extended.js"></script>
</head>

<body>

<div id="profile"></div>

<div id="products" class='container'>
</div>
<div class="clear"></div>

<div id="cart"></div>

<script id="loading-template" type="text/html">
	<div class="loading">Loading...</div>
</script>


<script id="product-template" type="text/html">
	<img src="<%=thumbSrc() %>" alt="<%=title %>" />
</script>

<script id="product-details-template" type="text/html">
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
</script>

<script type="text/javascript">


// TO DO NEXT: Try creating a relation to a images model, using Backbone relations extension
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
</script>

<script type="text/javascript">

var Wanter = new Backbone.Marionette.Application();

/**
 * Initialize Application
*/
Wanter.addInitializer(function(options) {
	var productCollection = new ProductCollection();
	var productList = new this.ProductListView({
		collection: productCollection
	});
	
	// Fetch first page of products collection
	productCollection.goTo(0);
	
	// Show the productList view in the products region
	this.products.show(productList);
	
});

/**
 * Application regions
*/
Wanter.addRegions({
	profile: '#profile',
	products: '#products',
	cart: '#cart'
});


/**
 * All purpose loading view
*/
Wanter.LoadingView = Backbone.Marionette.ItemView.extend({
	template: '#loading-template',
});


/** 
 * ProductDetailsView
 * more info about a product
*/
Wanter.ProductDetailsView = Backbone.Marionette.ItemView.extend({
	className: 'productDetails item',
	template: '#product-details-template',
	
	templateHelpers: {
		imageSrc: function() {
			return this.images[0].link;
		}
	},
	
	ui: {
		closeBtn: '.close'
	},
	
	events: {
		'click closeBtn' : 'close'
	},
	
	onRender: function() {
		var self = this;
		
		this.$el.hide();
		window.setTimeout(function() {				// This is crappy hack, because the CollectionView is inserting the $el after render is called.
			self.$el.slideDown("slow");
		}, 15);
	},
	beforeClose: function(close) {
		this.$el.slideUp(close);
		return false;
	}
});

/**
 * ProductView
 * a sinlge product ItemView
*/
Wanter.ProductView = Backbone.Marionette.ItemView.extend({
	template: '#product-template',
	className: 'item',
	
	templateHelpers: {
		thumbSrc: function() {
			return this.images[0].link;
		}
	},
	
	initialize: function() {
		_.bindAll(this,'handleRequestDetails', 'closeDetails'); 
	},
	
	events: {
		'click' : 'handleRequestDetails'
	},
	
	onRender: function() {
		var self = this;
		
		self.$el.fadeTo(0,0, function() {
			self.$el.imagesLoaded(function() {
				self.$el.delay(100).fadeTo(800, 1);
			});
		});
	},
	
	// Broadcast the details request
	handleRequestDetails: function() {
		// I'll let the list view handle opening and closing details
		Wanter.vent.trigger('details:request', this, this.model);
	},
	
	closeDetails: function() {
		this.openDetailView.close();
	},
});


/**
 * ProductsListView
 * List view of all products
*/
Wanter.ProductListView = Backbone.Marionette.CollectionView.extend({
	className: 'productList',
	itemView: Wanter.ProductView,
	openDetailView: null,
	detailView: Wanter.ProductDetailsView,
	emptyView: Wanter.LoadingView,
	perRow: 4,							// Items per row. Used to add detailView after last item in row

	initialize: function() {
		console.log(this.collection)
		
		// Handle opening and closing of detail views
		// NOTE: all this should be moved to a controller
		Wanter.vent.bind('details:request', this.openProductDetails, this);
	},
	
	onRender: function() {
		this.$el.append($('<div class="clear"></div>'));			// Kind of a crappy way to do this... but jquery overwrites my inline block
	},
	
	// Handle switching between detail views
	openProductDetails: function(itemView, itemModel) {
		// Find the last item in this row
		var row = Math.ceil(itemView.$el.index() / this.perRow),
			lastItemIndex = parseInt(row * this.perRow - 1),
			$lastItemInRow = this.$el.find('.item:eq('+lastItemIndex +')'),
			detailView = new this.detailView({ model: itemModel}),
			self = this;
		
		// Insert the detail view at the end of the row	
		var insertDetailView = function() {
			// Reset "active" class
			self.$el.find('.item').removeClass('active');
			itemView.$el.addClass('active');
			
			detailView.render().$el.insertAfter($lastItemInRow);
			self.openDetailView = detailView;
		}
		
		// If same view, exit
		if(this.openDetailView && this.openDetailView.model === detailView.model) {
			return false;
		}
		
		// If no detail view, show this one
		if(!this.openDetailView) {
			insertDetailView();
			return;
		}
		
		// Close existing view, then open new details view.
		this.openDetailView.close(insertDetailView);
	}
});

Wanter.start();
</script>

</body>
</html>