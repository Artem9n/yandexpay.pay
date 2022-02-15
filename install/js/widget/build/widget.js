this.BX = this.BX || {};
(function (exports) {
	'use strict';

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
	  }]);
	  return Template;
	}();

	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it.return != null) it.return(); } finally { if (didErr) throw err; } } }; }

	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

	var Factory = /*#__PURE__*/function () {
	  function Factory() {
	    babelHelpers.classCallCheck(this, Factory);
	    babelHelpers.defineProperty(this, "defaults", {
	      template: '<div id="yandexpay" class="yandex-pay"></div>'
	    });
	  }

	  babelHelpers.createClass(Factory, [{
	    key: "inject",
	    value: function inject(selector, position) {
	      var _this = this;

	      return Promise.resolve().then(function () {
	        return _this.waitElement(selector);
	      }).then(function (anchor) {
	        var element = _this.renderElement(anchor, position);

	        return _this.install(element);
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
	      var _this2 = this;

	      return new Promise(function (resolve, reject) {
	        _this2.waitCount = 0;
	        _this2.waitLimit = 10;

	        _this2.waitElementLoop(selector, resolve, reject);
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

	      if (this.waitCount >= this.waitLimit) {
	        reject('cant find element by selector ' + selector);
	        return;
	      }

	      setTimeout(this.waitElementLoop.bind(this, selector, resolve, reject), 1000);
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

	        var _iterator = _createForOfIteratorHelper(selector.split(',')),
	            _step;

	        try {
	          for (_iterator.s(); !(_step = _iterator.n()).done;) {
	            var part = _step.value;
	            // first selector
	            var partSanitized = part.trim();

	            if (partSanitized === '') {
	              continue;
	            }

	            var collection = document.querySelectorAll(partSanitized);

	            var _iterator2 = _createForOfIteratorHelper(collection),
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

	      var _iterator3 = _createForOfIteratorHelper(collection),
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
	      var result = Template.toElement(this.defaults.template);
	      anchor.insertAdjacentElement(position, result);
	      return result;
	    }
	  }]);
	  return Factory;
	}();

	var AbstractStep = /*#__PURE__*/function () {
	  /**
	   * @param {Object} options
	   */
	  function AbstractStep() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, AbstractStep);
	    this.options = Object.assign({}, this.constructor.defaults, options);
	    this.widget = null;
	    this.delayTimeouts = {};
	  }
	  /**
	   *
	   * @param {Widget} widget
	   */


	  babelHelpers.createClass(AbstractStep, [{
	    key: "setWidget",
	    value: function setWidget(widget) {
	      this.widget = widget;
	    }
	    /**
	     *
	     * @param {string} key
	     * @returns {*}
	     */

	  }, {
	    key: "getOption",
	    value: function getOption(key) {
	      var section = this.constructor.optionSection;
	      /*if (this.widget.options[section] !== null && this.widget.options[section][key]) {
	      	return this.widget.options[section][key];
	      } else */

	      if (key in this.options) {
	        return this.options[key];
	      } else {
	        return this.widget.options[key];
	      }
	    }
	  }, {
	    key: "setOption",
	    value: function setOption(key, value) {
	      if (key in this.options) {
	        this.options[key] = value;
	      } else if (key in this.widget.options) {
	        this.widget.options[key] = value;
	      }
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
	      return Template.compile(this.options.template, data);
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
	      if (this.delayTimeouts[name] === null) {
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

	babelHelpers.defineProperty(AbstractStep, "optionSection", null);
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

	babelHelpers.defineProperty(Finish, "optionPrefix", 'finish');
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

	babelHelpers.defineProperty(Failure, "optionPrefix", 'failure');
	babelHelpers.defineProperty(Failure, "defaults", {
	  template: '<div class="alert alert-danger" role="alert"><strong>#MESSAGE#</strong></div>'
	});

	var YaPay = window.YaPay;

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
	        countryCode: YaPay.CountryCode.Ru,
	        currencyCode: YaPay.CurrencyCode.Rub,
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
	          type: YaPay.PaymentMethodType.Card,
	          gateway: this.getOption('gateway'),
	          gatewayMerchantId: this.getOption('gatewayMerchantId'),
	          allowedAuthMethods: [YaPay.AllowedAuthMethod.PanOnly],
	          allowedCardNetworks: [YaPay.AllowedCardNetwork.UnionPay, YaPay.AllowedCardNetwork.Uzcard, YaPay.AllowedCardNetwork.Discover, YaPay.AllowedCardNetwork.AmericanExpress, YaPay.AllowedCardNetwork.Visa, YaPay.AllowedCardNetwork.Mastercard, YaPay.AllowedCardNetwork.Mir, YaPay.AllowedCardNetwork.Maestro, YaPay.AllowedCardNetwork.VisaElectron]
	        }]
	      };
	    }
	  }, {
	    key: "createPayment",
	    value: function createPayment(node, paymentData) {
	      var _this = this;

	      // Создать платеж.
	      YaPay.createPayment(paymentData, {
	        agent: {
	          name: "CMS-Bitrix",
	          version: "1.0"
	        }
	      }).then(function (payment) {
	        // Создать экземпляр кнопки.
	        var button = payment.createButton({
	          type: YaPay.ButtonType.Pay,
	          theme: _this.getOption('buttonTheme') || YaPay.ButtonTheme.Black,
	          width: _this.getOption('buttonWidth') || YaPay.ButtonWidth.Auto
	        }); // Смонтировать кнопку в DOM.

	        button.mount(node); // Подписаться на событие click.

	        button.on(YaPay.ButtonEventType.Click, function onPaymentButtonClick() {
	          // Запустить оплату после клика на кнопку.
	          payment.checkout();
	        }); // Подписаться на событие process.

	        payment.on(YaPay.PaymentEventType.Process, function (event) {
	          // Получить платежный токен.
	          _this.notify(payment, event).then(function (result) {});
	          /*alert('Payment token — ' + event.token);
	          		// Опционально (если выполнить шаг 7).
	          alert('Billing email — ' + event.billingContact.email);
	          		// Закрыть форму Yandex Pay.
	          */


	          payment.complete(YaPay.CompleteReason.Success);
	        }); // Подписаться на событие error.

	        payment.on(YaPay.PaymentEventType.Error, function onPaymentError(event) {
	          // Вывести информацию о недоступности оплаты в данный момент
	          // и предложить пользователю другой способ оплаты.
	          // Закрыть форму Yandex.Pay.
	          console.log({
	            'errors': event
	          });
	          payment.complete(YaPay.CompleteReason.Error);
	        }); // Подписаться на событие abort.
	        // Это когда пользователь закрыл форму Yandex Pay.

	        payment.on(YaPay.PaymentEventType.Abort, function onPaymentAbort(event) {// Предложить пользователю другой способ оплаты.
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

	babelHelpers.defineProperty(Payment, "optionPrefix", 'payment');
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

	var YaPay$1 = window.YaPay;

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
	      this.insertLoader();
	      this.paymentData = this.getPaymentData(data);
	      this.setupPaymentCash();
	      this.installSolution();
	      this.delayBootstrap();
	    }
	  }, {
	    key: "compile",
	    value: function compile(data) {
	      return Template.compile(this.options.template, data);
	    }
	  }, {
	    key: "installSolution",
	    value: function installSolution() {
	      var _window, _window$BX, _window$BX$YandexPay, _window$BX$YandexPay$, _window$BX$YandexPay$2;

	      var type = this.getOption('solution');

	      if (type == null) {
	        return;
	      }

	      var factory = (_window = window) === null || _window === void 0 ? void 0 : (_window$BX = _window.BX) === null || _window$BX === void 0 ? void 0 : (_window$BX$YandexPay = _window$BX.YandexPay) === null || _window$BX$YandexPay === void 0 ? void 0 : (_window$BX$YandexPay$ = _window$BX$YandexPay.Solution) === null || _window$BX$YandexPay$ === void 0 ? void 0 : (_window$BX$YandexPay$2 = _window$BX$YandexPay$[type]) === null || _window$BX$YandexPay$2 === void 0 ? void 0 : _window$BX$YandexPay$2.factory;

	      if (factory == null) {
	        var _console;

	        (_console = console) === null || _console === void 0 ? void 0 : _console.warn("cant find solution ".concat(type));
	        return;
	      }

	      factory.create(this);
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
	          throw new Error(result.error.message, result.error.code);
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
	        this.setOption('productId', newProductId);
	        this.getProducts().then(function (result) {
	          _this3.combineOrderWithProducts(result);
	        });
	      }
	    }
	  }, {
	    key: "setupPaymentCash",
	    value: function setupPaymentCash() {
	      // Указываем возможность оплаты заказа при получении
	      if (this.getOption('paymentCash') !== null) {
	        this.paymentData.paymentMethods.push({
	          type: YaPay$1.PaymentMethodType.Cash
	        });
	      }
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
	          name: this.getOption('merchantName'),
	          url: this.getOption('siteUrl')
	        },
	        order: {
	          id: '0'
	        },
	        paymentMethods: [{
	          type: YaPay$1.PaymentMethodType.Card,
	          gateway: this.getOption('gateway'),
	          gatewayMerchantId: this.getOption('gatewayMerchantId'),
	          allowedAuthMethods: [YaPay$1.AllowedAuthMethod.PanOnly],
	          allowedCardNetworks: [YaPay$1.AllowedCardNetwork.UnionPay, YaPay$1.AllowedCardNetwork.Uzcard, YaPay$1.AllowedCardNetwork.Discover, YaPay$1.AllowedCardNetwork.AmericanExpress, YaPay$1.AllowedCardNetwork.Visa, YaPay$1.AllowedCardNetwork.Mastercard, YaPay$1.AllowedCardNetwork.Mir, YaPay$1.AllowedCardNetwork.Maestro, YaPay$1.AllowedCardNetwork.VisaElectron]
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
	      YaPay$1.createPayment(paymentData, {
	        agent: {
	          name: "CMS-Bitrix",
	          version: "1.0"
	        }
	      }).then(function (payment) {
	        // Создать экземпляр кнопки.
	        var button = payment.createButton({
	          type: YaPay$1.ButtonType.Checkout,
	          theme: _this4.getOption('buttonTheme') || YaPay$1.ButtonTheme.Black,
	          width: _this4.getOption('buttonWidth') || YaPay$1.ButtonWidth.Auto
	        }); // Смонтировать кнопку в DOM.

	        _this4.removeLoader();

	        button.mount(node); // Подписаться на событие click.

	        button.on(YaPay$1.ButtonEventType.Click, function () {
	          // Запустить оплату после клика на кнопку.
	          payment.checkout();
	        }); // Подписаться на событие process.

	        payment.on(YaPay$1.PaymentEventType.Process, function (event) {
	          // Получить платежный токен.
	          _this4.orderAccept(event).then(function (result) {
	            if (result.error) {
	              throw new Error(result.error.message, result.error.code);
	            }

	            if (!_this4.isPaymentTypeCash(event)) {
	              _this4.notify(result, event).then(function (result) {
	                if (result.success === true) {
	                  _this4.widget.go(result.state, result);

	                  payment.complete(YaPay$1.CompleteReason.Success);
	                } else {
	                  _this4.widget.go('error', result);

	                  payment.complete(YaPay$1.CompleteReason.Error);
	                }
	              });
	            } else {
	              payment.complete(YaPay$1.CompleteReason.Success);

	              if (result.redirect !== null) {
	                window.location.href = result.redirect;
	              }
	            }
	          }).catch(function (error) {
	            _this4.showError('yapayProcess', '', error); // todo test it


	            payment.complete(YaPay$1.CompleteReason.Error);
	          });
	        }); // Подписаться на событие error.

	        payment.on(YaPay$1.PaymentEventType.Error, function (event) {
	          _this4.showError('yapayError', 'service temporary unavailable');

	          payment.complete(YaPay$1.CompleteReason.Error);
	        }); // Подписаться на событие change.

	        payment.on(YaPay$1.PaymentEventType.Change, function (event) {
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
	      var width = this.getOption('buttonWidth') || YaPay$1.ButtonWidth.Auto;
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
	  loaderTemplate: '<div class="yandex-pay__label width--#WIDTH#"><em></em><span>#LABEL#</span></div>' + '<div class="yandex-pay-skeleton-loading width--#WIDTH#"></div>',
	  loaderSelector: '.yandex-pay-skeleton-loading'
	});

	var Factory$1 = /*#__PURE__*/function () {
	  function Factory() {
	    babelHelpers.classCallCheck(this, Factory);
	  }

	  babelHelpers.createClass(Factory, null, [{
	    key: "make",

	    /**
	     * @param {string} type
	     * @returns {Cart|Finish|Step3ds|Payment|Failure}
	     * @throws {Error}
	     */
	    value: function make(type) {
	      if (type === '3ds') {
	        return new Step3ds();
	      } else if (type === 'finished') {
	        return new Finish();
	      } else if (type === 'error') {
	        return new Failure();
	      } else if (type === 'payment') {
	        return new Payment();
	      } else if (type === 'cart') {
	        return new Cart();
	      }

	      throw new Error('unknown step ' + type);
	    }
	  }]);
	  return Factory;
	}();

	var Widget = /*#__PURE__*/function () {
	  /**
	   * @type {{failureTemplate: string, modalTemplate: string, finishedTemplate: string}}
	   */

	  /**
	   * @param {Object<Element>} element
	   */
	  function Widget(element) {
	    babelHelpers.classCallCheck(this, Widget);
	    babelHelpers.defineProperty(this, "defaults", {
	      finishedTemplate: '<div class="alert alert-success" role="alert"><strong>#MESSAGE#</strong></div>',
	      failureTemplate: '<div class="alert alert-danger" role="alert"><strong>#MESSAGE#</strong></div>',
	      modalTemplate: '<div class="yandex-pay-modal-inner">#IFRAME#</div>'
	    });
	    this.el = element;
	  }

	  babelHelpers.createClass(Widget, [{
	    key: "setOptions",
	    value: function setOptions(options) {
	      this.options = Object.assign({}, this.defaults, options);
	    }
	    /**
	     * @param {Object} data
	     */

	  }, {
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
	    /**
	     * @param {string} type
	     * @param {Object} data
	     */

	  }, {
	    key: "go",
	    value: function go(type, data) {
	      var step = this.makeStep(type);
	      step.render(this.el, data);
	    }
	    /**
	     * @param {String} type
	     * @returns {Cart|Finish|Step3ds|Payment|Failure}
	     * @throws {Error}
	     */

	  }, {
	    key: "makeStep",
	    value: function makeStep(type) {
	      var step = Factory$1.make(type);
	      step.setWidget(this);
	      return step;
	    }
	  }]);
	  return Widget;
	}();

	exports.Factory = Factory;
	exports.Widget = Widget;

}((this.BX.YandexPay = this.BX.YandexPay || {})));
//# sourceMappingURL=widget.js.map
