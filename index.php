<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<style type="text/css">
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


<script id="product-template" type="text/html">
	<div data-id="<%=id%>"><%= name %></div>
</script>

<script id="product-details-template" type="text/html">
	<div>
		<h2><%=name %>: <%=description %></h2>
		<button class="close">Close</button>
	</div>
</script>

<script type="text/javascript">
var productCollection = new Backbone.Collection([
	{ id:1, name: "first", description: "Very cool." }, 
	{ id:2, name: "second", description: "Not so cool to be second, is it?" }
]);
</script>

<script type="text/javascript">

var Wanter = new Backbone.Marionette.Application();

/**
 * Initialize Application
*/
Wanter.addInitializer(function(options) {
	var productList = new this.ProductListView({
		collection: productCollection
	});
	
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
	
	foo: "bar",
	
	initialize: function() {
		_.bindAll(this, 'showDetails', 'handleRequestDetails', 'closeDetails'); 
	},
	
	events: {
		'click' : 'handleRequestDetails'
		/// On detail click --> notify ListView that this was clicked
		// list view will close all other views, then tell this view to open details
	},
	
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
	itemView: Wanter.ProductView,
	openDetailsView: null,

	initialize: function() {
		//_.bind(this.openProductDetails, this);
		
		// Handle opening and closing of detail views
		Wanter.vent.bind('details:request', this.openProductDetails);
		Wanter.vent.bind('details:render', this.setOpenDetailsView);
	},
	
	setOpenDetailsView: function(detailView) {
		console.log('setting');
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