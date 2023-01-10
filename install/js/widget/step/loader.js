import AbstractStep from './abstractstep';
import Utils from "../utils/template";

export default class Loader extends AbstractStep {

	static defaults = {
		template: '<div class="bx-yapay-skeleton-loading"></div>',
		loaderSelector: '.bx-yapay-skeleton-loading',
	}

	render(node, data = {}) {
		const html = this.compile(data);
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

}
