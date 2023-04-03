import Compact from "./compact";
import Mini from "./mini";
import Split from "./split";

export default class Factory {

	static make(type, widget, options) {
		if (type === 'Compact') {
			return new Compact(widget, options);
		} else if (type === 'Mini') {
			return new Mini(widget, options);
		} else if (type === 'BnplOffer' || type === 'BnplRequired') {
			return new Split(widget, options);
		}

		throw new Error('unknown loader widget type: ' + type);
	}

}