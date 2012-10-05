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
	var ProductsApp = WanterApp.ProductsApp;

	// current instance of the ProductListView
	ProductList.listView = null;			
	
	// Module-level event aggregator
	ProductList.vent = new Backbone.Marionette.EventAggregator();	
	
	var _baseProductView = Backbone.Marionette.ItemView.extend({
		
		templateHelpers: {
			imageSrc: function() {
				return (!this.images)? false: this.images[0].link;
			}
		},
		
		// Don't forget to call _baseProductView.prototype.initialize.call(this) in child objects!
		initiliaze: function() {
			_.bindAll(this);
			
			// Delegate cartBtn UI
			// In a way that makes extending from this class easier (so I don't have to call _baseView.prototype.... everywhere)
			this.ui = _.extend({}, {
				cartBtn: '.toggleCart'
			}, this.ui);
			this.delegateEvents({
				'click cartBtn':		'toggleItemInCart'
			});
			
			// Change cartBtn on add/remove from cart
			this.bindTo(this.model, "change:inCart", this.toggleCartBtnUI);
		},
		
		// Overwrite this method
		// to change cart button from "Add" to "Remove", or vice versa
		toggleCartBtnUI: function() {
			// eg: this.ui.cartBtn.toggleClass('add');
		},
		
		// Add or removed the item from the cart
		toggleItemInCart: function(e) {
			e.stopPropagation();
			
			cartAction = this.model.get('inCart')? "remove": "add";
			ProductsApp.vent.trigger("cart:" + cartAction, this.model);			
		}
	});
	
	var DetailView = _baseProductView.extend({
		template	: '#product-details-template',
		className	: 'productDetails item',
		elHeight	: null,
		
		ui: {
			container: '.details-container'
		},
		
		initialize: function() {
			// Run parent initialize method
			_baseProductView.prototype.initiliaze.apply(this, arguments);
			
			// Close the detail view when we reset the collection (eg. on search, change page)
			this.bindTo(this.model.collection, "reset", this.close);
			
			this.bindTo(this.model, "change:inCart", this.toggleCartBtnUI);
		},
		
		// Save height (for resizing height in onRender) then fade out
		/*beforeRender: function(render) {
			//Check that container is rendered
			if(this.ui.container instanceof $) {
				
				// Save the elements height, so we can do height-change animation in this.onRender
				this.elHeight = this.ui.container.height();
				
				// Fade out container, then render
				this.ui.container.fadeTo(300, 0, render); 
			}
			
			// Container not rendered --> height to zero (first display in row)
			else {
				this.elHeight = 0;
				render();
			}
				
			return false;
		},
		
		// Resize container to new height, then fade in content
		onRender: function() {
			// Because someone changed our model, we need to make sure everything is bound correctly
			// Making me think this is more hack-ish than I would like....
			// Actually, we're ruining all of Marionette's cleanup benefits by doing it like this. 
			// We really need to close the view, and think of another way to handle rows....
			this.initialize();
			
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
		
		// Slideup the container	
		beforeClose: function(close) {
			this.$el.slideUp(close);
			
			return false;
		},*/
		
		// Change cartBtn text between "Add"/"Remove"
		toggleCartBtnUI: function() {
			var text = this.model.get('inCart')? "Remove from Cart": "Add to Cart";
			this.ui.cartBtn.text(text);
		}
		
	});
	
	var ProductView = Backbone.Marionette.ItemView.extend({
		template: '#product-template',
		className: 'item thumb',
		activeClassName: 'active',
		
		templateHelpers: {
			// Returns the src of the products first image
			thumbSrc: function() {
				return this.images[0].link;
			}
		},
		
		ui: {
			toggleCart: '.toggleCart'
		},
		
		events: {
			'click'					: 'handleReqDetail',
			'mouseenter'			: 'showToggleCart',
			'mouseleave'			: 'hideToggleCart',
			'click toggleCart'		: 'toggleCart'
		},
		
		initialize: function() {
			_.bindAll(this);
			
			// Change toggle cart value, and flash
			this.bindTo(this.model, "change:inCart", this.toggleToggleCart);
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
			
			// Don't animate on re-render
			this.onRender = function() {};
		},
		
		// Add or remove from cart
		toggleCart: function(e) {
			// Prevent other item click events from running
			e.stopPropagation();
			
			cartAction = this.model.get('inCart')? "remove": "add";
			ProductsApp.vent.trigger("cart:" + cartAction, this.model);
			
			// Change toggle icon
			this.toggleToggleCart();
		},
		
		// Change the taggleCart class (yes, an awesome method name, I know)
		toggleToggleCart: function() {
			var toggleClass = this.model.get('inCart')? "cross": "check";
			var self = this;
			
			this.ui.toggleCart.removeClass('check cross').addClass(toggleClass);
			
			this.showToggleCart();
			window.setTimeout(this.hideToggleCart, 3000);
		},
		
		showToggleCart: function() {
			this.ui.toggleCart.stop(true, true).delay(100).fadeIn(100);
		},
		hideToggleCart: function() {
			this.ui.toggleCart.stop(true, true).fadeOut(100);
		}
	});
	
	var noResultsView = Backbone.Marionette.ItemView.extend({
		template: '#loading-template'
	});
	
	var _detailLayout = Backbone.Marionette.Layout.extend({
		template: '#detail-container-template',
		className: 'detail-container',
		
		regions: {
			'details': '.details'
		},
		
		initialize: function() {
			_.bindAll(this);
			
			// Close when collection is reset
			this.bindTo(this.collection, "reset", this.close);
			
			// Handle detail request
			ProductList.vent.on("detail:request", this.showDetail);
		},
		
		showDetail: function(model, itemView) {
			var isMyItemView = (this.options.myItemViews.indexOf(itemView)) >= 0;
			
			// Check that this layout is responsible for the requesting itemView
			// (maybe not the most efficient calculation to make every layout do this on req... but it works!)
			if(isMyItemView) {	
				var detailView = new DetailView({ model: model });
				this.details.show(detailView);
			}
			else {
				this.details.close();
			}
		}
	});
	
	var ProductListView = Backbone.Marionette.CompositeView.extend({
		template	: '#product-list-template',
		itemView	: ProductView,
		
		rowItemViews: [],						// Array of item views in the row (used in appendHtml);
		
		ui: {
			'clearFix'		: '#clearList',
			'message'		: '.messages',
		},
		
		events: {
			'scroll'		: 'infiniteScroller'
		},
		
		initialize: function() {
			var self = this;
			
			_.bindAll(this);
		},
		
		appendHtml: function(collectionView, itemView, index) {
			var isLastInRow = ((index + 1) % _perRow === 0);
			var detailContainer;
			
			// Add the view before our clearfix
			itemView.$el.insertBefore(collectionView.ui.clearFix);
			
			// Collect itemViews in row
			this.rowItemViews.push(itemView);
			
			// Add details container layout at end of row
			if (isLastInRow) {
				detailContainer = new _detailLayout({ collection: this.collection, myItemViews: this.rowItemViews });
				
				// Note that we insert the $el before rendering
				// so that my itemView.beforeClose has access to an el in the DOM
				itemView.$el.after(detailContainer.$el);
				detailContainer.render();
				
				this.rowItemViews = [];
			}
		},
		
		infiniteScroller: function() {
			var totalHeight = this.$el.$('> div').height();
			var scrollTop = this.$el.height() + this.$el.scrollTop();
			var margin = 200;
			
			// Load more within margin of content
			if(scrollTop + margin >= totalHeight) {
				ProductsApp.trigger("search:more");
			}
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
	
		
	
	// Render and display listview
	ProductList.showProducts = function(collection) {
		ProductList.listView = new ProductListView({ collection: collection });
		WanterApp.ProductsApp.layout.productList.show(ProductList.listView);
		ProductList.vent.trigger("productList:rendered");
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
			$('.item').removeClass(itemView.activeClassName);
			itemView.$el.addClass(itemView.activeClassName);
		}
		
		// Same view --> close the detailView
		if(isSameItem) {
			_activeDetailView.close();
			_activeDetailView = null;
			
			// Remove active class on itemView
			$('.item').removeClass(itemView.activeClassName);
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
	
	
	
	// Set up infinite scroll functionality
	ProductList.initializeInfiniteScroll = function() {
		ProductList.vent.on("scroll:bottom", ProductsApp.products.addNextPage);
		
		// Trigger window scroll event, for infitinte scrolling
		$(window).scroll(function() {
			var scrollPos = $(window).scrollTop();
			var bottomPos = $(document).height() - $(window).height();
			var buffer = 200;
			
			// Within buffer px of bottom
			if(scrollPos + buffer >= bottomPos) {
				ProductList.vent.trigger("scroll:bottom");
			}
		});
	}
	
	
	
	// Handle detail request
	//this.vent.on("detail:request", this.showDetail);

	// Show Products when layout's ready
	WanterApp.ProductsApp.vent.on("layout:rendered", function() {
		WanterApp.ProductsApp.ProductList.showProducts(WanterApp.ProductsApp.products);
	});
	
	this.vent.on("productList:rendered", this.initializeInfiniteScroll);
});




