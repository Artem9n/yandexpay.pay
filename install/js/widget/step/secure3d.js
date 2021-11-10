import Template from '../utils/template';
import AbstractStep from "./abstractstep";

export default class Step3ds extends AbstractStep {

	static defaults = {
		template: '<form name="form" action="#ACTION#" method="#METHOD#">'
			+ '#INPUTS#'
			+ '</form>',
	}

	render(node, data) {
		super.render(node, data);
		this.autosubmit(node);
	}

	compile(data) {

		const template = this.options.template;
		const vars = Object.assign(data, {
			'inputs': this.makeInputs(data)
		});

		return Template.compile(template, vars);
	}

	/**
	 * @param {{url:string, termUrl:boolean, params:[], method:string}} data
	 * @returns {string}
	 */
	makeInputs(data) {
		let key;
		let vars = data.params;
		let value;
		let template;

		if (Object.keys(vars).length === 0) { return ''; }

		template = data.termUrl ? '<input type="hidden" name="TermUrl" value="' + this.makeTermUrl() + '">' : '';

		for (key in vars)
		{
			if (!vars.hasOwnProperty(key)) { continue; }

			value = vars[key];

			template += '<input type="hidden" name="' + key + '" value="' + value + '">';
		}

		return template;
	}

	/**
	 * @returns {string}
	 */
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

	/**
	 * @param {Object<Element>} node
	 */
	autosubmit(node) {
		const form = node.querySelector('form');

		form.submit();
	}
}