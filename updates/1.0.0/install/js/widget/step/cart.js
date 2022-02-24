import Template from '../utils/template';
import AbstractStep from './abstractstep';
import { ready } from "../utils/ready";

const YaPay = window.YaPay;

export default class Cart extends AbstractStep {

	static defaults = {
		loaderTemplate: '<div class="bx-yapay-skeleton-loading width--#WIDTH#"></div>',
		loaderSelector: '.bx-yapay-skeleton-loading',
	}

	render(node, data) {
		this.isBootstrap = false;
		this.element = node;
		this.paymentData = this.getPaymentData(data);
		this.paymentButton = null;

		this.bootSolution();
		this.insertLoader();
		this.setupPaymentCash();
		this.delayBootstrap();
	}

	compile(data) {
		return Template.compile(this.options.template, data);
	}

	restore(node) {
		this.element = node;
		this.restoreButton(node);
	}

	bootSolution() {
		const solution = this.widget.getSolution();

		if (solution == null) { return; }

		solution.bootCart(this);
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

				if (result.error) { throw new Error(result.error.message); }

				this.combineOrderWithProducts(result);
				this.createPayment(this.element, this.paymentData);

			})
			.catch((error) => {
				//this.showError('bootstrap', '', error);
			});
	}

	changeOffer(newProductId) {

		if (!this.isBootstrap) { return; }

		let productId = this.getOption('productId');

		if (productId !== newProductId) { // todo in items
			this.widget.setOptions({productId: newProductId});
			this.getProducts().then((result) => {
				this.combineOrderWithProducts(result);
			});
		}
	}

	setupPaymentCash(){
		// Указываем возможность оплаты заказа при получении
		if (this.getOption('paymentCash') == null) { return; }

		this.paymentData.paymentMethods.push({
			type: YaPay.PaymentMethodType.Cash,
		});
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
				this.removeLoader();
				this.mountButton(node, payment);

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

							if (result.redirect != null) {
								window.location.href = result.redirect;
							}
						}
					})
					.catch((error) => {
						this.showError('yapayProcess', '', error); // todo test it
						payment.complete(YaPay.CompleteReason.Error);
					});

				});

				// Подписаться на событие error.
				payment.on(YaPay.PaymentEventType.Error, (event) => {
					this.showError('yapayError', 'service temporary unavailable');
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
				this.showError('yapayPayment','payment not created', err);
			});
	}

	mountButton(node, payment) {
		this.paymentButton = payment.createButton({
			type: YaPay.ButtonType.Checkout,
			theme: this.getOption('buttonTheme') || YaPay.ButtonTheme.Black,
			width: this.getOption('buttonWidth') || YaPay.ButtonWidth.Auto,
		});

		this.paymentButton.mount(this.element);

		this.paymentButton.on(YaPay.ButtonEventType.Click, () => {
			payment.checkout();
		});
	}

	restoreButton(node) {
		if (this.paymentButton == null) {
			this.insertLoader();
			return;
		}

		//this.removeLoader();
		this.paymentButton.mount(node);
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

	showError(type, message, err = null) {
		let notify = type + ' - ' + message;

		if (err) {
			notify += ' ' + err;
		}

		alert(notify);
	}

	insertLoader() {
		const width = this.getOption('buttonWidth') || YaPay.ButtonWidth.Auto;

		this.element.innerHTML = Template.compile(this.getOption('loaderTemplate'), {
			width: width.toLowerCase(),
			label: this.getOption('label'),
		});
	}

	removeLoader() {
		const loader = this.element.querySelector(this.getOption('loaderSelector'));

		if (loader == null) { return; }

		loader.remove();
	}
}