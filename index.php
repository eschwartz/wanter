<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<style type="text/css">

body {
	background: #f1f1f1;
}

.container {
	background:#fff;
	border:1px solid #ccc;
	width:960px;
	margin:0 auto;
}

.clear {
	clear:both;
}

.productList {
	margin:0;
}
	.productList .item {
		float:left;
		margin:20px;
		padding:10px;
		cursor:pointer;
	}
		.productList .item img {
			height:170px;
			width:170px;
		}

.productDetails {
	list-style:none;
	padding:15px;
	border:1px solid rgba(0,0,0,.7);
}
</style>

<title>Wanter - Backbone Clone</title>
<script type="text/javascript" src="../lib/jquery.js"></script>
<script type="text/javascript" src="../lib/jquery.masonry.min.js"></script>
<script type="text/javascript" src="../lib/underscore.js"></script>
<script type="text/javascript" src="../lib/backbone.js"></script>
<script type="text/javascript" src="../lib/backbone.paginator.js"></script>
<script type="text/javascript" src="../lib/backbone.marionette.js"></script>
<script type="text/javascript">
/**
 * Modified version of Backbone.Marionette.View.delegateEvents
 * Allows to delegate events to a named elements in this.selectors
 * eg. ui: { myButton: '#myButton' }, events: { 'click myButton': 'myCallback'}
*/

// NOTE: should submit a pull request for Marionette. This is pretty sweet, imho.



/* Helper Functions required for delegateEvents */
// Helper function to get a value from a Backbone object as a property
// or as a function.
var getValue = function(object, prop) {
if (!(object && object[prop])) return null;
return _.isFunction(object[prop]) ? object[prop]() : object[prop];
};

var delegateEventSplitter = /^(\S+)\s*(.*)$/;


Backbone.Marionette.View.prototype.delegateEvents = function(events) {
  if (!(events || (events = getValue(this, 'events')))) return;
  this.undelegateEvents();
  for (var key in events) {
	// Determine callback method
	var method = events[key];
	if (!_.isFunction(method)) method = this[events[key]];
	if (!method) throw new Error('Method "' + events[key] + '" does not exist');
	
	// Split up selector and event binding
	var match = key.match(delegateEventSplitter);
	var eventName = match[1];
	
	// Check for named selector
	var	selector = (this.ui && _.has(this.ui, match[2]))? this.ui[match[2]]: match[2];
	
	// Bind the event to the DOM object
	method = _.bind(method, this);
	eventName += '.delegateEvents' + this.cid;
	if (selector === '') {
	  this.$el.bind(eventName, method);
	} else {
	  this.$el.delegate(selector, eventName, method);
	}
  }
}

</script>

<script type="text/javascript">
/**
 * beforeClose, now with deferreds!
 * Allows you to wait to call close until beforeClose has completed.
 * Very useful if beforeClose is calling an animation or async.
 * beforeClose is passed a deferred.resolve method as a parameter.
 * calling the resolve method will allow the closing to continue
 * eg.
 
 beforeClose: function(close) {
	 this.$el.hide().fadeOut(close);
	 return false;				// Must return false to use the deferred
 }
*/

// NOTE: should submit a pull request for Marionette. This is pretty sweet, imho.

window.noop = function() {}

_.extend(Backbone.Marionette.View.prototype, {
	close: function(callback) {
		callback = (callback && _.isFunction(callback))? callback : window.noop;
		
		if (this.beforeClose) {
			
			// if beforeClose returns false, wait for beforeClose to resolve before closing
			// Before close calls `run` parameter to continue with closing element
			var dfd = $.Deferred(), close = dfd.resolve, self = this;
			if(this.beforeClose(close) === false) {
				dfd.done(function() {
					self._closeView();				// call _closeView, making sure our context is still `this`
					callback.call(self);
				});
				return true;
			}
		}
		
		// Run close immediately if beforeClose does not return false
		this._closeView();
	},
	
	_closeView: function() {
		this.remove();
	
		if (this.onClose) { this.onClose(); }
		this.trigger('close');
		this.unbindAll();
		this.unbind();		
	}
});
</script>
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
	<div>
		<h2>name: descr</h2>
		<button class="close">Close</button>
	</div>
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
	
	ui: {
		closeBtn: '.close'
	},
	
	events: {
		'click closeBtn' : 'close'
	},
	
	onRender: function() {
		this.$el.hide().fadeIn();
	},
	beforeClose: function(close) {
		this.$el.fadeOut(close);
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
			return this.images[0].link
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
		// Bind view to products colletions
		this.collection.on("reset", this.render, this);
		
		// Handle opening and closing of detail views
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
			
		var insertDetailView = function() {
			detailView.render().$el.insertAfter($lastItemInRow);
			self.openDetailView = detailView;
		}
		
		if(!this.openDetailView) {
			insertDetailView();
			return;
		}
		
		// Close existing view, then open new details view.
		this.openDetailView.close(insertDetailView());
	}
});

Wanter.start();
</script>

</body>
</html>