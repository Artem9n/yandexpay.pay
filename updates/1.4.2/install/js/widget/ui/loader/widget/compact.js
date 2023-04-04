import Loader from '../loader';
import Template from "../../../utils/template";
import Utils from "../../../utils/template";

export default class Compact extends Loader {

	static defaults = {
		template: `
			<div class="bx-yapay-skeleton-loading">
				#USER_DESCRIPTION#
				<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-button"></div>
				<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-description"></div>
			</div>
		`
	}

	render(node, data: {} = {}) {
		if (node.querySelector(this.getOption('loaderSelector')) != null) { return; }

		const mode = this.getOption('mode');
		let compileData = {
			user_description: '',
		};

		if (mode !== 'payment') {
			compileData.user_description = `<div class="bx-yapay-skeleton-user">
					<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-circle"></div>
					<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-line"></div>
					<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-circle"></div>
				</div>`;
		}

		const html = Template.compile(this.getOption('template'), compileData);
		const element = Utils.toElement(html);

		node.insertAdjacentElement('beforeend', element);
		this.restoreElement = element;
	}
}