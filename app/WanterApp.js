// WanterApp.js
// Umbrella application object

var WanterApp = new Backbone.Marionette.Application();

// Set App Regions
WanterApp.addRegions({
	flash: '#flash-container',
	products: '#products'
});