import Proxy from "./proxy";

export default class RestProxy extends Proxy {

	bootstrap() {
		this.reflow();
	}

	getButtonData() {

		let data = {
			productId: this.getOption('productId'),
			mode: this.getOption('mode'),
			currencyCode: this.getOption('currencyCode'),
			setupId: this.getOption('setupId'),
		};

		return this.cart.query(this.getOption('restUrl') + 'button/data', data);
	}

	getPaymentData() {
		return {
			env: this.getOption('env'),
			version: 3,
			merchantId: this.getOption('merchantId'),
			cart: { externalId: "checkout-b2b-test-order-id", },
			currencyCode: this.getOption('currencyCode'),
		}
	}

	createPayment(node, paymentData) {
		if (this._mounted != null) { return; }

		this._mounted = false;

		YaPay.createCheckout(paymentData, { agent: { name: "CMS-Bitrix", version: "1.0" } })
			.then((payment) => {
				this._mounted = true;

				this.cart.removeLoader();
				this.mountButton(node, payment);

				payment.on(YaPay.CheckoutEventType.Success, (event) => {

					this.authorize(event)
						.then((result) => {
							if (result.status === 'success') {
								setTimeout(function() {
									window.location.href = result.data.redirect;
								}, 1000);
							}
							else {
								this.cart.showError('authorize', result.reasonCode, result.reason);
							}
						});

					payment.complete(YaPay.CompleteReason.Success);
					console.log("Process", event);
				});

				payment.on(YaPay.CheckoutEventType.Error, (event) => {
					console.log("Process", event);
				});
			})
			.catch((err) => {
				this._mounted = null;
				node.remove();
				this.cart.showError('yapayPayment','payment not created', err);
			});
	}

	authorize(event) {
		let data = {
			orderId: event.orderId,
			hash: event.metadata,
			successUrl: this.getOption('successUrl'),
		};

		return this.cart.query(this.getOption('restUrl') + 'authorize', data);
	}

	bindDebug(payment) {
		for (const key in YaPay.CheckoutEventType) {
			if (!YaPay.CheckoutEventType.hasOwnProperty(key)) { continue; }

			payment.on(YaPay.CheckoutEventType[key], function() {
				console.log(arguments);
			});
		}
	}

	mountButton(node, payment) {
		this.payment = payment;
		this.cart.display.mount(this.cart.element, payment, YaPay.ButtonType.Checkout);
	}

	restoreButton(node) {
		if (this.payment == null) {
			return;
		}

		this.cart.display.mount(node, this.payment, YaPay.ButtonType.Checkout);
	}

	combineOrderWithData(data) {
		const { cart } = this.cart.paymentData;

		let exampleData = {
			cart: {
				...cart,
				items: data.items,
				total: {
					amount: data.total.amount,
				},
			},
			metadata: data.metadata
		};

		Object.assign(this.cart.paymentData, exampleData);
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

	setupPaymentCash() {

	}

	reflow() {
		this.getButtonData()
			.then((result) => {
				if (result.status === 'fail') { throw new Error(result.reason); }

				this.combineOrderWithData(result.data);
				this.createPayment(this.cart.element, this.cart.paymentData);
				this.payment?.update(this.cart.paymentData);
			})
			.catch((error) => {
				this.cart.removeLoader();
			});
	}
}