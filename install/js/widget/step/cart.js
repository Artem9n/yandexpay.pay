import Template from '../utils/template';
import AbstractStep from './abstractstep';

const YaPay = window.YaPay;

export default class Cart extends AbstractStep {

	render(node, data) {
		this.paymentData = this.getPaymentData(data);
		this.defaultBody = this.getDefaultBody();

		this.setupPaymentCash();
		this.getProducts().then((result) => {
			this.combineOrderWithProducts(result);
			this.createPayment(node, this.paymentData);
		});
	}

	compile(data) {
		return Template.compile(this.options.template, data);
	}

	getDefaultBody() {
		return {
			siteId: this.getOption('siteId'),
			productId: this.getOption('productId'),
			fUserId: this.getOption('fUserId'),
			userId: this.getOption('userId'),
			setupId: this.getOption('setupId'),
			mode: this.getOption('mode')
		}
	}

	setupPaymentCash(){
		// Указываем возможность оплаты заказа при получении
		if (this.getOption('paymentCash') !== null) {
			this.paymentData.paymentMethods.push({
				type: YaPay.PaymentMethodType.Cash,
			});
		}
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
				total: { amount: data.total }
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
		YaPay.createPayment(paymentData, { agent: { name: "CMS-Bitrix", version: "1.0" } })
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
				button.on(YaPay.ButtonEventType.Click, () => {
					// Запустить оплату после клика на кнопку.
					payment.checkout();
				});

				// Подписаться на событие process.
				payment.on(YaPay.PaymentEventType.Process, (event) => {
					// Получить платежный токен.
					this.orderAccept(event).then((result) => {

						if(this.isPaymentTypeCash(event)) {
							payment.complete(YaPay.CompleteReason.Success);
							return;
						}

						this.notify(result, event).then(result => {
							if (result.success === true) {
								this.widget.go(result.state, result);
							} else {
								this.widget.go('error', result);
							}
						});

						payment.complete(YaPay.CompleteReason.Success);
					});

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
						this.getDeliveryOptions('deliveryOptions', event.shippingAddress).then((result) => {
							payment.update({shippingOptions: result})
						});
					}

					if (event.shippingOption){
						payment.update({
							order: this.combineOrderWithDirectShipping(event.shippingOption),
						});
					}

					if (event.pickupAddress) {
						this.getDeliveryOptions('pickupOptions', event.pickupAddress).then((result) => {
							payment.update({pickupOptions: result})
						});
					}

					if (event.pickupOption) {
						payment.update({
							order: this.combineOrderWithPickupShipping(event.pickupOption),
						});
					}

				});
			})
			.catch(function (err) {
				// Платеж не создан.
				console.log({'payment not create': err});
			});
	}

	isPaymentTypeCash(event) {
		return (event.paymentMethodInfo.type === 'CASH');
	}

	getProducts(){

		let expandData = {
			yapayAction: 'getProducts',
		};

		let data = {...this.defaultBody, ...expandData };

		return this.query(this.getOption('purchaseUrl'), data);
	}

	notify(payment, yandexPayData) {

		let data = {
			service: this.getOption('requestSign'),
			accept: 'json',
			yandexData: yandexPayData,
			externalId: payment.externalId,
		};

		return this.query(this.getOption('notifyUrl'), data);
	}

	orderAccept(event) {

		let expandData = {
			yapayAction: 'orderAccept',
			address: event.shippingMethodInfo.shippingAddress,
			contact: event.shippingContact,
			payment: event.paymentMethodInfo,
			delivery: event.shippingMethodInfo.shippingOption || event.shippingMethodInfo.pickupOptions,
			paySystemId: this.isPaymentTypeCash(event) ? this.getOption('paymentCash') : this.getOption('paySystemId'),
		};

		let data = {...this.defaultBody, ...expandData };

		return this.query(this.getOption('purchaseUrl'), data);
	}

	getDeliveryOptions(action, address) {

		let expandData = {
			address: address,
			yapayAction: action
		};

		let data = {...this.defaultBody, ...expandData };

		return this.query(this.getOption('purchaseUrl'), data);
	}

	combineOrderWithPickupShipping(shippingOption) {
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

	combineOrderWithDirectShipping(pickupOption) {
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

	combineOrderWithProducts(products) {
		const { order } = this.paymentData;

		let exampleOrder = {
			...order,
			items: products.items,
			total: {
				...order.total,
				amount: this.amountSum(0, products.amount),
			},
		};

		Object.assign(this.paymentData.order, exampleOrder);
	}

	amountSum(amountA, amountB) {
		return (Number(amountA) + Number(amountB)).toFixed(2);
	}
}