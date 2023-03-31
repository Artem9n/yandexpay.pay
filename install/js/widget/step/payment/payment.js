import AbstractStep from '../abstractstep';
import Rest from "./rest";
import Site from "./site";
import Display from "../../ui/display/factory";

export default class Payment extends AbstractStep {

	display;

	render(node, data) {
		this.element = node;
		this.display = this.getDisplay();

		this.bootProxy();

		const paymentData = this.getPaymentData(data);
		this.createPayment(node, paymentData);
	}

	bootProxy() : Rest|Site {
		this.proxy = this.isRest() ? new Rest(this) : new Site(this);
	}

	getPaymentData(data) {
		return this.proxy.getPaymentData(data);
	}

	createPayment(node, paymentData) {
		this.proxy.createPayment(node, paymentData);
	}

	getDisplay() {
		const type = this.getOption('displayType');
		const options = this.getOption('displayParameters');

		return Display.make(type, this, options);
	}
}