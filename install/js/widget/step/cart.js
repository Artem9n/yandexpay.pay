import Template from '../utils/template';
import AbstractStep from './abstractstep';
import { ready } from "../utils/ready";

const YaPay = window.YaPay;

export default class Cart extends AbstractStep {

	render(node, data) {
		this.isBootstrap = false;
		this.element = node;
		this.paymentData = this.getPaymentData(data);

		this.setupPaymentCash();

		this.delayBootstrap();
		this.catalogElementChangeOffer();
		this.basketChange();
	}

	compile(data) {
		return Template.compile(this.options.template, data);
	}

	catalogElementChangeOffer() {
		if (typeof BX === 'undefined' || typeof JCCatalogElement === 'undefined') { return; }

		BX.addCustomEvent('onCatalogElementChangeOffer', (eventData) => {
			let newProductId = parseInt(eventData.newId, 10);

			if (isNaN(newProductId)) { return; }

			this.delayChangeOffer(newProductId);
		});
	}

	delayChangeOffer(productId) {
		this.delay('changeOffer', [productId]);
	}

	delayBootstrap() {
		ready(() => {
			this.delay('bootstrap');
		});
	}

	bootstrap() {
		this.isBootstrap = true;

		this.getProducts()
			.then((result) => {

				if (result.error) { throw new Error(result.error.message, result.error.code); }

				this.combineOrderWithProducts(result);
				this.createPayment(this.element, this.paymentData);

			})
			.catch((error) => {
				this.showError('', error);
			});
	}

	changeOffer(newProductId) {

		if (!this.isBootstrap) { return; }

		let productId = this.getOption('productId');

		if (productId !== newProductId) { // todo in items
			this.setOption('productId', newProductId);
			this.getProducts().then((result) => {
				this.combineOrderWithProducts(result);
			});
		}
	}

	basketChange() {
		if (typeof BX === 'undefined') { return; }

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
			order: { id: '0' },
			paymentMethods: [
				{
					type: YaPay.PaymentMethodType.Card,
					gateway: this.getOption('gateway'),
					gatewayMerchantId: this.getOption('gatewayMerchantId'),
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
			.then((payment) => {
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

						if (result.error) { throw new Error(result.error.message, result.error.code); }

						if(!this.isPaymentTypeCash(event)) {
							this.notify(result, event).then(result => {
								if (result.success === true) {
									this.widget.go(result.state, result);
									payment.complete(YaPay.CompleteReason.Success);
								} else {
									this.widget.go('error', result);
									payment.complete(YaPay.CompleteReason.Error);
								}
							});
						} else {

							payment.complete(YaPay.CompleteReason.Success);

							if (result.redirect !== null) {
								window.location.href = result.redirect;
							}
						}
					})
					.catch((error) => {
						this.showError('', error); // todo test it
						payment.complete(YaPay.CompleteReason.Error);
					});

				});

				// Подписаться на событие error.
				payment.on(YaPay.PaymentEventType.Error, (event) => {
					this.showError('service temporary unavailable');
					payment.complete(YaPay.CompleteReason.Error);
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
			.catch((err) => {
				this.showError('payment not created', err);
			});
	}

	isPaymentTypeCash(event) {
		return (event.paymentMethodInfo.type === 'CASH');
	}

	getProducts(){

		let data = {
			yapayAction: 'getProducts',
			productId: this.getOption('productId'),
			mode: this.getOption('mode'),
			setupId: this.getOption('setupId')
		};

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
			setupId: this.getOption('setupId'),
			items: this.paymentData.order.items,
			payment: event.paymentMethodInfo,
			contact: event.shippingContact,
			yapayAction: 'orderAccept',
			deliveryType: deliveryType,
			paySystemId: this.isPaymentTypeCash(event) ? this.getOption('paymentCash') : this.getOption('paySystemId'),
			orderAmount: event.orderAmount
		};

		let data = {...orderData, ...delivery };

		return this.query(this.getOption('purchaseUrl'), data);
	}

	getShippingOptions(address) {

		let data = {
			address: address,
			yapayAction: 'deliveryOptions',
			items: this.paymentData.order.items,
			setupId: this.getOption('setupId'),
		};

		return this.query(this.getOption('purchaseUrl'), data);
	}

	getPickupOptions(bounds) {

		let data = {
			bounds: bounds,
			yapayAction: 'pickupOptions',
			items: this.paymentData.order.items,
			setupId: this.getOption('setupId'),
		};

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
				amount: products.amount,
			},
		};

		Object.assign(this.paymentData.order, exampleOrder);
	}

	amountSum(amountA, amountB) {
		return (Number(amountA) + Number(amountB)).toFixed(2);
	}

	showError(message, err = null) {
		let notify = message;

		if (err) {
			notify += ' ' + err;
		}

		alert(notify);
	}
}