<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Wanter - Modular</title>

<link href="wanter.css" type="text/css" rel="stylesheet" />

<script type="text/javascript" src="../lib/jquery.js"></script>
<script type="text/javascript" src="../lib/jquery.masonry.min.js"></script>
<script type="text/javascript" src="../lib/plugins/jquery.scrollTo.js"></script>

<script type="text/javascript" src="../lib/underscore.js"></script>
<script type="text/javascript" src="../lib/backbone.js"></script>
<script type="text/javascript" src="../lib/backbone.paginator.js"></script>
<script type="text/javascript" src="../lib/backbone.marionette.js"></script>
<script type="text/javascript" src="Backbone.Marionette.extended.js"></script>

</head>

<body>

<h2>Following the <a href="http://davidsulc.com/blog/2012/05/06/tutorial-a-full-backbone-marionette-application-part-1/" target="_blank">Marionette App Tutorial</a></h2>


<div id="products" class="container"></div>

<div id="flash-container">
</div>



<!-- TEMPLATES
 ================== -->
 

<script id="products-layout" type="text/html">
	<div id="cart"></div>
	<div id="search">
		<input type="text" class="inline-block" placeholder="Search the Google Shopping API" name="term" id="term" />
		<div class="inline-block"><div class="loader hide"></div></div>
	</div>
	<div id="product-list"></div>
</script>

<script id="product-list-template" type="text/html">
	<div class="productList">
		<h4 class="messages"></h4>
		<div id="clearList" class="clear"></div>
	</div>
</script>

<script id="product-template" type="text/html">
	<img src="<%=thumbSrc() %>" alt="<%=title %>" />
</script>

<script id="cart-item-template" type="text/html" />
	<img src="<%=thumbSrc() %>" alt="<%=title %>" style="width:50px; height:50px"/>
</script>

<script id="product-details-template" type="text/html">
	<div class="details-container">
		<div class="left image">
			<img src="<%=imageSrc() %>" alt="<%=title %>" />
		</div>
		<div class="left content">
			<h2><%=title %></h2>
			<h5><%=brand %></h5>
			<button class="btn addToCart">Add to Gift</button>
			<p><%=description %></p>
			<p><a href="<%=link %>" target="_blank">Learn more</a></p>
		</div>   
		<div class="clear"></div>
	</div>
</script>


<script id="flash-template" type="text/html">
	<%=value %>
</script>


<!-- END TEMPLATES
 ================== -->


<script type="text/javascript" src="app/WanterApp.js"></script>
<script type="text/javascript" src="app/WanterApp.Flash.js"></script>
<script type="text/javascript" src="app/WanterApp.ProductsApp.js"></script>
<script type="text/javascript" src="app/WanterApp.ProductsApp.ProductList.js"></script>
<script type="text/javascript" src="app/WanterApp.ProductsApp.ProductSearch.js"></script>
<script type="text/javascript" src="app/WanterApp.ProductsApp.ShoppingCart.js"></script>
<script type="text/javascript">
// bootstrap 
// Important: this needs to be the very last thing, or everything's messed up with order of operations
$(document).ready(function() {
	WanterApp.start();
});
</script>

<div id="flash-area"></div>
</body>
</html>