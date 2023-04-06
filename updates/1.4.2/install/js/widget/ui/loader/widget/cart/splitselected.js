import Loader from '../../loader';

export default class CartSplitSelected extends Loader {

	static defaults = {
		template: `
		<div class="bx-yapay-skeleton-loading">
			<div class="bx-yapay-skeleton-user">
					<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-circle"></div>
					<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-line"></div>
					<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-circle"></div>
			</div>
			<div class="bx-yapay-skeleton-divider"></div>

			<div class="bx-yapay-skeleton-user bx-yapay-skeleton-split">
				<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-circle"></div>
				<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-line"></div>
				<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-ellipse-selected"></div>
			</div>
			
			<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-split-plan-plans"></div>
			<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-split-plan-pay-now"></div>

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
			
			<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-split-fee"></div>

			<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-button"></div>
			<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-description"></div>
		</div>
		`
	}
}