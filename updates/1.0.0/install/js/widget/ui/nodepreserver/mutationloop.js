import MutationSkeleton from "./mutationskeleton";

export default class MutationLoop extends MutationSkeleton {

	static defaults = Object.assign({}, MutationSkeleton.defaults, {
		timeout: 1000,
	})

	constructor(element, options = {}) {
		super(element, options);
		this.loopTimeout();
	}

	destroy() {
		this.loopCancel();
	}

	loopTimeout() {
		clearTimeout(this._loopTimeout);
		this._loopTimeout = setTimeout(this.loop, this.options.timeout);
	}

	loopCancel() {
		clearTimeout(this._loopTimeout);
	}

	loop = () => {
		this.options.check() && this.loopTimeout();
	}

}