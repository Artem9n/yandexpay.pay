import AbstractStep from './abstractstep';
import Utils from "../utils/template";

export default class Loader extends AbstractStep {

	static defaults = {
		template: '<div class="bx-yapay-skeleton-loading"></div>'
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

}
