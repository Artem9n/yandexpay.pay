import Template from '../../utils/template';
import AbstractStep from '../abstractstep';
import RestProxy from './rest';
import SiteProxy from './site';

const YaPay = window.YaPay;

export default class AbstractPayment extends AbstractStep {

	static defaults = {
		template: '<div class="alert alert-success" role="alert"><strong>#MESSAGE#</strong></div>'
	}

	render(node, data) {
		this.proxy = this.getOption('isRest')
			? new RestProxy(this)
			: new SiteProxy(this);

		const paymentData = this.getPaymentData(data);

		this.createPayment(node, paymentData);
	}

	compile(data) {
		return Template.compile(this.options.template, data);
	}

	getPaymentData(data) {
		return this.proxy.getPaymentData(data);
	}

	createPayment(node, paymentData) {
		this.proxy.createPayment(node, paymentData);
	}
}