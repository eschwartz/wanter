<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<style type="text/css">
ul.products {
	list-style:none;
	margin:0;
}
	.products li {
		display:inline-block;
		margin:20px;
	}
		.products li img {
			max-width:180px;
		}

.productDetails {
	list-style:none;
	padding:15px;
	border:1px solid rgba(0,0,0,.7);
}
</style>

<title>Wanter - Backbone Clone</title>
<script type="text/javascript" src="../lib/jquery.js"></script>
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

<div id="products"></div>

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
		'q'				: 'business casual',
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
	tagName: 'li',
	className: 'productDetails',
	template: '#product-details-template',
	
	ui: {
		closeBtn: '.close'
	},
	
	events: {
		'click closeBtn' : 'close'
	},
	
	onRender: function() {
		console.log(this);
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
	tagName: 'li',
	template: '#product-template',
	detailView: Wanter.ProductDetailsView,
	
	templateHelpers: {
		thumbSrc: function() {
			return this.images[0].link
		}
	},
	
	initialize: function() {
		_.bindAll(this, 'showDetails', 'handleRequestDetails', 'closeDetails'); 
	},
	
	events: {
		'click' : 'handleRequestDetails'
	},
	
	// Broadcast the details request
	handleRequestDetails: function() {
		// I'll let the list view handle opening and closing details
		Wanter.vent.trigger('details:request', this, this.model);
	},
	
	/**
	 * Opens a ProductDetailsView for this product
	 * and inserts after this view
	*/
	showDetails: function() {
		var detailView = new this.detailView({
			model: this.model
		});
		
		detailView.render().$el.insertAfter(this.$el);
		
		// Tell the app which details view is rendered
		Wanter.vent.trigger('details:render', detailView, this.model);
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
	tagName: 'ul',
	className: 'products',
	itemView: Wanter.ProductView,
	openDetailsView: null,
	emptyView: Wanter.LoadingView,

	initialize: function() {
		// Bind view to products colletions
		this.collection.on("reset", this.render, this);
		
		// Handle opening and closing of detail views
		Wanter.vent.bind('details:request', this.openProductDetails, this);
		Wanter.vent.bind('details:render', this.setOpenDetailsView, this);
	},
	
	// Set the current details view
	setOpenDetailsView: function(detailView) {
		this.openDetailsView = detailView;
	},
	
	// Handle switching between detail views
	openProductDetails: function(itemView, model) {
		if(!this.openDetailsView) {
			itemView.showDetails();
			return;
		}
		
		// Close existing view, then open new details view.
		this.openDetailsView.close(itemView.showDetails)
	}
});

Wanter.start();
</script>

</body>
</html>