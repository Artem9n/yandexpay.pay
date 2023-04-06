import PaymentCompact from "./payment/compact";
import PaymentMini from "./payment/mini";
import PaymentSplit from "./payment/split";
import PaymentSplitSelected from "./payment/splitselected";
import PaymentSplitRequired from "./payment/splitrequired";
import PaymentSplitPreview from "./payment/splitpreview";

import CartCompact from "./cart/compact";
import CartMini from "./cart/mini";
import CartSplit from "./cart/split";
import CartSplitSelected from "./cart/splitselected";
import CartSplitRequired from "./cart/splitrequired";
import CartSplitPreview from "./cart/splitpreview";

export default class Factory {

	static make(type, widget, options) {

		const mode = widget.getOption('mode');
		const displayParameters = widget.getOption('displayParameters');

		if (mode === 'payment') {
			if (type === 'Compact') {
				return new PaymentCompact(widget, options);
			} else if (type === 'Mini') {
				return new PaymentMini(widget, options);
			} else if (type === 'BnplOffer') {
				if (displayParameters?.SPLIT_SELECT_WIDGET === '1') {
					return new PaymentSplitSelected(widget, options);
				} else {
					return new PaymentSplit(widget, options);
				}
			} else if (type === 'BnplRequired') {
				return new PaymentSplitRequired(widget, options);
			} else if (type === 'BnplPreview') {
				return new PaymentSplitPreview(widget, options);
			}
		} else {
			if (type === 'Compact') {
				return new CartCompact(widget, options);
			} else if (type === 'Mini') {
				return new CartMini(widget, options);
			} else if (type === 'BnplOffer') {
				if (displayParameters?.SPLIT_SELECT_WIDGET === '1') {
					return new CartSplitSelected(widget, options);
				} else {
					return new CartSplit(widget, options);
				}
			} else if (type === 'BnplRequired') {
				return new CartSplitRequired(widget, options);
			} else if (type === 'BnplPreview') {
				return new CartSplitPreview(widget, options);
			}
		}

		throw new Error('unknown loader widget type: ' + type);
	}

}