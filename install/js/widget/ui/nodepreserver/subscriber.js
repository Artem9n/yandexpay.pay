import {EventProxy} from "../../utils/eventproxy";

export default class Subscriber {

	static defaults = {
		check: null,
		event: null,
		on: null,
		off: null,
	}

	constructor(element, options = {}) {
		this.el = element;
		this.options = Object.assign({}, this.constructor.defaults, options);

		this.bind();
	}

	destroy() {
		this.unbind();

		this.options = {};
		this.el = null;
	}

	bind() {
		this.bindOn();
		this.bindEvent();
	}

	unbind() {
		this.unbindOff();
		this.unbindEvent();
	}

	bindOn() {
		if (this.options.on == null) { return; }

		this.options.on(this.options.check);
	}

	unbindOff() {
		if (this.options.off == null) { return; }

		this.options.off(this.options.check);
	}

	bindEvent() {
		const event = this.options.event;

		if (event == null) { return; }

		const proxy = new EventProxy();

		if (typeof event === 'string') {
			proxy.on(event, this.options.check);
		} else if (Array.isArray(event)) {
			event.forEach((one) => {
				proxy.on(one, this.options.check);
			});
		} else {
			console?.warn(`unknown event type ${typeof event}`);
		}
	}

	unbindEvent() {
		const event = this.options.event;

		if (event == null) { return; }

		const proxy = new EventProxy();

		if (typeof event === 'string') {
			proxy.off(event, this.options.check);
		} else if (Array.isArray(event)) {
			event.forEach((one) => {
				proxy.off(one, this.options.check);
			});
		} else {
			console?.warn(`unknown event type ${typeof event}`);
		}
	}

}