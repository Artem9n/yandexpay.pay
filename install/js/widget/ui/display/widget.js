import Display from './display';
import Utils from "../../utils/template";

export default class Widget extends Display {

	static defaults = {
		style: `<style>#STYLE#</style>`,
		styleBorder: `##ID# .ya-pay-widget, ##ID# .bx-yapay-skeleton-loading {border-radius:#BORDER#px !important;}`,
		styleWidth: `##ID# .ya-pay-widget, ##ID# .bx-yapay-skeleton-loading, ##ID# .bx-yapay-divider{width: #WIDTH#px !important;}`,
	}

	style() {
		const collectBorder = this.collectBorder();
		const collectWidth = this.collectWidth();

		return Utils.compile(this.getOption('style'), {
			style: collectBorder + collectWidth,
		});
	}

	mount(node, payment, type) {
		payment.mountWidget(node, {
			widgetType: this.getOption('TYPE_WIDGET') || YaPay.WidgetType.Compact,
			widgetTheme: this.getOption('THEME_WIDGET') || YaPay.WidgetTheme.Dark,
			buttonTheme: this.getOption('BUTTON_THEME_WIDGET') || YaPay.ButtonTheme.Black,
			borderRadius: this.getOption('BORDER_RADIUS_VALUE_WIDGET') || '8',
			bnplSelected: this.getOption('SPLIT_SELECT_WIDGET') || false,
		});
	}

	collectBorder() {
		let result = '';

		const borderVariant = this.getOption('BORDER_RADIUS_TYPE_WIDGET') || null;

		if (borderVariant == null) {
			return result;
		}

		result = Utils.compile(this.getOption('styleBorder'), {
			id: this.factory.getOption('containerId'),
			border: this.getOption('BORDER_RADIUS_VALUE_WIDGET') ?? '8',
		});

		return result;
	}

	collectWidth() {
		let result = '';

		const widthVariant = this.getOption('WIDTH_TYPE_WIDGET') || null;

		if (widthVariant == null) {
			return result;
		}

		result = Utils.compile(this.getOption('styleWidth'), {
			id: this.factory.getOption('containerId'),
			width: this.getOption('WIDTH_VALUE_WIDGET') || '282',
		});

		return result;
	}

	width() {
		return 'MAX';
	}
}