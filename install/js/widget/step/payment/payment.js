import AbstractStep from '../abstractstep';
import RestProxy from "../payment/rest";
import SiteProxy from "../payment/site";

export default class Payment extends AbstractStep {
	render(node, data) {
		this.element = node;
		this.bootProxy();

		const paymentData = this.getPaymentData(data);
		this.createPayment(node, paymentData);
	}

	bootProxy() : RestProxy|SiteProxy {
		this.proxy = this.isRest() ? new RestProxy(this) : new SiteProxy(this);
	}

	getPaymentData(data) {
		return this.proxy.getPaymentData(data);
	}

	createPayment(node, paymentData) {
		this.proxy.createPayment(node, paymentData);
	}
}