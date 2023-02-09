import {EventProxy} from "../../widget/utils/eventproxy";

export default class Page {

	bootFactory(factory) {

	}

	bootWidget(widget) {

	}

	destroyWidget(widget) {

	}

	bootCart(cart) {

	}

	destroyCart(cart) {

	}

	onEvent(name, callback, config = {}) {
		EventProxy.make(config).on(name, callback);
	}

	offEvent(name, callback, config = {}) {
		EventProxy.make(config).off(name, callback);
	}

}