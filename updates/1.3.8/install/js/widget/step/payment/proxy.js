export default class Proxy {

	payment;
	widget;

	constructor(payment) {
		this.payment = payment;
		this.widget = payment.widget;
	}

	getOption(name) {
		return this.payment.getOption(name);
	}

	createPayment(node, paymentData) {

	}

	getPaymentData(data) {

	}
}