this.BX = this.BX || {};
(function (exports) {
	'use strict';

	var SolutionRegistry = /*#__PURE__*/function () {
	  function SolutionRegistry() {
	    babelHelpers.classCallCheck(this, SolutionRegistry);
	  }

	  babelHelpers.createClass(SolutionRegistry, null, [{
	    key: "getFactory",
	    value: function getFactory(name) {
	      var _window, _window$BX, _window$BX$YandexPay, _window$BX$YandexPay$, _window$BX$YandexPay$2;

	      if (name == null) {
	        return null;
	      }

	      var factory = (_window = window) === null || _window === void 0 ? void 0 : (_window$BX = _window.BX) === null || _window$BX === void 0 ? void 0 : (_window$BX$YandexPay = _window$BX.YandexPay) === null || _window$BX$YandexPay === void 0 ? void 0 : (_window$BX$YandexPay$ = _window$BX$YandexPay.Solution) === null || _window$BX$YandexPay$ === void 0 ? void 0 : (_window$BX$YandexPay$2 = _window$BX$YandexPay$[name]) === null || _window$BX$YandexPay$2 === void 0 ? void 0 : _window$BX$YandexPay$2.factory;

	      if (factory == null) {
	        var _console;

	        (_console = console) === null || _console === void 0 ? void 0 : _console.warn("cant find solution ".concat(name));
	        return;
	      }

	      return factory;
	    }
	  }, {
	    key: "getPage",
	    value: function getPage(name, mode) {
	      if (name == null || mode == null) {
	        return null;
	      }

	      var key = name + ':' + mode;

	      if (this.pages[key] == null) {
	        this.pages[key] = this.createPage(name, mode);
	      }

	      return this.pages[key];
	    }
	  }, {
	    key: "createPage",
	    value: function createPage(name, mode) {
	      var factory = this.getFactory(name);

	      if (factory == null) {
	        return null;
	      }

	      return factory.create(mode);
	    }
	  }]);
	  return SolutionRegistry;
	}();

	babelHelpers.defineProperty(SolutionRegistry, "pages", {});

	var MutationSkeleton = /*#__PURE__*/function () {
	  function MutationSkeleton(element) {
	    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, MutationSkeleton);
	    this.el = element;
	    this.options = Object.assign({}, this.constructor.defaults, options);
	  }

	  babelHelpers.createClass(MutationSkeleton, [{
	    key: "destroy",
	    value: function destroy() {}
	  }]);
	  return MutationSkeleton;
	}();

	babelHelpers.defineProperty(MutationSkeleton, "defaults", {
	  check: null
	});

	var MutationLoop = /*#__PURE__*/function (_MutationSkeleton) {
	  babelHelpers.inherits(MutationLoop, _MutationSkeleton);

	  function MutationLoop(element) {
	    var _this;

	    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, MutationLoop);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(MutationLoop).call(this, element, options));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "loop", function () {
	      _this.options.check() && _this.loopTimeout();
	    });

	    _this.loopTimeout();

	    return _this;
	  }

	  babelHelpers.createClass(MutationLoop, [{
	    key: "destroy",
	    value: function destroy() {
	      this.loopCancel();
	    }
	  }, {
	    key: "loopTimeout",
	    value: function loopTimeout() {
	      clearTimeout(this._loopTimeout);
	      this._loopTimeout = setTimeout(this.loop, this.options.timeout);
	    }
	  }, {
	    key: "loopCancel",
	    value: function loopCancel() {
	      clearTimeout(this._loopTimeout);
	    }
	  }]);
	  return MutationLoop;
	}(MutationSkeleton);

	babelHelpers.defineProperty(MutationLoop, "defaults", Object.assign({}, MutationSkeleton.defaults, {
	  timeout: 1000
	}));

	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it.return != null) it.return(); } finally { if (didErr) throw err; } } }; }

	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

	var MutationObserver = /*#__PURE__*/function (_MutationSkeleton) {
	  babelHelpers.inherits(MutationObserver, _MutationSkeleton);

	  function MutationObserver(element) {
	    var _this;

	    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, MutationObserver);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(MutationObserver).call(this, element, options));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "listener", function (mutations) {
	      var _iterator = _createForOfIteratorHelper(mutations),
	          _step;

	      try {
	        for (_iterator.s(); !(_step = _iterator.n()).done;) {
	          var mutation = _step.value;

	          if (mutation.removedNodes == null) {
	            continue;
	          }

	          var _iterator2 = _createForOfIteratorHelper(mutation.removedNodes),
	              _step2;

	          try {
	            for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
	              var removedNode = _step2.value;

	              if (!(removedNode instanceof HTMLElement)) {
	                continue;
	              }

	              if (removedNode === _this.el || removedNode.contains(_this.el)) {
	                _this.runCheck();

	                return;
	              }
	            }
	          } catch (err) {
	            _iterator2.e(err);
	          } finally {
	            _iterator2.f();
	          }
	        }
	      } catch (err) {
	        _iterator.e(err);
	      } finally {
	        _iterator.f();
	      }
	    });

	    _this.observe();

	    return _this;
	  }

	  babelHelpers.createClass(MutationObserver, [{
	    key: "destroy",
	    value: function destroy() {
	      this.disconnect();
	    }
	  }, {
	    key: "observe",
	    value: function observe() {
	      var anchor = this.getAnchor();

	      if (anchor == null) {
	        var _console;

	        (_console = console) === null || _console === void 0 ? void 0 : _console.warn('cant find anchor for node preserver');
	        return;
	      }

	      this.observer = new window.MutationObserver(this.listener);
	      this.observer.observe(anchor, {
	        childList: true,
	        subtree: true
	      });
	    }
	  }, {
	    key: "disconnect",
	    value: function disconnect() {
	      if (this.observer == null) {
	        return;
	      }

	      this.observer.disconnect();
	      this.observer = null;
	    }
	  }, {
	    key: "runCheck",
	    value: function runCheck() {
	      var _this2 = this;

	      var delay = this.options.delay;

	      if (delay == null) {
	        this.options.check();
	      } else {
	        clearTimeout(this._checkTimeout);
	        this._checkTimeout = setTimeout(function () {
	          _this2.options.check();
	        }, delay);
	      }
	    }
	  }, {
	    key: "getAnchor",
	    value: function getAnchor() {
	      if (this.options.anchor == null) {
	        return document.body;
	      }

	      return this.el.closest(this.options.anchor);
	    }
	  }]);
	  return MutationObserver;
	}(MutationSkeleton);

	babelHelpers.defineProperty(MutationObserver, "defaults", Object.assign({}, MutationSkeleton.defaults, {
	  anchor: null,
	  delay: 0
	}));

	var MutationFactory = /*#__PURE__*/function () {
	  function MutationFactory() {
	    babelHelpers.classCallCheck(this, MutationFactory);
	  }

	  babelHelpers.createClass(MutationFactory, null, [{
	    key: "make",
	    value: function make(element, options) {
	      if (typeof window.MutationObserver === 'function') {
	        return new MutationObserver(element, options);
	      }

	      return new MutationLoop(element, options);
	    }
	  }]);
	  return MutationFactory;
	}();

	var Subscriber = /*#__PURE__*/function () {
	  function Subscriber(element) {
	    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, Subscriber);
	    this.el = element;
	    this.options = Object.assign({}, this.constructor.defaults, options);
	    this.bind();
	  }

	  babelHelpers.createClass(Subscriber, [{
	    key: "destroy",
	    value: function destroy() {
	      this.unbind();
	      this.options = {};
	      this.el = null;
	    }
	  }, {
	    key: "bind",
	    value: function bind() {
	      if (this.options.on == null) {
	        var _console;

	        (_console = console) === null || _console === void 0 ? void 0 : _console.warn('define "on" option for subscriber of node preserver');
	        return;
	      }

	      this.options.on(this.options.check);
	    }
	  }, {
	    key: "unbind",
	    value: function unbind() {
	      if (this.options.off == null) {
	        var _console2;

	        (_console2 = console) === null || _console2 === void 0 ? void 0 : _console2.warn('define "off" option for subscriber of node preserver');
	        return;
	      }

	      this.options.off(this.options.check);
	    }
	  }]);
	  return Subscriber;
	}();

	babelHelpers.defineProperty(Subscriber, "defaults", {
	  check: null,
	  on: null,
	  off: null
	});

	var NodePreserver = /*#__PURE__*/function () {
	  function NodePreserver(element) {
	    var _this = this;

	    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, NodePreserver);
	    babelHelpers.defineProperty(this, "check", function () {
	      var found = document.body.contains(_this.el);

	      if (!found) {
	        _this.options.restore();
	      }

	      return found;
	    });
	    this.el = element;
	    this.options = Object.assign({}, this.constructor.defaults, options);
	    this.install();
	  }

	  babelHelpers.createClass(NodePreserver, [{
	    key: "destroy",
	    value: function destroy() {
	      this.uninstall();
	      this.options = {};
	      this.el = null;
	    }
	  }, {
	    key: "install",
	    value: function install() {
	      this.installMutation();
	      this.installSubscriber();
	    }
	  }, {
	    key: "uninstall",
	    value: function uninstall() {
	      this.uninstallMutation();
	      this.uninstallSubscriber();
	    }
	  }, {
	    key: "installMutation",
	    value: function installMutation() {
	      if (!this.isEnabled('mutation')) {
	        return;
	      }

	      this.mutation = MutationFactory.make(this.el, this.driverOptions('mutation'));
	    }
	  }, {
	    key: "uninstallMutation",
	    value: function uninstallMutation() {
	      if (this.mutation == null) {
	        return;
	      }

	      this.mutation.destroy();
	    }
	  }, {
	    key: "installSubscriber",
	    value: function installSubscriber() {
	      if (!this.isEnabled('subscriber')) {
	        return;
	      }

	      this.subscriber = new Subscriber(this.el, this.driverOptions('subscriber'));
	    }
	  }, {
	    key: "uninstallSubscriber",
	    value: function uninstallSubscriber() {
	      if (this.subscriber == null) {
	        return;
	      }

	      this.subscriber.destroy();
	      this.subscriber = null;
	    }
	  }, {
	    key: "isEnabled",
	    value: function isEnabled(type) {
	      return !!this.options[type];
	    }
	  }, {
	    key: "driverOptions",
	    value: function driverOptions(type) {
	      var option = babelHelpers.typeof(this.options[type]) === 'object' ? this.options[type] : {};
	      var overrides = {
	        check: this.check
	      };
	      return Object.assign({}, option, overrides);
	    }
	  }]);
	  return NodePreserver;
	}();

	babelHelpers.defineProperty(NodePreserver, "defaults", {
	  restore: null,
	  subscriber: null,
	  mutation: true
	});

	var Template = /*#__PURE__*/function () {
	  function Template() {
	    babelHelpers.classCallCheck(this, Template);
	  }

	  babelHelpers.createClass(Template, null, [{
	    key: "compile",

	    /**
	     * @param {string=} template
	     * @param {Object} vars
	     * @returns {string}
	     */
	    value: function compile(template, vars) {
	      var key;
	      var replaceKey;
	      var replaceValue;
	      var result = template;

	      for (key in vars) {
	        if (!vars.hasOwnProperty(key)) {
	          continue;
	        }

	        replaceKey = '#' + key.toUpperCase() + '#';
	        replaceValue = vars[key];

	        do {
	          result = result.replace(replaceKey, replaceValue);
	        } while (result.indexOf(replaceKey) !== -1);
	      }

	      return result;
	    }
	  }, {
	    key: "toElement",
	    value: function toElement(html) {
	      var context = document.createElement('div');
	      context.innerHTML = html;
	      return context.firstElementChild;
	    }
	  }, {
	    key: "toElements",
	    value: function toElements(html) {
	      var context = document.createElement('div');
	      context.innerHTML = html;
	      return babelHelpers.toConsumableArray(context.children);
	    }
	  }]);
	  return Template;
	}();

	function _createForOfIteratorHelper$1(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray$1(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it.return != null) it.return(); } finally { if (didErr) throw err; } } }; }

	function _unsupportedIterableToArray$1(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray$1(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray$1(o, minLen); }

	function _arrayLikeToArray$1(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

	var Factory = /*#__PURE__*/function () {
	  function Factory() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, Factory);
	    babelHelpers.defineProperty(this, "waitCount", 0);
	    this.defaults = Object.assign({}, this.constructor.defaults);
	    this.options = {};
	    this.setOptions(options);
	    this.bootSolution();
	  }

	  babelHelpers.createClass(Factory, [{
	    key: "inject",
	    value: function inject(selector, position) {
	      var _this = this;

	      return Promise.resolve().then(function () {
	        return _this.waitElement(selector);
	      }).then(function (anchor) {
	        return _this.checkElement(anchor);
	      }).then(function (anchor) {
	        var element = _this.renderElement(anchor, position);

	        var widget = _this.install(element);

	        if (_this.getOption('preserve')) {
	          _this.preserve(selector, position, widget);
	        }

	        return widget;
	      });
	    }
	  }, {
	    key: "checkElement",
	    value: function checkElement(anchor) {
	      var selector = this.getOption('containerSelector');
	      var contains = !!anchor.querySelector(selector) || this.containsSiblingElement(anchor, selector);

	      if (contains) {
	        throw new Error('the element already has a container');
	      }

	      return anchor;
	    }
	  }, {
	    key: "containsSiblingElement",
	    value: function containsSiblingElement(anchor, selector) {
	      var _anchor$parentElement;

	      var result = false;
	      var next = (_anchor$parentElement = anchor.parentElement) === null || _anchor$parentElement === void 0 ? void 0 : _anchor$parentElement.firstElementChild;

	      while (next) {
	        if (next.matches(selector) || next.querySelector(selector)) {
	          result = true;
	          break;
	        }

	        next = next.nextElementSibling;
	      }

	      return result;
	    }
	  }, {
	    key: "preserve",
	    value: function preserve(selector, position, widget) {
	      var _this2 = this;

	      var preserver = new NodePreserver(widget.el, Object.assign({}, this.preserveOptions(), {
	        restore: function restore() {
	          preserver.destroy(); // noinspection JSIgnoredPromiseFromCall

	          _this2.restore(selector, position, widget);
	        }
	      }));
	    }
	  }, {
	    key: "preserveOptions",
	    value: function preserveOptions() {
	      var preserveOption = this.getOption('preserve');
	      return babelHelpers.typeof(preserveOption) === 'object' ? preserveOption : {};
	    }
	  }, {
	    key: "restore",
	    value: function restore(selector, position, widget) {
	      var _this3 = this;

	      return Promise.resolve().then(function () {
	        return _this3.waitElement(selector);
	      }).then(function (anchor) {
	        var element = _this3.renderElement(anchor, position);

	        widget.restore(element);

	        if (_this3.getOption('preserve')) {
	          _this3.preserve(selector, position, widget);
	        }

	        return widget;
	      });
	    }
	  }, {
	    key: "install",
	    value: function install(element) {
	      return new BX.YandexPay.Widget(element);
	    }
	  }, {
	    key: "waitElement",
	    value: function waitElement(selector) {
	      var _this4 = this;

	      return new Promise(function (resolve, reject) {
	        _this4.waitCount = 0;

	        _this4.waitElementLoop(selector, resolve, reject);
	      });
	    }
	  }, {
	    key: "waitElementLoop",
	    value: function waitElementLoop(selector, resolve, reject) {
	      var anchor = this.findElement(selector);

	      if (anchor) {
	        resolve(anchor);
	        return;
	      }

	      ++this.waitCount;

	      if (this.waitCount >= this.getOption('waitLimit')) {
	        reject('cant find element by selector ' + selector);
	        return;
	      }

	      setTimeout(this.waitElementLoop.bind(this, selector, resolve, reject), this.getOption('waitTimeout'));
	    }
	  }, {
	    key: "findElement",
	    value: function findElement(selector) {
	      var _ref, _this$searchBySelecto;

	      var elementList;
	      var variant = selector.trim();
	      var result;

	      if (variant === '') {
	        throw new Error('widget selector is empty');
	      }

	      elementList = (_ref = (_this$searchBySelecto = this.searchBySelector(variant)) !== null && _this$searchBySelecto !== void 0 ? _this$searchBySelecto : this.searchById(selector)) !== null && _ref !== void 0 ? _ref : this.searchByClassName(selector);

	      if (elementList == null) {
	        return null;
	      }

	      if (elementList.length > 1) {
	        result = this.reduceVisible(elementList);
	      }

	      if (result == null) {
	        result = elementList[0];
	      }

	      return result;
	    }
	  }, {
	    key: "searchBySelector",
	    value: function searchBySelector(selector) {
	      try {
	        var result = [];

	        var _iterator = _createForOfIteratorHelper$1(selector.split(',')),
	            _step;

	        try {
	          for (_iterator.s(); !(_step = _iterator.n()).done;) {
	            var part = _step.value;
	            // first selector
	            var partSanitized = part.trim();

	            if (partSanitized === '' || !this.isCssSelector(partSanitized)) {
	              continue;
	            }

	            var collection = document.querySelectorAll(partSanitized);

	            var _iterator2 = _createForOfIteratorHelper$1(collection),
	                _step2;

	            try {
	              for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
	                var element = _step2.value;
	                result.push(element);
	              }
	            } catch (err) {
	              _iterator2.e(err);
	            } finally {
	              _iterator2.f();
	            }
	          }
	        } catch (err) {
	          _iterator.e(err);
	        } finally {
	          _iterator.f();
	        }

	        return result.length > 0 ? result : null;
	      } catch (e) {
	        return null;
	      }
	    }
	  }, {
	    key: "searchById",
	    value: function searchById(selector) {
	      try {
	        var element = document.getElementById(selector);
	        return element != null ? [element] : null;
	      } catch (e) {
	        return null;
	      }
	    }
	  }, {
	    key: "searchByClassName",
	    value: function searchByClassName(selector) {
	      try {
	        var collection = document.getElementsByClassName(selector);
	        return collection.length > 0 ? collection : null;
	      } catch (e) {
	        return null;
	      }
	    }
	  }, {
	    key: "reduceVisible",
	    value: function reduceVisible(collection) {
	      var result = null;

	      var _iterator3 = _createForOfIteratorHelper$1(collection),
	          _step3;

	      try {
	        for (_iterator3.s(); !(_step3 = _iterator3.n()).done;) {
	          var element = _step3.value;

	          if (this.testVisible(element)) {
	            result = element;
	            break;
	          }
	        }
	      } catch (err) {
	        _iterator3.e(err);
	      } finally {
	        _iterator3.f();
	      }

	      return result;
	    }
	  }, {
	    key: "testVisible",
	    value: function testVisible(element) {
	      return element.offsetWidth || element.offsetHeight || element.getClientRects().length;
	    }
	  }, {
	    key: "isCssSelector",
	    value: function isCssSelector(selector) {
	      return /^[.#]/.test(selector);
	    }
	  }, {
	    key: "renderElement",
	    value: function renderElement(anchor, position) {
	      var selector = this.getOption('containerSelector');
	      var width = this.getOption('buttonWidth') || YaPay.ButtonWidth.Auto;
	      var html = Template.compile(this.getOption('template'), {
	        label: this.getOption('label'),
	        width: width.toLowerCase()
	      });
	      var elements = Template.toElements(html);
	      var result = null;

	      if (position.indexOf('after') === 0) {
	        elements = elements.reverse();
	      }

	      var _iterator4 = _createForOfIteratorHelper$1(elements),
	          _step4;

	      try {
	        for (_iterator4.s(); !(_step4 = _iterator4.n()).done;) {
	          var element = _step4.value;
	          anchor.insertAdjacentElement(position, element);

	          if (result != null) {
	            continue;
	          }

	          result = element.matches(selector) ? element : element.querySelector(selector);
	        }
	      } catch (err) {
	        _iterator4.e(err);
	      } finally {
	        _iterator4.f();
	      }

	      if (result == null) {
	        throw new Error("cant find template container by selector ".concat(selector));
	      }

	      return result;
	    }
	  }, {
	    key: "bootSolution",
	    value: function bootSolution() {
	      var name = this.getOption('solution');
	      var mode = this.getOption('mode');
	      var solution = SolutionRegistry.getPage(name, mode);

	      if (solution == null) {
	        return;
	      }

	      solution.bootFactory(this);
	    }
	  }, {
	    key: "extendDefaults",
	    value: function extendDefaults(options) {
	      this.defaults = Object.assign(this.defaults, options);
	    }
	  }, {
	    key: "setOptions",
	    value: function setOptions(options) {
	      this.options = Object.assign(this.options, options);
	    }
	  }, {
	    key: "getOption",
	    value: function getOption(name) {
	      var _this$options$name;

	      return (_this$options$name = this.options[name]) !== null && _this$options$name !== void 0 ? _this$options$name : this.defaults[name];
	    }
	  }]);
	  return Factory;
	}();

	babelHelpers.defineProperty(Factory, "defaults", {
	  solution: null,
	  template: '<div id="yandexpay" class="bx-yapay-drawer"></div>',
	  containerSelector: '.bx-yapay-drawer',
	  preserve: false,
	  waitLimit: 30,
	  waitTimeout: 1000
	});

	var AbstractStep = /*#__PURE__*/function () {
	  /**
	   * @param {Widget} widget
	   * @param {Object} options
	   */
	  function AbstractStep(widget) {
	    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, AbstractStep);
	    babelHelpers.defineProperty(this, "delayTimeouts", {});
	    this.widget = widget;
	    this.defaults = Object.assign({}, this.constructor.defaults);
	    this.options = Object.assign({}, options);
	  }
	  /**
	   *
	   * @param {string} name
	   * @returns {*}
	   */


	  babelHelpers.createClass(AbstractStep, [{
	    key: "getOption",
	    value: function getOption(name) {
	      var _ref, _this$options$name;

	      return (_ref = (_this$options$name = this.options[name]) !== null && _this$options$name !== void 0 ? _this$options$name : this.widget.getOption(name)) !== null && _ref !== void 0 ? _ref : this.defaults[name];
	    }
	    /**
	     * @param {Object<Element>} node Element
	     * @param {Object} data Options
	     */

	  }, {
	    key: "render",
	    value: function render(node) {
	      var data = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      node.innerHTML = this.compile(data);
	    }
	    /**
	     * @param {Object} data
	     * @returns {string}
	     */

	  }, {
	    key: "compile",
	    value: function compile(data) {
	      return Template.compile(this.getOption('template'), data);
	    }
	    /**
	     * @param {Object<Element>} node
	     */

	  }, {
	    key: "restore",
	    value: function restore(node) {// nothing by default
	    }
	    /**
	     * @param {string} url
	     * @param {Object} data
	     * @returns {Promise.<Object>}
	     */

	  }, {
	    key: "query",
	    value: function query(url, data) {
	      return fetch(url, {
	        method: 'POST',
	        headers: {
	          'Content-Type': 'application/json'
	        },
	        body: JSON.stringify(data)
	      }).then(function (response) {
	        return response.json();
	      });
	    }
	  }, {
	    key: "getTemplate",
	    value: function getTemplate(key) {
	      var optionKey = key + 'Template';
	      var option = this.options[optionKey];
	      var optionFirstSymbol = option.substr(0, 1);
	      var result;

	      if (optionFirstSymbol === '.' || optionFirstSymbol === '#') {
	        result = this.getNode(option).innerHTML;
	      } else {
	        result = option;
	      }

	      return result;
	    }
	  }, {
	    key: "getElement",
	    value: function getElement(key, context, method) {
	      var selector = this.getElementSelector(key);
	      return this.getNode(selector, context, method || 'querySelector');
	    }
	  }, {
	    key: "getElementSelector",
	    value: function getElementSelector(key) {
	      var optionKey = key + 'Element';
	      return this.options[optionKey];
	    }
	  }, {
	    key: "getNode",
	    value: function getNode(selector, context, method) {
	      if (selector.substr(0, 1) === '#') {
	        // is id query
	        context = document;
	      } else if (!context) {
	        context = this.el;
	      }

	      return context[method](selector);
	    }
	  }, {
	    key: "clearDelay",
	    value: function clearDelay(name) {
	      if (this.delayTimeouts[name] == null) {
	        return;
	      }

	      clearTimeout(this.delayTimeouts[name]);
	      this.delayTimeouts[name] = null;
	    }
	  }, {
	    key: "delay",
	    value: function delay(name) {
	      var _this$name;

	      var args = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : [];
	      var timeout = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 300;
	      this.clearDelay(name);
	      this.delayTimeouts[name] = setTimeout((_this$name = this[name]).bind.apply(_this$name, [this].concat(babelHelpers.toConsumableArray(args))), timeout);
	    }
	  }]);
	  return AbstractStep;
	}();

	babelHelpers.defineProperty(AbstractStep, "defaults", {
	  template: ''
	});

	var Base = /*#__PURE__*/function () {
	  function Base() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, Base);
	    this.options = Object.assign({}, this.constructor.defaults, options);
	    this.widget = null;
	  }

	  babelHelpers.createClass(Base, [{
	    key: "render",
	    value: function render(node) {
	      var data = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      node.innerHTML = this.compile(data);
	    }
	  }, {
	    key: "compile",
	    value: function compile(data) {
	      return Template.compile(this.options.template, data);
	    }
	  }, {
	    key: "setWidget",
	    value: function setWidget(widget) {
	      this.widget = widget;
	    }
	  }, {
	    key: "getOption",
	    value: function getOption(key) {
	      if (key in this.options) {
	        return this.options[key];
	      } else {
	        return this.widget.options[key];
	      }
	    }
	  }]);
	  return Base;
	}();

	babelHelpers.defineProperty(Base, "defaults", {
	  template: null
	});

	var Form = /*#__PURE__*/function (_Base) {
	  babelHelpers.inherits(Form, _Base);

	  function Form() {
	    babelHelpers.classCallCheck(this, Form);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Form).apply(this, arguments));
	  }

	  babelHelpers.createClass(Form, [{
	    key: "render",
	    value: function render(node, data) {
	      babelHelpers.get(babelHelpers.getPrototypeOf(Form.prototype), "render", this).call(this, node, data);
	      this.autosubmit(node);
	    }
	  }, {
	    key: "compile",
	    value: function compile(data) {
	      var template = this.options.template;
	      var vars = Object.assign(data, {
	        'inputs': this.makeInputs(data)
	      });
	      return Template.compile(template, vars);
	    }
	  }, {
	    key: "makeInputs",
	    value: function makeInputs(data) {
	      var key;
	      var vars = data.params;
	      var value;
	      var template;

	      if (Object.keys(vars).length === 0) {
	        return '';
	      }

	      template = data.termUrl ? '<input type="hidden" name="TermUrl" value="' + vars.termUrl + '">' : '';

	      for (key in vars) {
	        if (!vars.hasOwnProperty(key)) {
	          continue;
	        }

	        value = vars[key];
	        template += '<input type="hidden" name="' + key + '" value="' + value + '">';
	      }

	      return template;
	    }
	  }, {
	    key: "makeTermUrl",
	    value: function makeTermUrl() {
	      var result = this.getOption('notifyUrl');
	      var backUrl = window.location.href;
	      result += (result.indexOf('?') === -1 ? '?' : '&') + 'backurl=' + encodeURIComponent(backUrl) + '&service=' + this.getOption('requestSign') + '&paymentId=' + this.getOption('externalId');
	      return result;
	    }
	  }, {
	    key: "autosubmit",
	    value: function autosubmit(node) {
	      var form = node.querySelector('form');
	      form.submit();
	    }
	  }]);
	  return Form;
	}(Base);

	babelHelpers.defineProperty(Form, "defaults", {
	  template: '<form name="form" action="#ACTION#" method="#METHOD#">' + '#INPUTS#' + '</form>'
	});

	var Iframe = /*#__PURE__*/function (_Base) {
	  babelHelpers.inherits(Iframe, _Base);

	  function Iframe() {
	    babelHelpers.classCallCheck(this, Iframe);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Iframe).apply(this, arguments));
	  }

	  babelHelpers.createClass(Iframe, [{
	    key: "render",
	    value: function render(node, data) {
	      this.insertIframe(node, data);
	    }
	  }, {
	    key: "insertIframe",
	    value: function insertIframe(node, data) {
	      node.innerHTML = Template.compile(this.options.template, data);
	      this.compile(node, data);
	    }
	  }, {
	    key: "compile",
	    value: function compile(node, data) {
	      var iframe = node.querySelector('iframe');
	      var contentIframe = iframe.contentWindow || iframe.contentDocument.document || iframe.contentDocument;
	      var html = data.params;
	      contentIframe.document.open();
	      contentIframe.document.write(html);
	      contentIframe.document.close();
	    }
	  }]);
	  return Iframe;
	}(Base);

	babelHelpers.defineProperty(Iframe, "defaults", {
	  template: '<iframe style="display: none;"></iframe>'
	});

	var IframeRbs = /*#__PURE__*/function (_Base) {
	  babelHelpers.inherits(IframeRbs, _Base);

	  function IframeRbs() {
	    babelHelpers.classCallCheck(this, IframeRbs);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(IframeRbs).apply(this, arguments));
	  }

	  babelHelpers.createClass(IframeRbs, [{
	    key: "render",
	    value: function render(node, data) {
	      this.insertIframe(node, data);
	    }
	  }, {
	    key: "insertIframe",
	    value: function insertIframe(node, data) {
	      node.innerHTML = Template.compile(this.options.template, data);
	      this.compile(node, data);
	    }
	  }, {
	    key: "compile",
	    value: function compile(node, data) {
	      var _this = this;

	      Promise.resolve().then(function () {
	        return _this.renderIframe(node, data);
	      }).then(function () {
	        return _this.query(data);
	      });
	    }
	  }, {
	    key: "query",
	    value: function query(data) {
	      var _this2 = this;

	      fetch(this.getOption('notifyUrl'), {
	        method: 'POST',
	        headers: {
	          'Content-Type': 'application/json'
	        },
	        body: JSON.stringify({
	          service: this.getOption('requestSign'),
	          accept: 'json',
	          secure: data.params.secure,
	          externalId: data.params.notify.externalId,
	          paySystemId: data.params.notify.paySystemId
	        })
	      }).then(function (response) {
	        return response.json();
	      }).then(function (result) {
	        if (result.success === true) {
	          _this2.widget.go(result.state, result);
	        } else {
	          _this2.widget.go('error', result);
	        }
	      }).catch(function (error) {
	        return console.log(error);
	      });
	    }
	  }, {
	    key: "renderIframe",
	    value: function renderIframe(node, data) {
	      var iframe = node.querySelector('iframe');
	      var contentIframe = iframe.contentWindow || iframe.contentDocument.document || iframe.contentDocument;
	      var html = "<form name=\"form\" action=\"".concat(data.action, "\" method=\"POST\">");
	      contentIframe.document.open();
	      contentIframe.document.write(html);
	      contentIframe.document.close();
	      contentIframe.document.querySelector('form').submit();
	    }
	  }]);
	  return IframeRbs;
	}(Base);

	babelHelpers.defineProperty(IframeRbs, "defaults", {
	  template: '<iframe style="display: none;"></iframe>'
	});

	var Step3ds = /*#__PURE__*/function (_AbstractStep) {
	  babelHelpers.inherits(Step3ds, _AbstractStep);

	  function Step3ds() {
	    babelHelpers.classCallCheck(this, Step3ds);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Step3ds).apply(this, arguments));
	  }

	  babelHelpers.createClass(Step3ds, [{
	    key: "render",
	    value: function render(node, data) {
	      var view = this.makeView(data);
	      view.setWidget(this.widget);
	      view.render(node, data);
	    }
	  }, {
	    key: "makeView",
	    value: function makeView(data) {
	      var view = data.view;

	      if (view === 'form') {
	        return new Form();
	      } else if (view === 'iframe') {
	        return new Iframe();
	      } else if (view === 'iframerbs') {
	        return new IframeRbs();
	      }

	      throw new Error('view secure3d missing');
	    }
	  }]);
	  return Step3ds;
	}(AbstractStep);

	var Finish = /*#__PURE__*/function (_AbstractStep) {
	  babelHelpers.inherits(Finish, _AbstractStep);

	  function Finish() {
	    babelHelpers.classCallCheck(this, Finish);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Finish).apply(this, arguments));
	  }

	  return Finish;
	}(AbstractStep);

	babelHelpers.defineProperty(Finish, "defaults", {
	  template: '<div class="alert alert-success" role="alert"><strong>#MESSAGE#</strong></div>'
	});

	var Failure = /*#__PURE__*/function (_AbstractStep) {
	  babelHelpers.inherits(Failure, _AbstractStep);

	  function Failure() {
	    babelHelpers.classCallCheck(this, Failure);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Failure).apply(this, arguments));
	  }

	  return Failure;
	}(AbstractStep);

	babelHelpers.defineProperty(Failure, "defaults", {
	  template: '<div class="alert alert-danger" role="alert"><strong>#MESSAGE#</strong></div>'
	});

	var YaPay$1 = window.YaPay;

	var Payment = /*#__PURE__*/function (_AbstractStep) {
	  babelHelpers.inherits(Payment, _AbstractStep);

	  function Payment() {
	    babelHelpers.classCallCheck(this, Payment);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Payment).apply(this, arguments));
	  }

	  babelHelpers.createClass(Payment, [{
	    key: "render",
	    value: function render(node, data) {
	      var paymentData = this.getPaymentData(data);
	      this.createPayment(node, paymentData);
	    }
	  }, {
	    key: "compile",
	    value: function compile(data) {
	      return Template.compile(this.options.template, data);
	    }
	  }, {
	    key: "getPaymentData",
	    value: function getPaymentData(data) {
	      return {
	        env: this.getOption('env'),
	        version: 2,
	        countryCode: YaPay$1.CountryCode.Ru,
	        currencyCode: YaPay$1.CurrencyCode.Rub,
	        merchant: {
	          id: this.getOption('merchantId'),
	          name: this.getOption('merchantName')
	        },
	        order: {
	          id: data.id,
	          total: {
	            amount: data.total
	          },
	          items: data.items
	        },
	        paymentMethods: [{
	          type: YaPay$1.PaymentMethodType.Card,
	          gateway: this.getOption('gateway'),
	          gatewayMerchantId: this.getOption('gatewayMerchantId'),
	          allowedAuthMethods: [YaPay$1.AllowedAuthMethod.PanOnly],
	          allowedCardNetworks: [YaPay$1.AllowedCardNetwork.UnionPay, YaPay$1.AllowedCardNetwork.Uzcard, YaPay$1.AllowedCardNetwork.Discover, YaPay$1.AllowedCardNetwork.AmericanExpress, YaPay$1.AllowedCardNetwork.Visa, YaPay$1.AllowedCardNetwork.Mastercard, YaPay$1.AllowedCardNetwork.Mir, YaPay$1.AllowedCardNetwork.Maestro, YaPay$1.AllowedCardNetwork.VisaElectron]
	        }]
	      };
	    }
	  }, {
	    key: "createPayment",
	    value: function createPayment(node, paymentData) {
	      var _this = this;

	      // Создать платеж.
	      YaPay$1.createPayment(paymentData, {
	        agent: {
	          name: "CMS-Bitrix",
	          version: "1.0"
	        }
	      }).then(function (payment) {
	        // Создать экземпляр кнопки.
	        var button = payment.createButton({
	          type: YaPay$1.ButtonType.Pay,
	          theme: _this.getOption('buttonTheme') || YaPay$1.ButtonTheme.Black,
	          width: _this.getOption('buttonWidth') || YaPay$1.ButtonWidth.Auto
	        }); // Смонтировать кнопку в DOM.

	        button.mount(node); // Подписаться на событие click.

	        button.on(YaPay$1.ButtonEventType.Click, function onPaymentButtonClick() {
	          // Запустить оплату после клика на кнопку.
	          payment.checkout();
	        }); // Подписаться на событие process.

	        payment.on(YaPay$1.PaymentEventType.Process, function (event) {
	          // Получить платежный токен.
	          _this.notify(payment, event).then(function (result) {});
	          /*alert('Payment token — ' + event.token);
	          		// Опционально (если выполнить шаг 7).
	          alert('Billing email — ' + event.billingContact.email);
	          		// Закрыть форму Yandex Pay.
	          */


	          payment.complete(YaPay$1.CompleteReason.Success);
	        }); // Подписаться на событие error.

	        payment.on(YaPay$1.PaymentEventType.Error, function onPaymentError(event) {
	          // Вывести информацию о недоступности оплаты в данный момент
	          // и предложить пользователю другой способ оплаты.
	          // Закрыть форму Yandex.Pay.
	          console.log({
	            'errors': event
	          });
	          payment.complete(YaPay$1.CompleteReason.Error);
	        }); // Подписаться на событие abort.
	        // Это когда пользователь закрыл форму Yandex Pay.

	        payment.on(YaPay$1.PaymentEventType.Abort, function onPaymentAbort(event) {// Предложить пользователю другой способ оплаты.
	        });
	      }).catch(function (err) {
	        // Платеж не создан.
	        console.log({
	          'payment not create': err
	        });
	      });
	    }
	  }, {
	    key: "notify",
	    value: function notify(payment, yandexPayData) {
	      var _this2 = this;

	      return fetch(this.getOption('notifyUrl'), {
	        method: 'POST',
	        headers: {
	          'Content-Type': 'application/json'
	        },
	        body: JSON.stringify({
	          service: this.getOption('requestSign'),
	          accept: 'json',
	          yandexData: yandexPayData,
	          externalId: this.getOption('externalId'),
	          paySystemId: this.getOption('paySystemId')
	        })
	      }).then(function (response) {
	        return response.json();
	      }).then(function (result) {
	        if (result.success === true) {
	          _this2.widget.go(result.state, result);
	        } else {
	          _this2.widget.go('error', result);
	        }
	      }).catch(function (error) {
	        return console.log(error);
	      });
	    }
	  }]);
	  return Payment;
	}(AbstractStep);

	babelHelpers.defineProperty(Payment, "defaults", {
	  template: '<div class="alert alert-success" role="alert"><strong>#MESSAGE#</strong></div>'
	});

	function ready(callback) {
	  if (document.readyState === 'complete' || document.readyState === 'interactive') {
	    setTimeout(callback, 1);
	  } else {
	    document.addEventListener('DOMContentLoaded', callback);
	  }
	}

	var YaPay$2 = window.YaPay;

	var Cart = /*#__PURE__*/function (_AbstractStep) {
	  babelHelpers.inherits(Cart, _AbstractStep);

	  function Cart() {
	    babelHelpers.classCallCheck(this, Cart);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Cart).apply(this, arguments));
	  }

	  babelHelpers.createClass(Cart, [{
	    key: "render",
	    value: function render(node, data) {
	      this.isBootstrap = false;
	      this.element = node;
	      this.paymentData = this.getPaymentData(data);
	      this.paymentButton = null;
	      this.bootSolution();
	      this.insertLoader();
	      this.setupPaymentCash();
	      this.delayBootstrap();
	    }
	  }, {
	    key: "compile",
	    value: function compile(data) {
	      return Template.compile(this.options.template, data);
	    }
	  }, {
	    key: "restore",
	    value: function restore(node) {
	      this.element = node;
	      this.restoreButton(node);
	    }
	  }, {
	    key: "bootSolution",
	    value: function bootSolution() {
	      var solution = this.widget.getSolution();

	      if (solution == null) {
	        return;
	      }

	      solution.bootCart(this);
	    }
	  }, {
	    key: "delayChangeOffer",
	    value: function delayChangeOffer(productId) {
	      this.delay('changeOffer', [productId]);
	    }
	  }, {
	    key: "delayBootstrap",
	    value: function delayBootstrap() {
	      var _this = this;

	      ready(function () {
	        _this.delay('bootstrap');
	      });
	    }
	  }, {
	    key: "bootstrap",
	    value: function bootstrap() {
	      var _this2 = this;

	      this.isBootstrap = true;
	      this.getProducts().then(function (result) {
	        if (result.error) {
	          throw new Error(result.error.message);
	        }

	        _this2.combineOrderWithProducts(result);

	        _this2.createPayment(_this2.element, _this2.paymentData);
	      }).catch(function (error) {//this.showError('bootstrap', '', error);
	      });
	    }
	  }, {
	    key: "changeOffer",
	    value: function changeOffer(newProductId) {
	      var _this3 = this;

	      if (!this.isBootstrap) {
	        return;
	      }

	      var productId = this.getOption('productId');

	      if (productId !== newProductId) {
	        // todo in items
	        this.widget.setOptions({
	          productId: newProductId
	        });
	        this.getProducts().then(function (result) {
	          _this3.combineOrderWithProducts(result);
	        });
	      }
	    }
	  }, {
	    key: "setupPaymentCash",
	    value: function setupPaymentCash() {
	      // Указываем возможность оплаты заказа при получении
	      if (this.getOption('paymentCash') == null) {
	        return;
	      }

	      this.paymentData.paymentMethods.push({
	        type: YaPay$2.PaymentMethodType.Cash
	      });
	    }
	  }, {
	    key: "getPaymentData",
	    value: function getPaymentData(data) {
	      return {
	        env: this.getOption('env'),
	        version: 2,
	        countryCode: YaPay$2.CountryCode.Ru,
	        currencyCode: YaPay$2.CurrencyCode.Rub,
	        merchant: {
	          id: this.getOption('merchantId'),
	          name: this.getOption('merchantName'),
	          url: this.getOption('siteUrl')
	        },
	        order: {
	          id: '0'
	        },
	        paymentMethods: [{
	          type: YaPay$2.PaymentMethodType.Card,
	          gateway: this.getOption('gateway'),
	          gatewayMerchantId: this.getOption('gatewayMerchantId'),
	          allowedAuthMethods: [YaPay$2.AllowedAuthMethod.PanOnly],
	          allowedCardNetworks: [YaPay$2.AllowedCardNetwork.UnionPay, YaPay$2.AllowedCardNetwork.Uzcard, YaPay$2.AllowedCardNetwork.Discover, YaPay$2.AllowedCardNetwork.AmericanExpress, YaPay$2.AllowedCardNetwork.Visa, YaPay$2.AllowedCardNetwork.Mastercard, YaPay$2.AllowedCardNetwork.Mir, YaPay$2.AllowedCardNetwork.Maestro, YaPay$2.AllowedCardNetwork.VisaElectron]
	        }],
	        requiredFields: {
	          billingContact: {
	            email: this.getOption('useEmail') || false
	          },
	          shippingContact: {
	            name: this.getOption('useName') || false,
	            email: this.getOption('useEmail') || false,
	            phone: this.getOption('usePhone') || false
	          },
	          shippingTypes: {
	            direct: true,
	            pickup: true
	          }
	        }
	      };
	    }
	  }, {
	    key: "createPayment",
	    value: function createPayment(node, paymentData) {
	      var _this4 = this;

	      // Создать платеж.
	      YaPay$2.createPayment(paymentData, {
	        agent: {
	          name: "CMS-Bitrix",
	          version: "1.0"
	        }
	      }).then(function (payment) {
	        _this4.removeLoader();

	        _this4.mountButton(node, payment); // Подписаться на событие process.


	        payment.on(YaPay$2.PaymentEventType.Process, function (event) {
	          // Получить платежный токен.
	          _this4.orderAccept(event).then(function (result) {
	            if (result.error) {
	              throw new Error(result.error.message, result.error.code);
	            }

	            if (!_this4.isPaymentTypeCash(event)) {
	              _this4.notify(result, event).then(function (result) {
	                if (result.success === true) {
	                  _this4.widget.go(result.state, result);

	                  payment.complete(YaPay$2.CompleteReason.Success);
	                } else {
	                  _this4.widget.go('error', result);

	                  payment.complete(YaPay$2.CompleteReason.Error);
	                }
	              });
	            } else {
	              payment.complete(YaPay$2.CompleteReason.Success);

	              if (result.redirect != null) {
	                window.location.href = result.redirect;
	              }
	            }
	          }).catch(function (error) {
	            _this4.showError('yapayProcess', '', error); // todo test it


	            payment.complete(YaPay$2.CompleteReason.Error);
	          });
	        }); // Подписаться на событие error.

	        payment.on(YaPay$2.PaymentEventType.Error, function (event) {
	          _this4.showError('yapayError', 'service temporary unavailable');

	          payment.complete(YaPay$2.CompleteReason.Error);
	        }); // Подписаться на событие change.

	        payment.on(YaPay$2.PaymentEventType.Change, function (event) {
	          if (event.shippingAddress) {
	            _this4.getShippingOptions(event.shippingAddress).then(function (result) {
	              payment.update({
	                shippingOptions: result
	              });
	            });
	          }

	          if (event.shippingOption) {
	            payment.update({
	              order: _this4.combineOrderWithDirectShipping(event.shippingOption)
	            });
	          }

	          if (event.pickupBounds) {
	            _this4.getPickupOptions(event.pickupBounds).then(function (result) {
	              payment.update({
	                pickupPoints: result
	              });
	            });
	          }

	          if (event.pickupPoint) {
	            payment.update({
	              order: _this4.combineOrderWithPickupShipping(event.pickupPoint)
	            });
	          }
	        });
	      }).catch(function (err) {
	        _this4.showError('yapayPayment', 'payment not created', err);
	      });
	    }
	  }, {
	    key: "mountButton",
	    value: function mountButton(node, payment) {
	      this.paymentButton = payment.createButton({
	        type: YaPay$2.ButtonType.Checkout,
	        theme: this.getOption('buttonTheme') || YaPay$2.ButtonTheme.Black,
	        width: this.getOption('buttonWidth') || YaPay$2.ButtonWidth.Auto
	      });
	      this.paymentButton.mount(this.element);
	      this.paymentButton.on(YaPay$2.ButtonEventType.Click, function () {
	        payment.checkout();
	      });
	    }
	  }, {
	    key: "restoreButton",
	    value: function restoreButton(node) {
	      if (this.paymentButton == null) {
	        this.insertLoader();
	        return;
	      } //this.removeLoader();


	      this.paymentButton.mount(node);
	    }
	  }, {
	    key: "isPaymentTypeCash",
	    value: function isPaymentTypeCash(event) {
	      return event.paymentMethodInfo.type === 'CASH';
	    }
	  }, {
	    key: "getProducts",
	    value: function getProducts() {
	      var data = {
	        yapayAction: 'getProducts',
	        productId: this.getOption('productId'),
	        mode: this.getOption('mode'),
	        setupId: this.getOption('setupId')
	      };
	      return this.query(this.getOption('purchaseUrl'), data);
	    }
	  }, {
	    key: "notify",
	    value: function notify(payment, yandexPayData) {
	      var data = {
	        service: this.getOption('requestSign'),
	        accept: 'json',
	        yandexData: yandexPayData,
	        externalId: payment.externalId,
	        paySystemId: payment.paySystemId
	      };
	      return this.query(this.getOption('notifyUrl'), data);
	    }
	  }, {
	    key: "orderAccept",
	    value: function orderAccept(event) {
	      var deliveryType = event.shippingMethodInfo.shippingOption ? 'delivery' : 'pickup';
	      var delivery;

	      if (deliveryType === 'pickup') {
	        delivery = {
	          address: event.shippingMethodInfo.pickupPoint.address,
	          pickup: event.shippingMethodInfo.pickupPoint
	        };
	      } else {
	        delivery = {
	          address: event.shippingMethodInfo.shippingAddress,
	          delivery: event.shippingMethodInfo.shippingOption
	        };
	      }

	      var orderData = {
	        setupId: this.getOption('setupId'),
	        items: this.paymentData.order.items,
	        payment: event.paymentMethodInfo,
	        contact: event.shippingContact,
	        yapayAction: 'orderAccept',
	        deliveryType: deliveryType,
	        paySystemId: this.isPaymentTypeCash(event) ? this.getOption('paymentCash') : this.getOption('paySystemId'),
	        orderAmount: event.orderAmount
	      };
	      var data = babelHelpers.objectSpread({}, orderData, delivery);
	      return this.query(this.getOption('purchaseUrl'), data);
	    }
	  }, {
	    key: "getShippingOptions",
	    value: function getShippingOptions(address) {
	      var data = {
	        address: address,
	        yapayAction: 'deliveryOptions',
	        items: this.paymentData.order.items,
	        setupId: this.getOption('setupId')
	      };
	      return this.query(this.getOption('purchaseUrl'), data);
	    }
	  }, {
	    key: "getPickupOptions",
	    value: function getPickupOptions(bounds) {
	      var data = {
	        bounds: bounds,
	        yapayAction: 'pickupOptions',
	        items: this.paymentData.order.items,
	        setupId: this.getOption('setupId')
	      };
	      return this.query(this.getOption('purchaseUrl'), data);
	    }
	  }, {
	    key: "combineOrderWithPickupShipping",
	    value: function combineOrderWithPickupShipping(pickupOption) {
	      var order = this.paymentData.order;
	      return babelHelpers.objectSpread({}, order, {
	        items: [].concat(babelHelpers.toConsumableArray(order.items), [{
	          type: 'SHIPPING',
	          label: pickupOption.label,
	          amount: pickupOption.amount
	        }]),
	        total: babelHelpers.objectSpread({}, order.total, {
	          amount: this.amountSum(order.total.amount, pickupOption.amount)
	        })
	      });
	    }
	  }, {
	    key: "combineOrderWithDirectShipping",
	    value: function combineOrderWithDirectShipping(shippingOption) {
	      var order = this.paymentData.order;
	      return babelHelpers.objectSpread({}, order, {
	        items: [].concat(babelHelpers.toConsumableArray(order.items), [{
	          type: 'SHIPPING',
	          label: shippingOption.label,
	          amount: shippingOption.amount
	        }]),
	        total: babelHelpers.objectSpread({}, order.total, {
	          amount: this.amountSum(order.total.amount, shippingOption.amount)
	        })
	      });
	    }
	  }, {
	    key: "combineOrderWithProducts",
	    value: function combineOrderWithProducts(products) {
	      var order = this.paymentData.order;
	      var exampleOrder = babelHelpers.objectSpread({}, order, {
	        items: products.items,
	        total: {
	          amount: products.amount
	        }
	      });
	      Object.assign(this.paymentData.order, exampleOrder);
	    }
	  }, {
	    key: "amountSum",
	    value: function amountSum(amountA, amountB) {
	      return (Number(amountA) + Number(amountB)).toFixed(2);
	    }
	  }, {
	    key: "showError",
	    value: function showError(type, message) {
	      var err = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
	      var notify = type + ' - ' + message;

	      if (err) {
	        notify += ' ' + err;
	      }

	      alert(notify);
	    }
	  }, {
	    key: "insertLoader",
	    value: function insertLoader() {
	      var width = this.getOption('buttonWidth') || YaPay$2.ButtonWidth.Auto;
	      this.element.innerHTML = Template.compile(this.getOption('loaderTemplate'), {
	        width: width.toLowerCase(),
	        label: this.getOption('label')
	      });
	    }
	  }, {
	    key: "removeLoader",
	    value: function removeLoader() {
	      var loader = this.element.querySelector(this.getOption('loaderSelector'));

	      if (loader == null) {
	        return;
	      }

	      loader.remove();
	    }
	  }]);
	  return Cart;
	}(AbstractStep);

	babelHelpers.defineProperty(Cart, "defaults", {
	  loaderTemplate: '<div class="bx-yapay-skeleton-loading width--#WIDTH#"></div>',
	  loaderSelector: '.bx-yapay-skeleton-loading'
	});

	var Factory$1 = /*#__PURE__*/function () {
	  function Factory() {
	    babelHelpers.classCallCheck(this, Factory);
	  }

	  babelHelpers.createClass(Factory, null, [{
	    key: "make",

	    /**
	     * @param {string} type
	     * @param {Widget} widget
	     * @param {Object} options
	     * @returns {Cart|Finish|Step3ds|Payment|Failure}
	     * @throws {Error}
	     */
	    value: function make(type, widget) {
	      var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};

	      if (type === '3ds') {
	        return new Step3ds(widget, options);
	      } else if (type === 'finished') {
	        return new Finish(widget, options);
	      } else if (type === 'error') {
	        return new Failure(widget, options);
	      } else if (type === 'payment') {
	        return new Payment(widget, options);
	      } else if (type === 'cart') {
	        return new Cart(widget, options);
	      }

	      throw new Error('unknown step ' + type);
	    }
	  }]);
	  return Factory;
	}();

	var Widget = /*#__PURE__*/function () {
	  /**
	   * @param {Object<Element>} element
	   * @param {Object} options
	   */
	  function Widget(element) {
	    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, Widget);
	    this.defaults = Object.assign({}, this.constructor.defaults);
	    this.options = {};
	    this.el = element;
	    this.setOptions(options);
	    this.bootSolution();
	  }
	  /**
	   * @param {Object} data
	   */


	  babelHelpers.createClass(Widget, [{
	    key: "payment",
	    value: function payment(data) {
	      this.go('payment', data);
	    }
	    /**
	     * @param {Object} data
	     */

	  }, {
	    key: "cart",
	    value: function cart(data) {
	      this.go('cart', data);
	    }
	  }, {
	    key: "restore",
	    value: function restore(element) {
	      var _this$step;

	      this.el = element;
	      (_this$step = this.step) === null || _this$step === void 0 ? void 0 : _this$step.restore(element);
	    }
	    /**
	     * @param {string} type
	     * @param {Object} data
	     */

	  }, {
	    key: "go",
	    value: function go(type, data) {
	      this.step = this.makeStep(type);
	      this.step.render(this.el, data);
	    }
	    /**
	     * @param {String} type
	     * @returns {Cart|Finish|Step3ds|Payment|Failure}
	     * @throws {Error}
	     */

	  }, {
	    key: "makeStep",
	    value: function makeStep(type) {
	      var options = this.getOption(type) || {};
	      return Factory$1.make(type, this, options);
	    }
	  }, {
	    key: "getSolution",
	    value: function getSolution() {
	      var name = this.getOption('solution');
	      var mode = this.getOption('mode');
	      return SolutionRegistry.getPage(name, mode);
	    }
	  }, {
	    key: "bootSolution",
	    value: function bootSolution() {
	      var solution = this.getSolution();

	      if (solution == null) {
	        return;
	      }

	      solution.bootWidget(this);
	    }
	  }, {
	    key: "extendDefaults",
	    value: function extendDefaults(options) {
	      this.defaults = Object.assign(this.defaults, options);
	    }
	  }, {
	    key: "setOptions",
	    value: function setOptions(options) {
	      this.options = Object.assign(this.options, options);
	    }
	  }, {
	    key: "getOption",
	    value: function getOption(name) {
	      var _this$options$name;

	      return (_this$options$name = this.options[name]) !== null && _this$options$name !== void 0 ? _this$options$name : this.defaults[name];
	    }
	  }]);
	  return Widget;
	}();

	babelHelpers.defineProperty(Widget, "defaults", {});

	exports.Factory = Factory;
	exports.Widget = Widget;

}((this.BX.YandexPay = this.BX.YandexPay || {})));
//# sourceMappingURL=widget.js.map
