export default class MutationSkeleton {

	static defaults = {
		check: null,
	}

	constructor(element, options = {}) {
		this.el = element;
		this.options = Object.assign({}, this.constructor.defaults, options);
	}

	destroy() {

	}

}