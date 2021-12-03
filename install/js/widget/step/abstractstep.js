import Template from '../utils/template';

export default class AbstractStep {

	static optionSection = null;

	static defaults = {
		template: '',
	}

	/**
	 * @param {Object} options
	 */
	constructor(options = {}) {
		this.options = Object.assign({}, this.constructor.defaults, options);
		this.widget = null;
	}

	/**
	 *
	 * @param {Widget} widget
	 */
	setWidget(widget) {
		this.widget = widget;
	}

	/**
	 *
	 * @param {string} key
	 * @returns {*}
	 */
	getOption(key) {
		const section = this.constructor.optionSection;

		/*if (this.widget.options[section] !== null && this.widget.options[section][key]) {
			return this.widget.options[section][key];
		} else */if (key in this.options) {
			return this.options[key];
		} else {
			return this.widget.options[key];
		}
	}

	setOption(key, value) {
		if (key in this.options) {
			this.options[key] = value;
		} else if (key in this.widget.options){
			this.widget.options[key] = value;
		}
	}

	/**
	 * @param {Object<Element>} node Element
	 * @param {Object} data Options
	 */
	render(node, data = {}) {
		node.innerHTML = this.compile(data);
	}

	/**
	 * @param {Object} data
	 * @returns {string}
	 */
	compile(data) {
		return Template.compile(this.options.template, data);
	}

	/**
	 * @param {string} url
	 * @param {Object} data
	 * @returns {Promise.<Object>}
	 */
	query(url, data) {
		return fetch(url, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify(data)
		})
			.then(response => {return response.json()})
	}

	getTemplate(key) {
		let optionKey = key + 'Template';
		let option = this.options[optionKey];
		let optionFirstSymbol = option.substr(0, 1);
		let result;

		if (optionFirstSymbol === '.' || optionFirstSymbol === '#') {
			result = this.getNode(option).innerHTML;
		} else {
			result = option;
		}

		return result;
	}

	getElement(key, context, method) {
		let selector = this.getElementSelector(key);

		return this.getNode(selector, context, method || 'querySelector');
	}

	getElementSelector(key) {
		let optionKey = key + 'Element';

		return this.options[optionKey];
	}

	getNode(selector, context, method) {
		let result;

		if (selector.substr(0, 1) === '#') { // is id query
			context = document;
		} else if (!context) {
			context = this.el;
		}

		return context[method](selector);
	}

}