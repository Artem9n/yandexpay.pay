import Proxy from "./proxy";
import {EventProxy} from "../../utils/eventproxy";

export default class Rest extends Proxy {

	bootstrap() {
		this.getButtonData()
			.then((result) => {
				if (result.status === 'fail') { throw new Error(result.reason); }
				this.combineOrderWithData(result.data);
				this.createPayment(this.cart.element, this.paymentData);
			})
			.catch((error) => {
				this.widget.removeLoader();
			});
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

	onPaymentSuccess(event) {
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

		EventProxy.make().fire('bxYapayPaymentSuccess', event);
	}

	onPaymentAbort(event) {
		EventProxy.make().fire('bxYapayPaymentAbort', event);
	}

	onPaymentError(event) {
		EventProxy.make().fire('bxYapayPaymentError', event);
		this.cart.showError('yapayPayment','payment not created', event.reason);
	}

	createPayment(node, paymentData) {
		if (this._mounted != null) { return; }

		this._mounted = false;

		YaPay.createSession(paymentData, {
			onSuccess: this.onPaymentSuccess.bind(this),
			onAbort: this.onPaymentAbort.bind(this),
			onError: this.onPaymentError.bind(this),
			agent: { name: "CMS-Bitrix", version: "1.0" }
		})
			.then((paymentSession) => {
				this._mounted = true;
				this.widget.removeLoader();
				this.mount(paymentSession);
			})
			.catch((err) => {
				this._mounted = null;
				node.remove();
				this.cart.showError('yapayPayment', 'payment not created', err);
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

	mount(payment) {
		this.cart.initialContent = null;
		this.payment = payment;
		this.cart.display.mount(this.cart.element, payment, YaPay.ButtonType.Checkout);

		EventProxy.make().fire('bxYapayMountButton');
	}

	restore(node) {
		if (this.payment == null) {
			return;
		}

		this.cart.display.mount(node, this.payment, YaPay.ButtonType.Checkout);
		EventProxy.make().fire('bxYapayRestoreButton');
	}

	combineOrderWithData(data) {
		const { cart } = this.paymentData;

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

		Object.assign(this.paymentData, exampleData);
	}

	changeOffer(newProductId) {
		let productId = this.getOption('productId');

		if (productId !== newProductId) { // todo in items
			this.widget.setOptions({productId: newProductId});
			this.update();
		}
	}

	changeBasket() {
		this.update();
	}

	update() {
		this.payment.update(async () => {
			const result = await this.getButtonData()
				.then(result => result)
				.catch((error) => {
					this.cart.showError('getButtonData','get not button data', error);
				});

			if (result.status === 'fail') { throw new Error(result.reason); }

			return {
				cart: {
					items: result.data.items
				},
				total: {
					amount: result.data.total.amount,
				},
				metadata: result.data.metadata,
			}
		});
	}
}