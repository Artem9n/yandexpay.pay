import Template from '../utils/template';

export default class AbstractStep {

	static defaults = {
		template: '',
	}

	defaults;
	options;
	widget;
	delayTimeouts = {};

	/**
	 * @param {Widget} widget
	 * @param {Object} options
	 */
	constructor(widget, options = {}) {
		this.widget = widget;
		this.defaults = Object.assign({}, this.constructor.defaults);
		this.options = Object.assign({}, options);
	}

	/**
	 *
	 * @param {string} name
	 * @returns {*}
	 */
	getOption(name) {
		return this.options[name] ?? this.widget.getOption(name) ?? this.defaults[name];
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
		return Template.compile(this.getOption('template'), data);
	}

	/**
	 * @param {Object<Element>} node
	 */
	restore(node) {
		// nothing by default
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
		if (selector.substr(0, 1) === '#') { // is id query
			context = document;
		} else if (!context) {
			context = this.el;
		}

		return context[method](selector);
	}

	clearDelay(name) {
		if (this.delayTimeouts[name] == null) { return; }

		clearTimeout(this.delayTimeouts[name]);
		this.delayTimeouts[name] = null;
	}

	delay(name, args = [], timeout = 300) {
		this.clearDelay(name);
		this.delayTimeouts[name] = setTimeout(this[name].bind(this, ...args), timeout);
	}

}