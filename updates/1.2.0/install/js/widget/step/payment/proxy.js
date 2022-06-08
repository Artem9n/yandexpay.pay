export default class Proxy {

	constructor(order) {
		this.order = order;
	}

	getOption(name) {
		return this.order.getOption(name);
	}

	createPayment(node, paymentData) {

	}

	getPaymentData(data) {

	}
}