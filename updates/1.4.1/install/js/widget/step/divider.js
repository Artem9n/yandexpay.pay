import AbstractStep from './abstractstep';
import Utils from "../utils/template";

export default class Divider extends AbstractStep {

	static defaults = {
		template: '<div class="bx-yapay-divider"> ' +
			'<span class="bx-yapay-divider__corner"></span> ' +
			'<span class="bx-yapay-divider__text">#LABEL#</span> ' +
			'<span class="bx-yapay-divider__corner at--right"></span> ' +
			'</div>',
		dividerSelector: '.bx-yapay-divider',
	}

	render(node, data = {}) {
		const parentNode = node.parentElement;
		const isRender = parentNode?.querySelector(this.getOption('dividerSelector'));

		if (
			!this.getOption('useDivider')
			|| isRender != null
		) { return; }

		const html = this.compile({label: this.getOption('label')});
		const element = Utils.toElement(html);

		node.insertAdjacentElement('beforebegin', element);
		this.restoreElement = element;
	}

	restore(node) {
		if (this.restoreElement == null) { return; }
		node.insertAdjacentElement('beforebegin', this.restoreElement);
	}

	remove(node) {
		const parentNode = node.parentElement;
		const divider = parentNode?.querySelector(this.getOption('dividerSelector'));
		divider?.remove();
	}

}
