<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Wanter - Modular</title>
</head>

<!-- TEMPLATES
 ================== -->

<div id="products"></div>

<!-- END TEMPLATES
 ================== -->


<script type="text/javascript">
// WanterApp.js
// Umbrella application object

var WanterApp = new Backbone.Marionette.Application();

// Set App Regions
Wanter.addRegions({
	products: '#products'
});
</script>


<script type="text/javascript">
// WanterApp.ProductsApp.js
// Products application object
// Acts as controller for product listings

WanterApp.ProductsApp = function() {
	var ProductsApp = {};
	
	return ProductsApp;
}();

</script>


<body>

<h2>Following the <a href="http://davidsulc.com/blog/2012/05/06/tutorial-a-full-backbone-marionette-application-part-1/" target="_blank">Marionette App Tutorial</a></h2>




</body>
</html>