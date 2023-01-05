import AbstractStep from '../abstractstep';
import Rest from "./rest";
import Site from "./site";

export default class Payment extends AbstractStep {
	render(node, data) {
		this.element = node;
		this.bootProxy();

		const paymentData = this.getPaymentData(data);
		this.createPayment(node, paymentData);
	}

	bootProxy() : RestProxy|SiteProxy {
		this.proxy = this.isRest() ? new Rest(this) : new Site(this);
	}

	getPaymentData(data) {
		return this.proxy.getPaymentData(data);
	}

	createPayment(node, paymentData) {
		this.proxy.createPayment(node, paymentData);
	}
}