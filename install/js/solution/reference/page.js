import {EventProxy} from "../../widget/utils/eventproxy";

export default class Page {

	constructor() {
		this.eventProxies = {};
	}

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
		if (this.eventProxies[name] == null) {
			this.eventProxies[name] = EventProxy.make(config);
		}

		this.eventProxies[name].on(name, callback);
	}

	offEvent(name, callback, config = {}) {
		if (this.eventProxies[name] == null) { return; }

		this.eventProxies[name].off(name, callback);
	}

}