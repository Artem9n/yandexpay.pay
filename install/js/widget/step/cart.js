import Template from '../utils/template';
import AbstractStep from './abstractstep';

const YaPay = window.YaPay;

export default class Cart extends AbstractStep {

	render(node, data) {
		this.paymentData = this.getPaymentData(data);
		console.log(this.paymentData);
		this.createPayment(node, this.paymentData);
	}

	compile(data) {
		return Template.compile(this.options.template, data);
	}

	getPaymentData(data) {
		return {
			env: this.getOption('env'),
			version: 2,
			countryCode: YaPay.CountryCode.Ru,
			currencyCode: YaPay.CurrencyCode.Rub,
			merchant: {
				id: this.getOption('merchantId'),
				name: this.getOption('merchantName'),
				url: this.getOption('siteUrl')
			},
			order: {
				id: data.id,
				total: { amount: data.total },
				items: data.items
			},
			paymentMethods: [
				{
					type: YaPay.PaymentMethodType.Card,
					gateway: this.getOption('gateway'),
					gatewayMerchantId: this.getOption('gatewayMerchantId'),
					allowedAuthMethods: [YaPay.AllowedAuthMethod.PanOnly],
					allowedCardNetworks: this.getOption('cardNetworks') || [
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

			requiredFields: {

				billingContact: {
					email: this.getOption('useEmail') || false
				},

				shippingContact: {
					name: this.getOption('useName') || false,
					email: this.getOption('useEmail') || false,
					phone: this.getOption('usePhone') || false,
				},

				shippingTypes: {
					direct: true,
					pickup: true,
				},
			}
		}
	}

	createPayment(node, paymentData) {
		// Создать платеж.
		YaPay.createPayment(paymentData)
			.then( (payment) => {
				// Создать экземпляр кнопки.
				let button = payment.createButton({
					type: YaPay.ButtonType.Pay,
					theme: this.getOption('buttonTheme') || YaPay.ButtonTheme.Black,
					width: this.getOption('buttonWidth') || YaPay.ButtonWidth.Auto,
				});

				// Смонтировать кнопку в DOM.
				button.mount(node);

				// Подписаться на событие click.
				button.on(YaPay.ButtonEventType.Click, function onPaymentButtonClick() {
					// Запустить оплату после клика на кнопку.
					payment.checkout();
				});

				// Подписаться на событие process.
				payment.on(YaPay.PaymentEventType.Process, (event) => {
					// Получить платежный токен.
					console.log(event);

					this.orderAccept('orderAccept', event).then((result) => {
						//payment.update({shippingOptions: result})
					});

					//this.notify(payment, event);

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
				payment.on(YaPay.PaymentEventType.Abort, (event) => {
					// Предложить пользователю другой способ оплаты.
				});

				payment.on(YaPay.PaymentEventType.Change, (event) => {

					if (event.shippingAddress) {
						this.exampleDeliveryOptions('deliveryOptions', event.shippingAddress).then((result) => {
							payment.update({shippingOptions: result})
						});
					}

					if (event.shippingOption){
						payment.update({
							order: this.exampleOrderWithDirectShipping(event.shippingOption),
						});
					}

					if (event.pickupAddress) {
						this.exampleDeliveryOptions('pickupOptions', event.pickupAddress).then((result) => {
							payment.update({pickupOptions: result})
						});
					}

					if (event.pickupOption) {
						payment.update({
							order: this.exampleOrderWithPickupShipping(event.pickupOption),
						});
					}

				});
			})
			.catch(function (err) {
				// Платеж не создан.
				console.log({'payment not create': err});
			});
	}

	notify(payment, yandexPayData) {
		fetch(this.getOption('notifyUrl'), {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
			},
			body: JSON.stringify({
				service: this.getOption('requestSign'),
				accept: 'json',
				yandexData: yandexPayData,
				externalId: this.getOption('externalId'),
				paySystemId: this.getOption('paySystemId')
			})
		})
			.then(response => response.json())
			.then(result => {
				payment.complete(YaPay.CompleteReason.Success);

				if (result.success === true) {
					this.widget.go(result.state, result);
				} else {
					this.widget.go('error', result);
				}
			});
	}

	orderAccept(action, event){
		return fetch(this.getOption('purchaseUrl'), {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({
				siteId: this.getOption('siteId'),
				productId: this.getOption('productId') || null,
				fUserId: this.getOption('fUserId'),
				userId: this.getOption('userId') || null,
				yapayAction: action,
				address: event.shippingMethodInfo.shippingAddress,
				contact: event.shippingContact,
				delivery: event.shippingMethodInfo.shippingOption || event.shippingMethodInfo.pickupOptions
			})
		})
		.then(response => {return response.json()})
	}

	exampleDeliveryOptions(action, address){
		return fetch(this.getOption('purchaseUrl'), {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify({
				siteId: this.getOption('siteId'),
				productId: this.getOption('productId') || null,
				fUserId: this.getOption('fUserId'),
				userId: this.getOption('userId') || null,
				address: address,
				yapayAction: action
			})
		})
		.then(response => {return response.json()})
	}

	exampleOrderWithDirectShipping(shippingOption) {
		const { order } = this.paymentData;

		console.log(shippingOption);

		return {
			...order,
			items: [
				...order.items,
				{
					type: 'SHIPPING',
					label: shippingOption.label,
					amount: shippingOption.amount,
				},
			],
			total: {
				...order.total,
				amount: this.amountSum(order.total.amount, shippingOption.amount),
			},
		};
	}

	exampleOrderWithPickupShipping(pickupOption) {
		const { order } = this.paymentData;

		return {
			...order,
			items: [
				...order.items,
				{
					type: 'SHIPPING',
					label: pickupOption.label,
					amount: pickupOption.amount,
				},
			],
			total: {
				...order.total,
				amount: this.amountSum(order.total.amount, pickupOption.amount),
			},
		};
	}

	amountSum(amountA, amountB) {
		return (Number(amountA) + Number(amountB)).toFixed(2);
	}
}