import {EventProxy} from "../../widget/utils/eventproxy";

export default class Page {

	bootFactory(factory) {

	}

	bootWidget(widget) {

	}

	bootCart(cart) {

	}

	onEvent(name, callback, config = {}) {
		EventProxy.make(config).on(name, callback);
	}

}