import Loader from '../loader';

export default class Compact extends Loader {

	static defaults = {
		template: `
		<div class="bx-yapay-skeleton bx-yapay-skeleton-type-mini">
			<div class="bx-yapay-skeleton-loading bx-yapay-skeleton-loading-button"></div>
			<div class="bx-yapay-skeleton-loading bx-yapay-skeleton-loading-description"></div>
		</div>
		`
	}
}