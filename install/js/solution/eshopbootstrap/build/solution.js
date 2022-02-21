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

	var Element = /*#__PURE__*/function (_Page) {
	  babelHelpers.inherits(Element, _Page);

	  function Element() {
	    babelHelpers.classCallCheck(this, Element);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Element).apply(this, arguments));
	  }

	  babelHelpers.createClass(Element, [{
	    key: "bootCart",
	    value: function bootCart(cart) {
	      if (typeof BX === 'undefined' || typeof JCCatalogElement === 'undefined') {
	        return;
	      }

	      BX.addCustomEvent('onCatalogElementChangeOffer', function (eventData) {
	        var newProductId = parseInt(eventData === null || eventData === void 0 ? void 0 : eventData.newId, 10);

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

	var Order = /*#__PURE__*/function (_Page) {
	  babelHelpers.inherits(Order, _Page);

	  function Order() {
	    babelHelpers.classCallCheck(this, Order);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Order).apply(this, arguments));
	  }

	  babelHelpers.createClass(Order, [{
	    key: "bootFactory",
	    value: function bootFactory(factory) {
	      factory.extendDefaults({
	        preserve: {
	          mutation: {
	            anchor: '.bx-soa-cart-total',
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
	  basket: Basket,
	  order: Order
	});

	exports.factory = factory;

}((this.BX.YandexPay.Solution.EshopBootstrap = this.BX.YandexPay.Solution.EshopBootstrap || {})));
//# sourceMappingURL=solution.js.map
