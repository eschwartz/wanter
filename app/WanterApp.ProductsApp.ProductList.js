/* WanterApp.ProductsApp.ProductList
 * Controller for product list views
 * 
 * Public properties:
 *
 *
 * Public methods:
 *		showProducts(collection) - renders a collection of products in the `productList` region
 *	   ~showDetail(model, itemView) - displays a detailView in the row under the specfied itemView. 
*/ 
//WanterApp.ProductsApp.ProductList = function() {
WanterApp.module("ProductsApp.ProductList", function(ProductList, WanterApp, Backbone, Marionette, $, _){
	var _activeDetailView = null;
	var _perRow = 4;					// Items per row, so we know where to put the detail view
	
	var DetailView = Backbone.Marionette.ItemView.extend({
		template	: '#product-details-template',
		className	: 'productDetails item',
		elHeight	: null,
		
		ui: {
			container: '.details-container'
		},
		
		templateHelpers: {
			imageSrc: function() {
				return (!this.images)? false: this.images[0].link;
			}
		},
		
		initialize: function() {
			// Close the detail view when we reset the collection (eg. on search, change page)
			this.bindTo(this.model.collection, "reset", this.close);
		},
		
		beforeRender: function(render) {
			//Check that container is rendered
			if(this.ui.container instanceof $) {
				
				// Save the elements height, so we can do height-change animation in this.onRender
				this.elHeight = this.ui.container.height();
				
				// Fade out container, then render
				this.ui.container.fadeTo(300, 0, render); 
				
				return false;
			}
			
			this.elHeight = 0;
		},
		
		onRender: function() {
			// Calculate new height
			var self = this;
			var newHeight = this.ui.container.height();
						
			// Fix to old height
			this.ui.container.height(this.elHeight);
			
			// Hide container content
			this.ui.container.fadeTo(0,0);
			
			// Animate to new height
			this.ui.container.animate({height: newHeight}, function() {
				self.ui.container.fadeTo(300, 1);
			});
		},
			
		beforeClose: function(close) {
			this.$el.slideUp(close);
			
			return false;
		}
	});
	
	var ProductView = Backbone.Marionette.ItemView.extend({
		template: '#product-template',
		className: 'item',
		activeClassName: 'active',
		
		templateHelpers: {
			// Returns the src of the products first image
			thumbSrc: function() {
				return this.images[0].link;
			}
		},
		
		events: {
			'click'			: 'handleReqDetail'
		},
		
		initialize: function() {
			_.bindAll(this, 'handleReqDetail');
		},
		
		handleReqDetail: function() {
			ProductList.vent.trigger('detail:request', this.model, this);
		},
		
		// Fade in element
		onRender: function() {
			var self = this;
			
			// Hide, then fadeIn view
			self.$el.fadeTo(0,0, function() {
				self.$el.imagesLoaded(function() {
					self.$el.delay(100).fadeTo(800, 1);
				});
			});
		}
	});
	
	var ProductListView = Backbone.Marionette.CompositeView.extend({
		template	: '#product-list-template',
		itemView	: ProductView,
		
		ui: {
			'clearFix'		: '#clearList'
		},
		
		appendHtml: function(collectionView, itemView, index) {
			// Add the view before our clearfix
			itemView.$el.insertBefore(collectionView.ui.clearFix);
		}
	});
	
	// Return the last item in the itemView's row
	// NOTE: not handling if our row is half full at the end. Need a quick conditional to fix
	var _getLastRowItem = function(itemView) {
		var row = Math.ceil((itemView.$el.index() + 1) / _perRow),
			lastItemIndex = parseInt(row * _perRow - 1),
			$lastItemInRow = ProductList.listView.$el.find('.item:eq('+lastItemIndex +')');

		return $lastItemInRow;
	}
	
	
	// current instance of the ProductListView
	ProductList.listView = null;			
	
	// Module-level event aggregator
	ProductList.vent = new Backbone.Marionette.EventAggregator();	
	
	ProductList.showProducts = function(collection) {
		ProductList.listView = new ProductListView({ collection: collection });
		WanterApp.ProductsApp.layout.productList.show(ProductList.listView);
	};
	
	// Handle displaying detail views in the product lsit
	ProductList.showDetail = function(model, itemView) {
		var isSameRow = (!_activeDetailView)? false: (_getLastRowItem(itemView)[0] === _activeDetailView.$el.prev('.item')[0]),
			isSameItem = (_activeDetailView && _activeDetailView.model === model);
		
		// Renders and inserts a detail view at the end of the row
		var render = function() {
			_activeDetailView = new DetailView({model: model});
			_activeDetailView.$el.insertAfter(_getLastRowItem(itemView));
			_activeDetailView.render();
			
			// Scroll to the row
			$.scrollTo(_activeDetailView.$el, {duration: 400, offset: {top: -80} });
		}
		
		// Give the active class to the appropriate itemView
		var setActive = function() {
			// Set active item
			$('.' + itemView.className).removeClass(itemView.activeClassName);
			itemView.$el.addClass(itemView.activeClassName);
		}
		
		// Same view --> close the detailView
		if(isSameItem) {
			_activeDetailView.close();
			_activeDetailView = null;
			
			// Remove active class on itemView
			$('.' + itemView.className).removeClass(itemView.activeClassName);
		}
		
		// No detailView open --> render a new one
		else if(!_activeDetailView) {
			render();
			setActive();
		}
		
		// We're in the same row --> refresh the detailView
		else if(isSameRow) {
			_activeDetailView.model = model;
			_activeDetailView.render();
			setActive();
		}
		
		// Close the detail view, then render a new detail view in a new row
		else {
			_activeDetailView.close(render);
			setActive();
		}
	};
	
	
	// Handle detail request
	ProductList.vent.on("detail:request", this.showDetail);
});

// Show Products on init
WanterApp.vent.on("layout:rendered", function() {
	WanterApp.ProductsApp.ProductList.showProducts(WanterApp.ProductsApp.products);
});
