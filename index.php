<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Wanter - Backbone Clone</title>
<script type="text/javascript" src="../lib/jquery.js"></script>
<script type="text/javascript" src="../lib/underscore.js"></script>
<script type="text/javascript" src="../lib/backbone.js"></script>
<script type="text/javascript" src="../lib/backbone.marionette.js"></script>
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
		<h3><%=name%></h3>
		<p><%=description %></p>
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
	
	// Note: Should try repurposing that Backbone.Events extension I made for yii-beersdb to use named ui elements
	events: {
		'click .close' : 'close'
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