import Loader from '../loader';
import Template from "../../../utils/template";
import Utils from "../../../utils/template";

export default class Split extends Loader {

	static defaults = {
		template: `
		<div class="bx-yapay-skeleton-loading">
			
			#USER_DESCRIPTION#

			<div class="bx-yapay-skeleton-user bx-yapay-skeleton-split">
				<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-circle"></div>
				<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-line"></div>
				<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-#TYPE#"></div>
			</div>
			
			#SPLIT_NOW#

			<div class="bx-yapay-skeleton-user bx-yapay-skeleton-split-plan">

			<div class="bx-yapay-skeleton-split-plan-list">
			
				<div class="bx-yapay-skeleton-split-plan-item">
					<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-split-plan-payline"></div>
					<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-split-plan-pay"></div>
				</div>
			
				<div class="bx-yapay-skeleton-split-plan-item">
					<div class="bx-yapay-skeleton-split-plan-item-group">
						<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-split-plan-payline"></div>
						<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-split-plan-payline"></div>
						<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-split-plan-payline"></div>
						<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-split-plan-payline"></div>
						<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-split-plan-payline"></div>
					</div>
					<div class="bx-yapay-skeleton-load bx-yapay-skeleton-split-plan-pay-date"></div>
				</div>
			</div>
			
			</div>
			
			#SPLIT_FEE#

			<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-button"></div>
			<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-description"></div>
		</div>
		`
	}

	render(node, data = {}) {
		if (node.querySelector(this.getOption('loaderSelector')) != null) { return; }

		const displayParameters = this.getOption('displayParameters');
		const mode = this.getOption('mode');
		let compileData;

		if (displayParameters?.TYPE_WIDGET === 'BnplRequired') {
			compileData = {
				type: 'circle-info',
				split_now: '<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-split-plan-plans"></div>' +
					'<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-split-plan-pay-now"></div>',
				split_fee: '<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-split-fee"></div>',
			};
		} else if (displayParameters?.SPLIT_SELECT_WIDGET === '1') {
			compileData = {
				type: 'ellipse-selected',
				split_now: '<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-split-plan-plans"></div>' +
					'<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-split-plan-pay-now"></div>',
				split_fee: '<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-split-fee"></div>',
			};
		} else {
			compileData = {
				type: 'ellipse',
				split_now: '',
				split_fee: '',
			};
		}

		if (mode !== 'payment') {
			compileData.user_description = `<div class="bx-yapay-skeleton-user">
					<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-circle"></div>
					<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-line"></div>
					<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-circle"></div>
				</div>
				<div class="bx-yapay-skeleton-divider"></div>`;
		} else {
			compileData.user_description = '';
		}

		const html = Template.compile(this.getOption('template'), compileData);
		const element = Utils.toElement(html);

		node.insertAdjacentElement('beforeend', element);
		this.restoreElement = element;
	}
}