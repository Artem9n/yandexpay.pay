import MutationSkeleton from "./mutationskeleton";

export default class MutationObserver extends MutationSkeleton {

	static defaults = Object.assign({}, MutationSkeleton.defaults, {
		anchor: null,
		delay: 0,
	})

	constructor(element, options = {}) {
		super(element, options);
		this.observe();
	}

	destroy() {
		this.disconnect();
	}

	observe() {
		const anchor = this.getAnchor();

		if (anchor == null) {
			console?.warn('cant find anchor for node preserver');
			return;
		}

		this.observer = new window.MutationObserver(this.listener);
		this.observer.observe(anchor, {
			childList: true,
			subtree: true,
		});
	}

	disconnect() {
		if (this.observer == null) { return; }

		this.observer.disconnect();
		this.observer = null;
	}

	listener = (mutations) => {
		for (const mutation of mutations) {
			if (mutation.removedNodes == null) { continue; }

			for (const removedNode of mutation.removedNodes) {
				if (!(removedNode instanceof HTMLElement)) { continue; }

				if (removedNode === this.el || removedNode.contains(this.el)) {
					this.runCheck();
					return;
				}
			}
		}
	}

	runCheck() {
		const delay = this.options.delay;

		if (delay == null) {
			this.options.check();
		} else {
			clearTimeout(this._checkTimeout);
			this._checkTimeout = setTimeout(() => { this.options.check(); }, delay);
		}
	}

	getAnchor() {
		if (this.options.anchor == null) { return document.body; }

		return this.el.closest(this.options.anchor);
	}

}