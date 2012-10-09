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
		className	: 'productDetails',
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
		
		
		beforeClose: function(close) {
			this.$el.fadeOut(close);
			return false;
		},
		
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
			var self = this;
			
			// Trigger detail request to this module
			ProductList.vent.trigger('detail:request', this.model, this);
			
			// Reset active class
			$('.' + $.trim(this.className.replace(' ', '.'))).removeClass(this.activeClassName);
			this.$el.toggleClass(this.activeClassName);
			
			// Scroll to element
			/*window.setTimeout(function() {
				$.scrollTo(self.$el, {duration: 400, offset: {top: 60} });
			}, 800);*/
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
			var isSameView = this.details && this.details.currentView && (model == this.details.currentView.model);		
			
			// Should be a jquery plugin. oh well.
			var getAutoHeight  = function($el) {
				var $clone = $el.clone().css({"height":"auto","width":"auto"}).appendTo('body');
				var autoHeight = $clone.outerHeight();
				
				$clone.remove();
				return autoHeight;
			}
			
			if(isSameView) {
				this.closeDetail();
			}
			else if(isMyItemView) {	
				var detailView = new DetailView({ model: model });
				this.details.show(detailView);
				
				this.$el.animate({height: getAutoHeight(detailView.$el)}, 200);
			}

			// It's not mine, so I should close mine
			else if(this.details && this.details.currentView) {
				this.closeDetail();
			}
		},
		
		closeDetail: function() {
			var self = this;
			
			// Fix Height
			this.$el.height(this.$el.height());
			
			// remove active class (hack-ish)
			$('.active').removeClass('active');
			
			// Close details region view, then slideup layout
			this.details.close(function() {	
				self.$el.animate({height: 0 });
			});
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
	
	
	
	// Show Products when layout's ready
	WanterApp.ProductsApp.vent.on("layout:rendered", function() {
		WanterApp.ProductsApp.ProductList.showProducts(WanterApp.ProductsApp.products);
	});
	
	this.vent.on("productList:rendered", this.initializeInfiniteScroll);
});




