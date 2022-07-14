import Proxy from "./proxy";

export default class SiteProxy extends Proxy {

	bootstrap() {
		this.reflow();
	}

	getPaymentData() {
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

	getProducts(){

		let data = {
			yapayAction: 'getProducts',
			productId: this.getOption('productId'),
			mode: this.getOption('mode'),
			setupId: this.getOption('setupId')
		};

		return this.cart.query(this.getOption('purchaseUrl'), data);
	}

	getShippingOptions(address) {

		let data = {
			address: address,
			yapayAction: 'deliveryOptions',
			items: this.cart.paymentData.order.items,
			setupId: this.getOption('setupId'),
			paySystemId: this.getOption('paySystemId'),
		};

		return this.cart.query(this.getOption('purchaseUrl'), data);
	}

	getPickupOptions(bounds) {

		let data = {
			bounds: bounds,
			yapayAction: 'pickupOptions',
			items: this.cart.paymentData.order.items,
			setupId: this.getOption('setupId'),
			paySystemId: this.getOption('paySystemId'),
		};

		return this.cart.query(this.getOption('purchaseUrl'), data);
	}

	createPayment(node, paymentData) {
		if (this._mounted === true) { return; }

		YaPay.createPayment(paymentData, { agent: { name: "CMS-Bitrix", version: "1.0" } })
			.then((payment) => {
				this._mounted = true;

				this.cart.removeLoader();
				this.cart.mountButton(node, payment);

				payment.on(YaPay.PaymentEventType.Process, (event) => {

					this.orderAccept(event).then((result) => {

						if (result.error) { throw new Error(result.error.message, result.error.code); }

						if(!this.isPaymentTypeCash(event)) {
							this.notify(result, event).then(result => {
								if (result.success === true) {
									this.cart.widget.go(result.state, result);
									payment.complete(YaPay.CompleteReason.Success);
								} else {
									this.cart.widget.go('error', result);
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
							this.cart.showError('yapayProcess', '', error); // todo test it
							payment.complete(YaPay.CompleteReason.Error);
						});
				});

				payment.on(YaPay.PaymentEventType.Error, (event) => {
					this.cart.showError('yapayError', 'service temporary unavailable');
					payment.complete(YaPay.CompleteReason.Error);
				});

				payment.on(YaPay.PaymentEventType.Change, (event) => {
					// если выбрали адрес доставки, отдаем скисок доставок
					if (event.shippingAddress) {
						this.getShippingOptions(event.shippingAddress).then((result) => {
							payment.update({shippingOptions: result})
						});
					}

					// добавляем выбранную доставку
					if (event.shippingOption){
						payment.update({
							order: this.combineOrderWithDirectShipping(event.shippingOption),
						});
					}

					// если выбрали ПВЗ, отдаем список ПВЗ
					if (event.pickupBounds) {
						this.getPickupOptions(event.pickupBounds).then((result) => {
							payment.update({pickupPoints: result})
						});
					}

					if (event.pickupInfo) {
						this.getPickupDetail(event.pickupInfo.pickupPointId).then((result) => {
							payment.update({
								pickupPoint: result
							});
						});
					}

					// добавляем выбранный ПВЗ
					if (event.pickupPoint) {
						payment.update({
							order: this.combineOrderWithPickupShipping(event.pickupPoint),
						});
					}
				});
			})
			.catch((err) => {
				this.cart.showError('yapayPayment','payment not created', err);
			});
	}

	getPickupDetail(pickupId) {
		let data = {
			pickupId: pickupId,
			yapayAction: 'pickupDetail',
			items: this.cart.paymentData.order.items,
			setupId: this.getOption('setupId'),
			paySystemId: this.getOption('paySystemId'),
		};

		return this.cart.query(this.getOption('purchaseUrl'), data);
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
			items: this.cart.paymentData.order.items,
			payment: event.paymentMethodInfo,
			contact: event.shippingContact,
			yapayAction: 'orderAccept',
			deliveryType: deliveryType,
			paySystemId: this.isPaymentTypeCash(event) ? this.getOption('paymentCash') : this.getOption('paySystemId'),
			orderAmount: event.orderAmount
		};

		let data = {...orderData, ...delivery };

		return this.cart.query(this.getOption('purchaseUrl'), data);
	}

	isPaymentTypeCash(event) {
		return (event.paymentMethodInfo.type === 'CASH');
	}

	notify(payment, yandexPayData) {

		let data = {
			service: this.getOption('requestSign'),
			accept: 'json',
			yandexData: yandexPayData,
			externalId: payment.externalId,
			paySystemId: payment.paySystemId
		};

		return this.cart.query(this.getOption('notifyUrl'), data);
	}

	changeOffer(newProductId) {
		let productId = this.getOption('productId');

		if (productId !== newProductId) { // todo in items
			this.cart.widget.setOptions({productId: newProductId});
			this.reflow();
		}
	}

	changeBasket() {
		this.reflow();
	}

	reflow() {
		this.getProducts()
			.then((result) => {
				if (result.error) { throw new Error(result.error.message); }

				this.combineOrderWithProducts(result);
				this.createPayment(this.cart.element, this.cart.paymentData);
			})
			.catch((error) => {
				this.cart.removeLoader();
				// todo this.showError();
			});
	}

	combineOrderWithPickupShipping(pickupOption) {
		const { order } = this.cart.paymentData;

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
				amount: this.cart.amountSum(order.total.amount, pickupOption.amount),
			},
		};
	}

	combineOrderWithDirectShipping(shippingOption) {
		const { order } = this.cart.paymentData;

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
				amount: this.cart.amountSum(order.total.amount, shippingOption.amount),
			},
		};
	}

	combineOrderWithProducts(products) {
		const { order } = this.cart.paymentData;

		let exampleOrder = {
			...order,
			items: products.items,
			total: {
				amount: products.amount,
			},
		};

		Object.assign(this.cart.paymentData.order, exampleOrder);
	}

	restoreButton(node) {
		if (this.cart.paymentButton == null) {
			this.cart.insertLoader();
			return;
		}

		//this.removeLoader();
		this.cart.paymentButton.mount(node);
	}

	setupPaymentCash(){
		// Указываем возможность оплаты заказа при получении
		if (this.getOption('paymentCash') == null) { return; }

		this.cart.paymentData.paymentMethods.push({
			type: YaPay.PaymentMethodType.Cash,
		});
	}
}