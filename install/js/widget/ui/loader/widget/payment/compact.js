import Loader from '../../loader';

export default class PaymentCompact extends Loader {

	static defaults = {
		template: `
			<div class="bx-yapay-skeleton-loading">
				<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-button"></div>
				<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-description"></div>
			</div>
		`
	}
}