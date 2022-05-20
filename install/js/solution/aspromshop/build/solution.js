this.BX = this.BX || {};
this.BX.YandexPay = this.BX.YandexPay || {};
this.BX.YandexPay.Solution = this.BX.YandexPay.Solution || {};
(function (exports) {
	'use strict';

	var EventProxy = /*#__PURE__*/function () {
	  babelHelpers.createClass(EventProxy, null, [{
	    key: "make",
	    value: function make() {
	      var config = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      return new EventProxy(config);
	    }
	  }]);

	  function EventProxy() {
	    var config = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, EventProxy);
	    this.config = config;
	    this.callbackMap = {};
	  }

	  babelHelpers.createClass(EventProxy, [{
	    key: "on",
	    value: function on(name, callback) {
	      this.matchEvent('bx') && this.onBxEvent(name, callback);
	      this.matchEvent('jquery') && this.onJQueryEvent(name, callback);
	      this.matchEvent('plain') && this.onPlainEvent(name, callback);
	    }
	  }, {
	    key: "off",
	    value: function off(name, callback) {
	      this.matchEvent('bx') && this.offBxEvent(name, callback);
	      this.matchEvent('jquery') && this.offJQueryEvent(name, callback);
	      this.matchEvent('plain') && this.offPlainEvent(name, callback);
	    }
	  }, {
	    key: "fire",
	    value: function fire(name) {
	      var data = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      this.matchEvent('bx') && this.fireBxEvent(name, data);
	      this.matchEvent('jquery') && this.fireJQueryEvent(name, data);
	      this.matchEvent('plain') && this.firePlainEvent(name, data);
	    }
	  }, {
	    key: "matchEvent",
	    value: function matchEvent(type) {
	      return this.config[type] != null ? !!this.config[type] : !this.config['strict'];
	    }
	  }, {
	    key: "onBxEvent",
	    value: function onBxEvent(name, callback) {
	      if (typeof BX === 'undefined') {
	        return;
	      }

	      BX.addCustomEvent(name, callback);
	    }
	  }, {
	    key: "offBxEvent",
	    value: function offBxEvent(name, callback) {
	      if (typeof BX === 'undefined') {
	        return;
	      }

	      BX.removeCustomEvent(name, callback);
	    }
	  }, {
	    key: "fireBxEvent",
	    value: function fireBxEvent(name, data) {
	      if (typeof BX === 'undefined') {
	        return;
	      }

	      BX.onCustomEvent(name, [data]);
	    }
	  }, {
	    key: "onJQueryEvent",
	    value: function onJQueryEvent(name, callback) {
	      if (typeof jQuery === 'undefined') {
	        return;
	      }

	      var selfConfig = this.typeConfig('jquery');

	      if (this.canProxyCallback(selfConfig)) {
	        var originalCallback = callback;

	        callback = function callback(evt, data) {
	          var _evt$originalEvent;

	          var proxyData = data != null ? data : evt === null || evt === void 0 ? void 0 : (_evt$originalEvent = evt.originalEvent) === null || _evt$originalEvent === void 0 ? void 0 : _evt$originalEvent.detail;
	          originalCallback(proxyData);
	        };

	        this.storeCallbackVariation('jquery', originalCallback, callback);
	      }

	      jQuery(document).on(name, callback);
	    }
	  }, {
	    key: "offJQueryEvent",
	    value: function offJQueryEvent(name, callback) {
	      if (typeof jQuery === 'undefined') {
	        return;
	      }

	      var selfConfig = this.typeConfig('jquery');

	      if (this.canProxyCallback(selfConfig)) {
	        callback = this.getCallbackVariation('jquery', callback);

	        if (callback == null) {
	          return;
	        }
	      }

	      jQuery(document).off(name, callback);
	    }
	  }, {
	    key: "fireJQueryEvent",
	    value: function fireJQueryEvent(name, data) {
	      if (typeof jQuery === 'undefined') {
	        return;
	      }

	      if (this.hasPlainAndJQueryCollision()) {
	        return;
	      }

	      jQuery(document).triggerHandler(new CustomEvent(name, {
	        "detail": data
	      }));
	    }
	  }, {
	    key: "onPlainEvent",
	    value: function onPlainEvent(name, callback) {
	      if (this.hasPlainAndJQueryCollision()) {
	        return;
	      }

	      var selfConfig = this.typeConfig('plain');

	      if (this.canProxyCallback(selfConfig)) {
	        var originalCallback = callback;

	        callback = function callback(evt) {
	          originalCallback(evt.detail);
	        };

	        this.storeCallbackVariation('plain', originalCallback, callback);
	      }

	      document.addEventListener(name, callback);
	    }
	  }, {
	    key: "offPlainEvent",
	    value: function offPlainEvent(name, callback) {
	      if (this.hasPlainAndJQueryCollision()) {
	        return;
	      }

	      var selfConfig = this.typeConfig('plain');

	      if (this.canProxyCallback(selfConfig)) {
	        callback = this.getCallbackVariation('plain', callback);

	        if (callback == null) {
	          return;
	        }
	      }

	      document.removeEventListener(name, callback);
	    }
	  }, {
	    key: "firePlainEvent",
	    value: function firePlainEvent(name, data) {
	      document.dispatchEvent(new CustomEvent(name, {
	        "detail": data
	      }));
	    }
	  }, {
	    key: "hasPlainAndJQueryCollision",
	    value: function hasPlainAndJQueryCollision() {
	      if (!this.matchEvent('plain') || !this.matchEvent('jquery')) {
	        return false;
	      }

	      var plainConfig = this.typeConfig('plain');
	      return plainConfig['force'] !== true && typeof jQuery !== 'undefined';
	    }
	  }, {
	    key: "canProxyCallback",
	    value: function canProxyCallback(selfConfig) {
	      return selfConfig['proxy'] !== false;
	    }
	  }, {
	    key: "typeConfig",
	    value: function typeConfig(type) {
	      return babelHelpers["typeof"](this.config[type]) === 'object' && this.config[type] != null ? this.config : {};
	    }
	  }, {
	    key: "storeCallbackVariation",
	    value: function storeCallbackVariation(type, callback, proxy) {
	      if (this.callbackMap[type] == null) {
	        this.callbackMap[type] = new WeakMap();
	      }

	      this.callbackMap[type].set(callback, proxy);
	    }
	  }, {
	    key: "getCallbackVariation",
	    value: function getCallbackVariation(type, callback) {
	      var _this$callbackMap$typ;

	      return (_this$callbackMap$typ = this.callbackMap[type]) === null || _this$callbackMap$typ === void 0 ? void 0 : _this$callbackMap$typ.get(callback);
	    }
	  }]);
	  return EventProxy;
	}();

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
	  }, {
	    key: "onEvent",
	    value: function onEvent(name, callback) {
	      var config = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
	      EventProxy.make(config).on(name, callback);
	    }
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

	      this.onEvent('OnBasketChange', function () {
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

}((this.BX.YandexPay.Solution.AsproMshop = this.BX.YandexPay.Solution.AsproMshop || {})));
//# sourceMappingURL=solution.js.map
