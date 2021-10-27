this.BX = this.BX || {};
(function (exports) {
	'use strict';

	var Template = /*#__PURE__*/function () {
	  function Template() {
	    babelHelpers.classCallCheck(this, Template);
	  }

	  babelHelpers.createClass(Template, null, [{
	    key: "compile",
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

	var AbstractStep = /*#__PURE__*/function () {
	  function AbstractStep() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, AbstractStep);
	    this.options = Object.assign({}, this.constructor.defaults, options);
	    this.widget = null;
	  }

	  babelHelpers.createClass(AbstractStep, [{
	    key: "setWidget",
	    value: function setWidget(widget) {
	      this.widget = widget;
	    }
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
	  }]);
	  return AbstractStep;
	}();

	babelHelpers.defineProperty(AbstractStep, "optionSection", null);
	babelHelpers.defineProperty(AbstractStep, "defaults", {
	  template: null
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
	      babelHelpers.get(babelHelpers.getPrototypeOf(Step3ds.prototype), "render", this).call(this, node, data);
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

	      template = data.termUrl ? '<input type="hidden" name="TermUrl" value="' + this.makeTermUrl() + '">' : '';

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
	      var result = this.getOption('YANDEX_PAY_NOTIFY_URL');
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
	  return Step3ds;
	}(AbstractStep);

	babelHelpers.defineProperty(Step3ds, "defaults", {
	  url: '/yandex_pay.php',
	  template: '<form name="form" action="#ACTION#" method="#METHOD#">' + '#INPUTS#' + '</form>'
	});

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
	          allowedCardNetworks: this.getOption('cardNetworks') || [YaPay.AllowedCardNetwork.UnionPay, YaPay.AllowedCardNetwork.Uzcard, YaPay.AllowedCardNetwork.Discover, YaPay.AllowedCardNetwork.AmericanExpress, YaPay.AllowedCardNetwork.Visa, YaPay.AllowedCardNetwork.Mastercard, YaPay.AllowedCardNetwork.Mir, YaPay.AllowedCardNetwork.Maestro, YaPay.AllowedCardNetwork.VisaElectron]
	        }]
	      };
	    }
	  }, {
	    key: "createPayment",
	    value: function createPayment(node, paymentData) {
	      var _this = this;

	      // Создать платеж.
	      YaPay.createPayment(paymentData).then(function (payment) {
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
	          //alert({'Process': event});
	          //alert(event);
	          _this.notify(payment, event).then(function (resolve) {//payment.complete(YaPay.CompleteReason.Success);
	          });
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

	      return new Promise(function (resolve) {
	        fetch(_this2.getOption('YANDEX_PAY_NOTIFY_URL'), {
	          method: 'POST',
	          headers: {
	            'Content-Type': 'application/json'
	          },
	          body: JSON.stringify({
	            service: _this2.getOption('requestSign'),
	            accept: 'json',
	            yandexData: yandexPayData,
	            externalId: _this2.getOption('externalId'),
	            paySystemId: _this2.getOption('paySystemId')
	          })
	        }).then(function (response) {
	          return response.json();
	        }).then(function (result) {
	          //payment.complete(YaPay.CompleteReason.Success);
	          resolve();

	          if (result.success === true) {
	            _this2.widget.go(result.state, result);
	          } else {
	            _this2.widget.go('error', result);
	          }
	        }).catch(function (error) {
	          return alert(error);
	        });
	      });
	    }
	  }]);
	  return Payment;
	}(AbstractStep);

	babelHelpers.defineProperty(Payment, "optionPrefix", 'payment');
	babelHelpers.defineProperty(Payment, "defaults", {
	  template: '<div class="alert alert-success" role="alert"><strong>#MESSAGE#</strong></div>'
	});

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
	      this.paymentData = this.getPaymentData(data);
	      this.createPayment(node, this.paymentData);
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
	          name: this.getOption('merchantName'),
	          url: this.getOption('siteUrl')
	        },
	        order: {
	          id: data.id,
	          total: {
	            amount: data.total
	          }
	        },
	        paymentMethods: [{
	          type: YaPay$1.PaymentMethodType.Card,
	          gateway: this.getOption('gateway'),
	          gatewayMerchantId: this.getOption('gatewayMerchantId'),
	          allowedAuthMethods: [YaPay$1.AllowedAuthMethod.PanOnly],
	          allowedCardNetworks: this.getOption('cardNetworks') || [YaPay$1.AllowedCardNetwork.UnionPay, YaPay$1.AllowedCardNetwork.Uzcard, YaPay$1.AllowedCardNetwork.Discover, YaPay$1.AllowedCardNetwork.AmericanExpress, YaPay$1.AllowedCardNetwork.Visa, YaPay$1.AllowedCardNetwork.Mastercard, YaPay$1.AllowedCardNetwork.Mir, YaPay$1.AllowedCardNetwork.Maestro, YaPay$1.AllowedCardNetwork.VisaElectron]
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
	      var _this = this;

	      // Создать платеж.
	      YaPay$1.createPayment(paymentData).then(function (payment) {
	        // Создать экземпляр кнопки.
	        var button = payment.createButton({
	          type: YaPay$1.ButtonType.Pay,
	          theme: _this.getOption('buttonTheme') || YaPay$1.ButtonTheme.Black,
	          width: _this.getOption('buttonWidth') || YaPay$1.ButtonWidth.Auto
	        }); // Смонтировать кнопку в DOM.

	        button.mount(node); // Подписаться на событие click.

	        button.on(YaPay$1.ButtonEventType.Click, function () {
	          // Заполенение товаров
	          _this.fillProducts().then(function (result) {
	            payment.update({
	              order: _this.exampleOrderWithProducts(result)
	            }); // Запустить оплату после клика на кнопку.

	            payment.checkout();
	          });
	        }); // Подписаться на событие process.

	        payment.on(YaPay$1.PaymentEventType.Process, function (event) {
	          // Получить платежный токен.
	          _this.orderAccept('orderAccept', event).then(function (result) {
	            payment.complete(YaPay$1.CompleteReason.Success);

	            _this.notify(result, event); //payment.update({shippingOptions: result})

	          }); //this.notify(payment, event);
	          //payment.complete(YaPay.CompleteReason.Success);

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

	        payment.on(YaPay$1.PaymentEventType.Abort, function (event) {// Предложить пользователю другой способ оплаты.
	        });
	        payment.on(YaPay$1.PaymentEventType.Change, function (event) {
	          console.log(222);

	          if (event.shippingAddress) {
	            _this.exampleDeliveryOptions('deliveryOptions', event.shippingAddress).then(function (result) {
	              payment.update({
	                shippingOptions: result
	              });
	            });
	          }

	          if (event.shippingOption) {
	            payment.update({
	              order: _this.exampleOrderWithDirectShipping(event.shippingOption, payment)
	            });
	          }

	          if (event.pickupAddress) {
	            _this.exampleDeliveryOptions('pickupOptions', event.pickupAddress).then(function (result) {
	              payment.update({
	                pickupOptions: result
	              });
	            });
	          }

	          if (event.pickupOption) {
	            payment.update({
	              order: _this.exampleOrderWithPickupShipping(event.pickupOption)
	            });
	          }
	        });
	      }).catch(function (err) {
	        // Платеж не создан.
	        console.log({
	          'payment not create': err
	        });
	      });
	    }
	  }, {
	    key: "fillProducts",
	    value: function fillProducts() {
	      return fetch(this.getOption('purchaseUrl'), {
	        method: 'POST',
	        headers: {
	          'Content-Type': 'application/json'
	        },
	        body: JSON.stringify({
	          siteId: this.getOption('siteId'),
	          productId: this.getOption('productId') || null,
	          fUserId: this.getOption('fUserId'),
	          userId: this.getOption('userId') || null,
	          setupId: this.getOption('setupId') || null,
	          yapayAction: 'getProducts',
	          mode: this.getOption('mode')
	        })
	      }).then(function (response) {
	        return response.json();
	      });
	    }
	  }, {
	    key: "notify",
	    value: function notify(payment, yandexPayData) {
	      var _this2 = this;

	      fetch(this.getOption('notifyUrl'), {
	        method: 'POST',
	        headers: {
	          'Content-Type': 'application/json'
	        },
	        body: JSON.stringify({
	          service: this.getOption('requestSign'),
	          accept: 'json',
	          yandexData: yandexPayData,
	          externalId: payment.externalId //paySystemId: this.getOption('paySystemId')

	        })
	      }).then(function (response) {
	        return response.json();
	      }).then(function (result) {
	        if (result.success === true) {
	          _this2.widget.go(result.state, result);
	        } else {
	          _this2.widget.go('error', result);
	        }
	      });
	    }
	  }, {
	    key: "orderAccept",
	    value: function orderAccept(action, event) {
	      return fetch(this.getOption('purchaseUrl'), {
	        method: 'POST',
	        headers: {
	          'Content-Type': 'application/json'
	        },
	        body: JSON.stringify({
	          siteId: this.getOption('siteId'),
	          productId: this.getOption('productId') || null,
	          order: this.paymentData.order,
	          fUserId: this.getOption('fUserId'),
	          userId: this.getOption('userId') || null,
	          setupId: this.getOption('setupId') || null,
	          yapayAction: action,
	          address: event.shippingMethodInfo.shippingAddress,
	          contact: event.shippingContact,
	          paySystemId: this.getOption('paySystemId') || null,
	          mode: this.getOption('mode'),
	          delivery: event.shippingMethodInfo.shippingOption || event.shippingMethodInfo.pickupOptions
	        })
	      }).then(function (response) {
	        return response.json();
	      });
	    }
	  }, {
	    key: "exampleDeliveryOptions",
	    value: function exampleDeliveryOptions(action, address) {
	      return fetch(this.getOption('purchaseUrl'), {
	        method: 'POST',
	        headers: {
	          'Content-Type': 'application/json'
	        },
	        body: JSON.stringify({
	          siteId: this.getOption('siteId'),
	          productId: this.getOption('productId') || null,
	          fUserId: this.getOption('fUserId'),
	          userId: this.getOption('userId') || null,
	          setupId: this.getOption('setupId') || null,
	          mode: this.getOption('mode'),
	          address: address,
	          yapayAction: action
	        })
	      }).then(function (response) {
	        return response.json();
	      });
	    }
	  }, {
	    key: "exampleOrderWithDirectShipping",
	    value: function exampleOrderWithDirectShipping(shippingOption) {
	      var order = this.paymentData.order;
	      console.log(shippingOption);
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
	    key: "exampleOrderWithPickupShipping",
	    value: function exampleOrderWithPickupShipping(pickupOption) {
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
	    key: "exampleOrderWithProducts",
	    value: function exampleOrderWithProducts(products) {
	      var order = this.paymentData.order;
	      var exampleOrder = babelHelpers.objectSpread({}, order, {
	        items: products.items,
	        total: babelHelpers.objectSpread({}, order.total, {
	          amount: this.amountSum(0, products.amount)
	        })
	      });
	      Object.assign(this.paymentData.order, exampleOrder);
	      return exampleOrder;
	    }
	  }, {
	    key: "amountSum",
	    value: function amountSum(amountA, amountB) {
	      return (Number(amountA) + Number(amountB)).toFixed(2);
	    }
	  }]);
	  return Cart;
	}(AbstractStep);

	var Factory = /*#__PURE__*/function () {
	  function Factory() {
	    babelHelpers.classCallCheck(this, Factory);
	  }

	  babelHelpers.createClass(Factory, null, [{
	    key: "make",
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
	  function Widget(element) {
	    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, Widget);
	    babelHelpers.defineProperty(this, "defaults", {
	      finishedTemplate: '<div class="alert alert-success" role="alert"><strong>#MESSAGE#</strong></div>',
	      failureTemplate: '<div class="alert alert-danger" role="alert"><strong>#MESSAGE#</strong></div>',
	      modalTemplate: '<div class="yandex-pay-modal-inner">#IFRAME#</div>'
	    });
	    this.el = element;
	    this.options = Object.assign({}, this.defaults, options);
	  }

	  babelHelpers.createClass(Widget, [{
	    key: "payment",
	    value: function payment(data) {
	      this.go('payment', data);
	    }
	  }, {
	    key: "cart",
	    value: function cart(data) {
	      this.go('cart', data);
	    }
	  }, {
	    key: "go",
	    value: function go(type, data) {
	      var step = this.makeStep(type);
	      step.render(this.el, data);
	    }
	  }, {
	    key: "makeStep",
	    value: function makeStep(type) {
	      var step = Factory.make(type);
	      step.setWidget(this);
	      return step;
	    }
	  }]);
	  return Widget;
	}();

	exports.Widget = Widget;

}((this.BX.YandexPay = this.BX.YandexPay || {})));
//# sourceMappingURL=widget.js.map
