import Utils from "../../utils/template";

export default class Display {

	static defaults = {

	}

	constructor(factory, options) {
		this.factory = factory;
		this.options = Object.assign({}, this.constructor.defaults, options);
	}

	style() {

	}

	mount(node, payment, type) {

	}

	getOption(name) {
		return this.options[name];
	}
}