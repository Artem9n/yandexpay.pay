import AbstractStep from './abstractstep';

export default class Loader extends AbstractStep {

	static defaults = {
		template: '<div class="bx-yapay-skeleton-loading width--#WIDTH#"></div>'
	}

	render(node, data = {}) {
		const html = this.compile(data);

		node.innerHTML = html;
		this.restoreHtml = html;
	}

	restore(node) {
		if (this.restoreHtml == null) { return; }

		node.innerHTML = this.restoreHtml;
	}

}
