export default class Subscriber {

	static defaults = {
		check: null,
		on: null,
		off: null,
	}

	constructor(element, options = {}) {
		this.el = element;
		this.options = Object.assign({}, this.constructor.defaults, options);

		this.bind();
	}

	destroy() {
		this.unbind();

		this.options = {};
		this.el = null;
	}

	bind() {
		if (this.options.on == null) {
			console?.warn('define "on" option for subscriber of node preserver');
			return;
		}

		this.options.on(this.options.check);
	}

	unbind() {
		if (this.options.off == null) {
			console?.warn('define "off" option for subscriber of node preserver');
			return;
		}

		this.options.off(this.options.check);
	}

}