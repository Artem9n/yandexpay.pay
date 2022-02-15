this.BX = this.BX || {};
this.BX.YandexPay = this.BX.YandexPay || {};
this.BX.YandexPay.Solution = this.BX.YandexPay.Solution || {};
(function (exports) {
	'use strict';

	var Page = /*#__PURE__*/function () {
	  function Page(cart) {
	    babelHelpers.classCallCheck(this, Page);
	    babelHelpers.defineProperty(this, "cart", null);
	    this.cart = cart;
	    this.bootstrap();
	  }

	  babelHelpers.createClass(Page, [{
	    key: "bootstrap",
	    value: function bootstrap() {}
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
	    key: "bootstrap",
	    value: function bootstrap() {
	      var _this = this;

	      if (typeof BX === 'undefined' || typeof JCCatalogElement === 'undefined') {
	        return;
	      }

	      BX.addCustomEvent('onAsproSkuSetPrice', function (eventData) {
	        var _eventData$offer;

	        var newProductId = parseInt(eventData === null || eventData === void 0 ? void 0 : (_eventData$offer = eventData.offer) === null || _eventData$offer === void 0 ? void 0 : _eventData$offer.ID, 10);

	        if (isNaN(newProductId)) {
	          return;
	        }

	        _this.cart.delayChangeOffer(newProductId);
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
	    key: "bootstrap",
	    value: function bootstrap() {
	      var _this = this;

	      if (typeof BX === 'undefined') {
	        return;
	      }

	      BX.addCustomEvent('OnBasketChange', function () {
	        _this.cart.getProducts().then(function (result) {
	          _this.cart.combineOrderWithProducts(result);
	        });
	      });
	    }
	  }]);
	  return Basket;
	}(Page);

	var Factory = /*#__PURE__*/function () {
	  function Factory(classMap) {
	    babelHelpers.classCallCheck(this, Factory);
	    babelHelpers.defineProperty(this, "classMap", {});
	    this.classMap = classMap;
	  }

	  babelHelpers.createClass(Factory, [{
	    key: "create",
	    value: function create(cart) {
	      var type = cart.getOption('mode');
	      var className = this.classMap[type];

	      if (className == null) {
	        return null;
	      }

	      return new className(cart);
	    }
	  }]);
	  return Factory;
	}();

	var factory = new Factory({
	  element: Element,
	  basket: Basket
	});

	exports.factory = factory;

}((this.BX.YandexPay.Solution.AsproNext = this.BX.YandexPay.Solution.AsproNext || {})));
//# sourceMappingURL=build.js.map
