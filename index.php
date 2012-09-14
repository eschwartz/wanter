<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
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
 * ProductView
 * a sinlge product ItemView
*/
Wanter.ProductView = Backbone.Marionette.ItemView.extend({
	tagName: 'li',
	template: '#product-template',
	
	events: {
		'click' : 'openDetails'
	},
	
	/**
	 * Opens a ProductDetailsView for this product
	 * and inserts after this view
	*/
	openDetails: function() {
		var detailsView = new Wanter.ProductDetailsView({
			model: this.model
		});
		
		detailsView.render().$el.insertAfter(this.$el);
	}
});

/** 
 * ProductDetailsView
 * more info about a product
*/
Wanter.ProductDetailsView = Backbone.Marionette.ItemView.extend({
	tagName: 'li',
	template: '#product-details-template',
	
	ui: {
		closeBtn: '.close'
	},
	
	events: {
		'click closeBtn' : 'close'
	},
});

/**
 * ProductsListView
 * List view of all products
*/
Wanter.ProductListView = Backbone.Marionette.CollectionView.extend({
	tagName: 'ul',
	itemView: Wanter.ProductView
});

Wanter.start();
</script>

</body>
</html>