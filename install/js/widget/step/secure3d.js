import Template from '../utils/template';
import AbstractStep from "./abstractstep";

export default class Step3ds extends AbstractStep {

	static defaults = {
		url: '/yandex_pay.php',

		template: '<form name="form" action="#ACTION#" method="post">'
			+ '<input type="hidden" name="TermUrl" value="#TERMURL#" >'
			+ '<input type="hidden" name="MD" value="#MD#" >'
			+ '<input type="hidden" name="PaReq" value="#PAREQ#" >'
			+ '</form>',
	}

	render(node, data) {
		super.render(node, data);
		this.autosubmit(node);
	}

	compile(data) {
		const template = this.options.template;
		const vars = Object.assign(data, {
			'TermUrl': this.makeTermUrl(),
		});

		return Template.compile(template, vars);
	}

	makeTermUrl() {
		let result = this.getOption('YANDEX_PAY_NOTIFY_URL');
		let backUrl = window.location.href;

		result +=
			(result.indexOf('?') === -1 ? '?' : '&')
			+ 'backurl=' + encodeURIComponent(backUrl)
			+ '&service=' + this.getOption('requestSign')
			+ '&paymentId=' + this.getOption('externalId');

		return result;
	}

	autosubmit(node) {
		const form = node.querySelector('form')

		form.submit();
	}
}