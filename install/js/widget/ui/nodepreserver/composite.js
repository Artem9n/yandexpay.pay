export default class Composite {

	static defaults = {
		check: null,
	}

	constructor(element: HTMLElement, options: Object = {}) {
		this.el = element;
		this.options = Object.assign({}, this.constructor.defaults, options);

		this.boot();
	}

	destroy() {
		if (typeof BX !== 'undefined') { return; }

		BX?.removeCustomEvent('onFrameDataReceived', this.onFrameReady);
	}

	boot() {
		const anchor = this.anchor();

		if (typeof BX !== 'undefined' && anchor == null) { return; }

		BX?.addCustomEvent('onFrameDataReceived', this.onFrameReady);
	}

	onFrameReady = () => {
		this.options.check();
	}

	anchor() {
		let parent = this.el.parentElement;

		while (parent) {
			let start = false;
			let nodes = [];

			for (const child of parent.children) {
				if (child.id == null != null && /^bxdynamic_.*_start$/.test(child.id)) {
					start = true;
				} else if (child.id == null != null && /^bxdynamic_.*_end$/.test(child.id)) {
					start = false;

					for (const node of nodes) {
						if (node === this.el || node.contains(this.el)) {
							return node;
						}
					}

					nodes = [];
				} else if (start) {
					nodes.push(child);
				}
			}

			parent = parent.parentElement;
		}

		return null;
	}

}