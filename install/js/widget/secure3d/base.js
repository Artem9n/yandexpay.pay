import Template from "../utils/template";

export default class Base {
	static defaults = {
		template: null,
	}

	constructor(options = {}) {
		this.options = Object.assign({}, this.constructor.defaults, options);
		this.widget = null;
	}

	render(node, data = {}) {
		node.innerHTML = this.compile(data);
	}

	compile(data) {
		return Template.compile(this.options.template, data);
	}

	setWidget(widget) {
		this.widget = widget;
	}

	getOption(key) {
		if (key in this.options) {
			return this.options[key];
		} else {
			return this.widget.options[key];
		}
	}
}