import Template from '../../utils/template';
import AbstractStep from '../abstractstep';
import RestProxy from './rest';
import SiteProxy from './site';

export default class AbstractPayment extends AbstractStep {

	static defaults = {
		template: '<div class="alert alert-success" role="alert"><strong>#MESSAGE#</strong></div>',
		loaderSelector: '.bx-yapay-skeleton-loading',
	}

	render(node, data) {
		this.element = node;
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

	removeLoader() {
		const loader = this.element.querySelector(this.getOption('loaderSelector'));

		if (loader == null) { return; }

		loader.remove();
	}
}