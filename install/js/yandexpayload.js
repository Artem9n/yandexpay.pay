(function(window) {

	'use strict';

	window.YandexPay =
	{
		load: function(element, options)
		{
			this.defaults = {
				finishedTemplate:   '<div class="alert alert-success" role="alert"><strong>#MESSAGE#</strong></div>',
				errorTemplate:      '<div class="alert alert-danger" role="alert"><strong>#MESSAGE#</strong></div>'
			}

			this.options = {... this.defaults,... options || {}};

			this.setOrigin(element);
			this.initialize();
		},

		setOrigin: function(element) {
			this.el = element;
		},

		initialize: function() {

			// Сформировать данные платежа.
			this.setPaymentData();

			// Создать платеж.
			this.createPayment();
		},

		setPaymentData: function() {

			const YaPay = window.YaPay;

			this.paymentData = {
				env: this.options.env,
				version: 2,
				countryCode: YaPay.CountryCode.Ru,
				currencyCode: YaPay.CurrencyCode.Rub,
				merchant: {
					id: this.options.merchantId,
					name: this.options.merchantName
				},
				order: {
					id: this.options.order.id,
					total: { amount: this.options.order.total },
					items: this.options.order.items
				},
				paymentMethods: [
					{
						type: YaPay.PaymentMethodType.Card,
						gateway: this.options.gateway,
						gatewayMerchantId: this.options.gatewayMerchantId,
						allowedAuthMethods: [YaPay.AllowedAuthMethod.PanOnly],
						allowedCardNetworks: [
							YaPay.AllowedCardNetwork.UnionPay,
							YaPay.AllowedCardNetwork.Uzcard,
							YaPay.AllowedCardNetwork.Discover,
							YaPay.AllowedCardNetwork.AmericanExpress,
							YaPay.AllowedCardNetwork.Visa,
							YaPay.AllowedCardNetwork.Mastercard,
							YaPay.AllowedCardNetwork.Mir,
							YaPay.AllowedCardNetwork.Maestro,
							YaPay.AllowedCardNetwork.VisaElectron
						]
					}
				],
			}

			console.log(this.paymentData);
		},

		createPayment: function() {
			// Создать платеж.
			YaPay.createPayment(this.paymentData)
				.then( (payment) => {
					// Создать экземпляр кнопки.
					let container = this.el;
					let button = payment.createButton({
						type: YaPay.ButtonType.Pay,
						theme: this.options.buttonTheme || YaPay.ButtonTheme.Black,
						width: this.options.buttonWidth || YaPay.ButtonWidth.Auto,
					});

					// Смонтировать кнопку в DOM.
					button.mount(container);

					// Подписаться на событие click.
					button.on(YaPay.ButtonEventType.Click, function onPaymentButtonClick() {
						// Запустить оплату после клика на кнопку.
						payment.checkout();
					});

					// Подписаться на событие process.
					payment.on(YaPay.PaymentEventType.Process, (event) => {
						// Получить платежный токен.
						console.log(event);
						this.token = event.token;
						this.yandexPayData = event;

						this.query(payment);

						/*alert('Payment token — ' + event.token);

						// Опционально (если выполнить шаг 7).
						alert('Billing email — ' + event.billingContact.email);

						// Закрыть форму Yandex Pay.
						*/
						//payment.complete(YaPay.CompleteReason.Success);
					});

					// Подписаться на событие error.
					payment.on(YaPay.PaymentEventType.Error, function onPaymentError(event) {
						// Вывести информацию о недоступности оплаты в данный момент
						// и предложить пользователю другой способ оплаты.

						// Закрыть форму Yandex.Pay.
						console.log({'errors': event});
						payment.complete(YaPay.CompleteReason.Error);
					});

					// Подписаться на событие abort.
					// Это когда пользователь закрыл форму Yandex Pay.
					payment.on(YaPay.PaymentEventType.Abort, function onPaymentAbort(event) {
						// Предложить пользователю другой способ оплаты.
					});
				})
				.catch(function (err) {
					// Платеж не создан.
					console.log({'payment not create': err});
				});
		},

		query: function(payment) {
			fetch(this.options.YANDEX_PAY_NOTIFY_URL, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify({
					yandexData: this.yandexPayData,
					externalId: this.options.externalId,
					paySystemId: this.options.paySystemId
				})
			}).then(response => response.json().then(result =>
			{
				payment.complete(YaPay.CompleteReason.Success);

				if (result.success === true)
				{
					if (result.state === 'finished')
					{
						this.el.innerHTML = this.getTemplate('finished').replace('#MESSAGE#', result.message);
					}
				}
				else
				{
					this.el.innerHTML = this.getTemplate('error').replace('#MESSAGE#', result.errors)
				}

			}));
		},

		getTemplate: function(key) {
			let optionKey = key + 'Template';
			let option = this.options[optionKey];
			let optionFirstSymbol = option.substr(0, 1);
			let result;

			if (optionFirstSymbol === '.' || optionFirstSymbol === '#') {
				result = this.getNode(option).innerHTML;
			} else {
				result = option;
			}

			return result;
		},

		getNode: function(selector, context, method) {
			let result;

			if (selector.substr(0, 1) === '#') { // is id query
				context = document;
			} else if (!context) {
				context = this.el;
			}

			return context[method](selector);
		}
	};

})(window);
