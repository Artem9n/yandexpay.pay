export default class Proxy {

	paymentData;
	cart;
	widget;

	constructor(cart) {
		this.cart = cart;
		this.widget = cart.widget;
		this.paymentData = this.getPaymentData();
	}

	getOption(name) {
		return this.cart.getOption(name);
	}

	bootstrap() {

	}

	createPayment(node, paymentData) {

	}

	getPaymentData() {

	}

	restore(node) {

	}

	mount(node, payment) {

	}
}