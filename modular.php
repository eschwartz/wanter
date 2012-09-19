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

WanterApp.start();
</script>


<script type="text/javascript">
// WanterApp.ProductsApp.js
// Products application object
// Acts as controller for product listings

WanterApp.ProductsApp = function() {
	var ProductsApp = {};
	
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
	
	
	return ProductsApp;
}();


// Initialize ProductsApp layout when the parent app loads
WanterApp.addInitializer(function() {
	WanterApp.ProductsApp.initializeLayout();
});

</script>






</body>
</html>