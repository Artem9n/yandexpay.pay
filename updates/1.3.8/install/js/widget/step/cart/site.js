import Proxy from "./proxy";

export default class Site extends Proxy {

	bootstrap() {
		this.reflow();
	}

	getPaymentData() {
		let paymentData = {
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

		if (this.getOption('paymentCash') != null) {
			paymentData.paymentMethods.push({
				type: YaPay.PaymentMethodType.Cash,
			});
		}

		return paymentData;
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
			items: this.paymentData.order.items,
			setupId: this.getOption('setupId'),
			paySystemId: this.getOption('paySystemId'),
		};

		return this.cart.query(this.getOption('purchaseUrl'), data);
	}

	getPickupOptions(bounds) {

		let data = {
			bounds: bounds,
			yapayAction: 'pickupOptions',
			items: this.paymentData.order.items,
			setupId: this.getOption('setupId'),
			paySystemId: this.getOption('paySystemId'),
		};

		return this.cart.query(this.getOption('purchaseUrl'), data);
	}

	createPayment(node, paymentData) {
		if (this._mounted != null) { return; }

		this._mounted = false;

		YaPay.createPayment(paymentData, { agent: { name: "CMS-Bitrix", version: "1.0" } })
			.then((payment) => {
				this._mounted = true;

				this.widget.removeLoader();
				this.mountButton(node, payment);

				payment.on(YaPay.PaymentEventType.Process, (event) => {

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
							this.cart.showError('yapayProcess', '', error); // todo test it
							payment.complete(YaPay.CompleteReason.Error);
						});
				});

				payment.on(YaPay.PaymentEventType.Error, (event) => {
					this.cart.showError('yapayError', 'service temporary unavailable');
					payment.complete(YaPay.CompleteReason.Error);
				});

				payment.on(YaPay.PaymentEventType.Change, (event) => {
					// ���� ������� ����� ��������, ������ ������ ��������
					if (event.shippingAddress) {
						this.getShippingOptions(event.shippingAddress).then((result) => {
							payment.update({shippingOptions: result})
						});
					}

					// ��������� ��������� ��������
					if (event.shippingOption){
						payment.update({
							order: this.combineOrderWithDirectShipping(event.shippingOption),
						});
					}

					// ���� ������� ���, ������ ������ ���
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

					// ��������� ��������� ���
					if (event.pickupPoint) {
						payment.update({
							order: this.combineOrderWithPickupShipping(event.pickupPoint),
						});
					}
				});
			})
			.catch((err) => {
				this._mounted = null;
				this.cart.showError('yapayPayment','payment not created', err);
			});
	}

	mountButton(node, payment) {
		this.cart.initialContent = null;

		const theme = this.cart.display.getOption('VARIANT_BUTTON') || YaPay.ButtonTheme.Black;
		const width = this.cart.display.getOption('WIDTH_BUTTON') || YaPay.ButtonWidth.Auto;

		this.paymentButton = payment.createButton({
			type: YaPay.ButtonType.Checkout,
			theme: theme,
			width: width,
		});

		this.paymentButton.mount(this.cart.element);

		this.paymentButton.on(YaPay.ButtonEventType.Click, () => {
			payment.checkout();
		});
	}

	getPickupDetail(pickupId) {
		let data = {
			pickupId: pickupId,
			yapayAction: 'pickupDetail',
			items: this.paymentData.order.items,
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
			items: this.paymentData.order.items,
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
			this.widget.setOptions({productId: newProductId});
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
				this.createPayment(this.cart.element, this.paymentData);
			})
			.catch((error) => {
				this.widget.removeLoader();
				// todo this.showError();
			});
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

	restore(node) {
		if (this.paymentButton == null) {
			return;
		}

		this.paymentButton.mount(node);
	}

	amountSum(amountA, amountB) {
		return (Number(amountA) + Number(amountB)).toFixed(2);
	}
}