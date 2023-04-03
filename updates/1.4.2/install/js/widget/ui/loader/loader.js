import Utils from "../../utils/template";
import Template from "../../utils/template";

export default class Loader {

	static defaults = {
		template: '',
	}

	constructor(widget, options) {
		this.widget = widget;
		this.options = Object.assign({}, this.constructor.defaults, options);
		this.options.loaderSelector = '.bx-yapay-skeleton';
	}

	render(node, data = {}) {
		if (node.querySelector(this.getOption('loaderSelector')) != null) { return; }

		const html = Template.compile(this.getOption('template'), data);
		const element = Utils.toElement(html);

		node.insertAdjacentElement('beforeend', element);
		this.restoreElement = element;
	}

	restore(node) {
		if (this.restoreElement == null) { return; }

		node.insertAdjacentElement('beforeend', this.restoreElement);
	}

	remove(node) {
		const loader = node.querySelector(this.getOption('loaderSelector'));
		loader?.remove();
	}

	/**
	 *
	 * @param {string} name
	 * @returns {*}
	 */
	getOption(name) {
		return this.options[name] ?? this.widget.getOption(name) ?? this.defaults[name];
	}
}