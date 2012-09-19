<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<style type="text/css">

body {
	background: #f1f1f1;
}

p {
	font-family:ff-meta-web-pro, "Lucida Grande", sans-serif;
	font-size:13px;
	line-height:1.5em;
}

a {
  color: #000;
  margin-top: 16px;
  font-family: proxima-nova;
  letter-spacing: 0.2em;
  font-size: 12px;
  font-weight: bold;
  border-bottom: 2px solid #000;
  text-transform: uppercase;
	text-decoration: none;
}

.container {
	background:#fff;
	border:1px solid #ccc;
	width:960px;
	margin:0 auto;
}

.left {
	float:left;
}
.right {
	float:right;
}

.clear {
	clear:both;
}

.productList {
	margin:0;
}
	.productList .item {
		position:relative;
		float:left;
		margin:20px;
		padding:10px;
		cursor:pointer;
	}
	/* arrow on active item */
	.item.active:before {
	  content: '';
	  width: 0em;
	  height: 0em;
	  border-left: 14px solid transparent;
	  border-right: 14px solid transparent;
	  border-bottom: 14px solid #ccc;
	  position: absolute;
	  bottom: -16px;
	  left: 50%;
	  margin-left: -14px;
	  z-index:99;
	}
	.item.active:after {
		  content: '';
		  width: 0em;
		  height: 0em;
		  border-left: 14px solid transparent;
		  border-right: 14px solid transparent;
		  border-bottom: 14px solid #fff;
		  position: absolute;
		  bottom: -18px;
		  left: 50%;
		  margin-left: -14px;
		  z-index: 100;
	}
		.productList .item img {
			height:170px;
			width:170px;
		}

.item.productDetails {
	width:100%;
	box-sizing:border-box;
	padding:30px;
	margin: -5px 0 20px 0;
	border-top:1px solid #ccc;
	border-bottom:1px solid #ccc;
}
	.item.productDetails .image img {
		width:400px;
		height:auto;
	}
	.item.productDetails .content {
		width: 300px;
		margin-left: 100px;
		padding: 0 20px;
	}
	.item h2 {
		font-family: ff-meta-web-pro, "Lucida Grande", sans-serif;
		font-size:16px;
		line-height:1.33em;
	}
	.item h5 {
		font-family: ff-meta-web-pro, "Lucida Grande", sans-serif;
		color:#8d8d8d;
		font-size:14px;
		padding-bottom:20px;
		margin:0;
	}
	.item .btn {
		width:180px;
	}
	
	
	
	
.btn {
	padding:10px 0;

  display:inline-block;

/*  font-family: "Proxima Nova", "Helvetica", "Arial", sans-serif; */
  font-family: proxima-nova, "Helvetica", "Arial", sans-serif;
  font-size:13px;
  font-weight: bold;
  letter-spacing:0.25em;
  text-transform:uppercase;
  text-decoration:none;
  text-align:center;
  -webkit-font-smoothing: antialiased;
  text-rendering: optimizeLegibility;


  border:none;
  -webkit-appearance: none;

  box-sizing:border-box;
  -webkit-border-radius: 0.25em;
  -moz-border-radius: 0.25em;
  border-radius: 0.25em;

  border:0;

  background-color: #000000;
  color: #FFFFFF;

}

.btn:hover, .btn:visited {
  color: #fff;
}

.btn.default:active {
  color: #DDDDDD;
}

.btn:active {
  background-color: #333;
  color: #fff;
}

.btn.action {
  height:4.233em;
  line-height: 4.233em;

  box-shadow:0 0.333em 0 #000;
  filter:progid:DXImageTransform.Microsoft.dropshadow(OffX=0, OffY=3, Color='000', Positive='true');
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