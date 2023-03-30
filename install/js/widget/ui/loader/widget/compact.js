import Loader from '../loader';

export default class Compact extends Loader {

	static defaults = {
		template: `
			<div class="bx-yapay-skeleton">
				<div class="bx-yapay-skeleton-user">
					<div class="bx-yapay-skeleton-loading bx-yapay-skeleton-loading-circle"></div>
					<div class="bx-yapay-skeleton-loading bx-yapay-skeleton-loading-line"></div>
					<div class="bx-yapay-skeleton-loading bx-yapay-skeleton-loading-circle"></div>
				</div>
				<div class="bx-yapay-skeleton-loading bx-yapay-skeleton-loading-button"></div>
				<div class="bx-yapay-skeleton-loading bx-yapay-skeleton-loading-description"></div>
			</div>
		`
	}
}