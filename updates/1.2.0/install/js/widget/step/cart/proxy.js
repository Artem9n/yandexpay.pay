export default class Proxy {

	constructor(cart) {
		this.cart = cart;
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
}