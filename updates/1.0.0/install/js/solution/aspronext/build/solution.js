this.BX = this.BX || {};
this.BX.YandexPay = this.BX.YandexPay || {};
this.BX.YandexPay.Solution = this.BX.YandexPay.Solution || {};
(function (exports) {
	'use strict';

	var Page = /*#__PURE__*/function () {
	  function Page() {
	    babelHelpers.classCallCheck(this, Page);
	  }

	  babelHelpers.createClass(Page, [{
	    key: "bootFactory",
	    value: function bootFactory(factory) {}
	  }, {
	    key: "bootWidget",
	    value: function bootWidget(widget) {}
	  }, {
	    key: "bootCart",
	    value: function bootCart(cart) {}
	  }]);
	  return Page;
	}();

	var factoryLayout = (function (factory, options) {
	  var template = {
	    template: '<div class="bx-yapay-divider-container width--#WIDTH#">' + '<div class="bx-yapay-divider width--#WIDTH#">' + '<span class="bx-yapay-divider__corner"></span>' + '<span class="bx-yapay-divider__text">#LABEL#</span>' + '<span class="bx-yapay-divider__corner at--right"></span>' + '</div>' + factory.getOption('template') + '</div>'
	  };
	  factory.extendDefaults(Object.assign({}, options, template));
	});

	var Element = /*#__PURE__*/function (_Page) {
	  babelHelpers.inherits(Element, _Page);

	  function Element() {
	    babelHelpers.classCallCheck(this, Element);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Element).apply(this, arguments));
	  }

	  babelHelpers.createClass(Element, [{
	    key: "bootFactory",
	    value: function bootFactory(factory) {
	      factoryLayout(factory);
	    }
	  }, {
	    key: "bootCart",
	    value: function bootCart(cart) {
	      if (typeof BX === 'undefined' || typeof JCCatalogElement === 'undefined') {
	        return;
	      }

	      BX.addCustomEvent('onAsproSkuSetPrice', function (eventData) {
	        var _eventData$offer;

	        var newProductId = parseInt(eventData === null || eventData === void 0 ? void 0 : (_eventData$offer = eventData.offer) === null || _eventData$offer === void 0 ? void 0 : _eventData$offer.ID, 10);

	        if (isNaN(newProductId)) {
	          return;
	        }

	        cart.delayChangeOffer(newProductId);
	      });
	    }
	  }]);
	  return Element;
	}(Page);

	var Basket = /*#__PURE__*/function (_Page) {
	  babelHelpers.inherits(Basket, _Page);

	  function Basket() {
	    babelHelpers.classCallCheck(this, Basket);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Basket).apply(this, arguments));
	  }

	  babelHelpers.createClass(Basket, [{
	    key: "bootFactory",
	    value: function bootFactory(factory) {
	      factory.extendDefaults({
	        preserve: {
	          mutation: {
	            anchor: '[data-entity="basket-total-block"]',
	            delay: null
	          }
	        }
	      });
	    }
	  }, {
	    key: "bootCart",
	    value: function bootCart(cart) {
	      if (typeof BX === 'undefined') {
	        return;
	      }

	      BX.addCustomEvent('OnBasketChange', function () {
	        cart.getProducts().then(function (result) {
	          cart.combineOrderWithProducts(result);
	        });
	      });
	    }
	  }]);
	  return Basket;
	}(Page);

	var Basket$1 = /*#__PURE__*/function (_EshopBasket) {
	  babelHelpers.inherits(Basket$$1, _EshopBasket);

	  function Basket$$1() {
	    babelHelpers.classCallCheck(this, Basket$$1);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Basket$$1).apply(this, arguments));
	  }

	  babelHelpers.createClass(Basket$$1, [{
	    key: "bootFactory",
	    value: function bootFactory(factory) {
	      factoryLayout(factory, {
	        preserve: {
	          mutation: {
	            anchor: '[data-entity="basket-total-block"]',
	            delay: null
	          }
	        }
	      });
	    }
	  }]);
	  return Basket$$1;
	}(Basket);

	var Order = /*#__PURE__*/function (_Page) {
	  babelHelpers.inherits(Order, _Page);

	  function Order() {
	    babelHelpers.classCallCheck(this, Order);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Order).apply(this, arguments));
	  }

	  babelHelpers.createClass(Order, [{
	    key: "bootFactory",
	    value: function bootFactory(factory) {
	      factoryLayout(factory, {
	        preserve: {
	          mutation: {
	            anchor: '#bx-soa-total, #bx-soa-total-mobile',
	            delay: null
	          }
	        }
	      });
	    }
	  }]);
	  return Order;
	}(Page);

	var Factory = /*#__PURE__*/function () {
	  function Factory(classMap) {
	    babelHelpers.classCallCheck(this, Factory);
	    babelHelpers.defineProperty(this, "classMap", {});
	    this.classMap = classMap;
	  }

	  babelHelpers.createClass(Factory, [{
	    key: "create",
	    value: function create(mode) {
	      var className = this.classMap[mode];

	      if (className == null) {
	        return null;
	      }

	      return new className();
	    }
	  }]);
	  return Factory;
	}();

	var factory = new Factory({
	  element: Element,
	  basket: Basket$1,
	  order: Order
	});

	exports.factory = factory;

}((this.BX.YandexPay.Solution.AsproNext = this.BX.YandexPay.Solution.AsproNext || {})));
//# sourceMappingURL=solution.js.map
