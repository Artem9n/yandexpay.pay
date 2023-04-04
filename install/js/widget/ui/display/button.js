import Display from './display';
import Utils from "../../utils/template";

export default class Button extends Display {

	static defaults = {
		style: `<style>#STYLE#</style>`,
		styleHeight: `##ID# .ya-pay-button, ##ID# .bx-yapay-skeleton-loading {height:#HEIGHT#px;}`,
		styleBorder: `##ID# .ya-pay-button, ##ID# .bx-yapay-skeleton-load {border-radius:#BORDER#px;}`,
		styleWidth: `##ID# .ya-pay-button, ##ID# .bx-yapay-skeleton-loading, ##ID# .bx-yapay-divider{width: #WIDTH#px;}`,
	}

	style() {
		const collectHeight = this.collectHeight();
		const collectBorder = this.collectBorder();
		const collectWidth = this.collectWidth();

		return Utils.compile(this.getOption('style'), {
			style: collectHeight + collectBorder + collectWidth,
		});
	}

	mount(node, payment, type) {
		const theme = this.getOption('VARIANT_BUTTON') || YaPay.ButtonTheme.Black;
		const width = this.getOption('WIDTH_BUTTON') || YaPay.ButtonWidth.Max;

		payment.mountButton(node, {
			type: type,
			theme: theme,
			width: width !== 'OWN' ? width : YaPay.ButtonWidth.Max
		});
	}

	unmount(node, payment) {
		payment.unmountButton(node);
	}

	collectHeight() {

		let result = '';

		const heightVariant = this.getOption('HEIGHT_TYPE_BUTTON') || null;

		if (heightVariant == null) {
			return result;
		}

		result = Utils.compile(this.getOption('styleHeight'), {
			id: this.factory.getOption('containerId'),
			height: this.getOption('HEIGHT_VALUE_BUTTON') || '54',
		});

		return result;
	}

	collectBorder() {
		let result = '';

		const borderVariant = this.getOption('BORDER_RADIUS_TYPE_BUTTON') || null;

		if (borderVariant == null) {
			return result;
		}

		result = Utils.compile(this.getOption('styleBorder'), {
			id: this.factory.getOption('containerId'),
			border: this.getOption('BORDER_RADIUS_VALUE_BUTTON') ?? '8',
		});

		return result;
	}

	collectWidth() {
		let result = '';

		const widthVariant = this.getOption('WIDTH_BUTTON');

		if (widthVariant !== 'OWN') {
			return result;
		}

		result = Utils.compile(this.getOption('styleWidth'), {
			id: this.factory.getOption('containerId'),
			width: this.getOption('WIDTH_VALUE_BUTTON') || '282',
		});

		return result;
	}

	width() {
		let width = this.getOption('WIDTH_BUTTON') || 'MAX';

		if (width === 'OWN'){
			width = 'MAX';
		}

		return width;
	}
}