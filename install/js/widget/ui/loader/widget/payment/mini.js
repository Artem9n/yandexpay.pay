import Loader from '../../loader';

export default class PaymentMini extends Loader {

	static defaults = {
		template: `
		<div class="bx-yapay-skeleton-loading bx-yapay-skeleton-type-mini">
			<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-button"></div>
			<div class="bx-yapay-skeleton-load bx-yapay-skeleton-loading-description"></div>
		</div>
		`
	}
}