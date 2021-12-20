import Template from '../utils/template';
import AbstractStep from './abstractstep';

const YaPay = window.YaPay;

export default class Cart extends AbstractStep {

	render(node, data) {
		this.element = node;
		this.paymentData = this.getPaymentData(data);
		this.defaultBody = this.getDefaultBody();

		this.setupPaymentCash();

		this.getProducts().then((result) => {
			this.combineOrderWithProducts(result);
			this.createPayment(this.element, this.paymentData);
		});

		this.catalogElementChangeOffer();
		this.basketChange();
	}

	compile(data) {
		return Template.compile(this.options.template, data);
	}

	getDefaultBody() {
		return {
			siteId: this.getOption('siteId'),
			fUserId: this.getOption('fUserId'),
			userId: this.getOption('userId'),
			setupId: this.getOption('setupId'),
			mode: this.getOption('mode')
		}
	}

	catalogElementChangeOffer() {

		if (!BX) { return; }

		if (!window.JCCatalogElement) { return; }

		BX.addCustomEvent('onCatalogElementChangeOffer', (eventData) => {
			this.setOption('productId', eventData.newId);
			this.getProducts().then((result) => {
				this.combineOrderWithProducts(result);
			});
		});
	}

	basketChange() {

		if (!BX) { return; }

		BX.addCustomEvent('OnBasketChange', () => {
			this.getProducts().then((result) => {
				this.combineOrderWithProducts(result);
			});
		});
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

						if(!this.isPaymentTypeCash(event)) {
							this.notify(result, event).then(result => {
								if (result.success === true) {
									this.widget.go(result.state, result);
								} else {
									this.widget.go('error', result);
								}

								payment.complete(YaPay.CompleteReason.Success);
							});
						} else {
							payment.complete(YaPay.CompleteReason.Success);
						}
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

				// Подписаться на событие setup.
				payment.on(YaPay.PaymentEventType.Setup, (event) => {
					// Передаем данные для инициализации формы
					if (event.pickupPoints) {

						this.getPickupOptions(event.pickupBounds).then((result) => {
							payment.setup({pickupPoints: result})
						});
					}
				});

				// Подписаться на событие change.
				payment.on(YaPay.PaymentEventType.Change, (event) => {

					if (event.shippingAddress) {
						this.getShippingOptions(event.shippingAddress).then((result) => {
							payment.update({shippingOptions: result})
						});
					}

					if (event.shippingOption){
						payment.update({
							order: this.combineOrderWithDirectShipping(event.shippingOption),
						});
					}

					if (event.pickupBounds) {
						this.getPickupOptions(event.pickupBounds).then((result) => {
							payment.update({pickupPoints: result})
						});
					}

					if (event.pickupPoint) {
						payment.update({
							order: this.combineOrderWithPickupShipping(event.pickupPoint),
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
			productId: this.getOption('productId'),
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
			paySystemId: payment.paySystemId
		};

		return this.query(this.getOption('notifyUrl'), data);
	}

	orderAccept(event) {

		let deliveryType = event.shippingMethodInfo.shippingOption ? 'delivery' : 'pickup';
		let delivery;

		if (deliveryType === 'pickup') {
			delivery = {
				address: event.shippingMethodInfo.pickupPoint.address,
				pickup: event.shippingMethodInfo.pickupPoint,
			}
		}
		else {
			delivery = {
				address: event.shippingMethodInfo.shippingAddress,
				delivery: event.shippingMethodInfo.shippingOption,
			}
		}

		let orderData = {
			items: this.paymentData.order.items,
			payment: event.paymentMethodInfo,
			contact: event.shippingContact,
			yapayAction: 'orderAccept',
			productId: this.getOption('productId'),
			deliveryType: deliveryType,
			paySystemId: this.isPaymentTypeCash(event) ? this.getOption('paymentCash') : this.getOption('paySystemId'),
			orderAmount: event.orderAmount
		};

		let data = {...this.defaultBody, ...orderData, ...delivery };

		console.log(event);
		console.log(data);

		return this.query(this.getOption('purchaseUrl'), data);
	}

	getShippingOptions(address) {

		let expandData = {
			address: address,
			yapayAction: 'deliveryOptions',
			productId: this.getOption('productId'),
			deliveryType: 'delivery'
		};

		let data = {...this.defaultBody, ...expandData };

		return this.query(this.getOption('purchaseUrl'), data);
	}

	getPickupOptions(bounds) {

		let expandData = {
			bounds: bounds,
			yapayAction: 'pickupOptions',
			productId: this.getOption('productId'),
			deliveryType: 'pickup'
		};

		let data = {...this.defaultBody, ...expandData };

		return this.query(this.getOption('purchaseUrl'), data);
	}

	combineOrderWithPickupShipping(pickupOption) {
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

	combineOrderWithDirectShipping(shippingOption) {
		const { order } = this.paymentData;

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