export class EventProxy {

	static make(config = {}) {
		return new EventProxy(config);
	}

	constructor(config = {}) {
		this.config = config;
	}

	on(name, callback) {
		this.matchEvent('bx') && this.onBxEvent(name, callback);
		this.matchEvent('jquery') && this.onJQueryEvent(name, callback);
		this.matchEvent('plain') && this.onPlainEvent(name, callback);
	}

	off(name, callback) {
		this.matchEvent('bx') && this.offBxEvent(name, callback);
		this.matchEvent('jquery') && this.offJQueryEvent(name, callback);
		this.matchEvent('plain') && this.offPlainEvent(name, callback);
	}

	fire(name, data = {}) {
		this.matchEvent('bx') && this.fireBxEvent(name, data);
		this.matchEvent('jquery') && this.fireJQueryEvent(name, data);
		this.matchEvent('plain') && this.firePlainEvent(name, data);
	}

	matchEvent(type) {
		return this.config[type] != null ? !!this.config[type] : !this.config['strict'];
	}

	onBxEvent(name, callback) {
		if (typeof BX === 'undefined') { return; }

		BX.addCustomEvent(name, callback);
	}

	offBxEvent(name, callback) {
		if (typeof BX === 'undefined') { return; }

		BX.removeCustomEvent(name, callback);
	}

	fireBxEvent(name, data) {
		if (typeof BX === 'undefined') { return; }

		BX.onCustomEvent(name, [data]);
	}

	onJQueryEvent(name, callback) {
		if (typeof jQuery === 'undefined') { return; }

		const selfConfig = this.extractEventTypeConfig('jquery');

		if (selfConfig['proxy'] !== false) {
			const originalCallback = callback;

			callback = (evt, data) => {
				const proxyData = data != null ? data : evt?.originalEvent?.detail;

				originalCallback(proxyData);
			};
		}

		jQuery(document).on(name, callback);
	}

	offJQueryEvent(name, callback) {
		if (typeof jQuery === 'undefined') { return; }

		jQuery(document).off(name, callback); // todo unbind with proxy
	}

	fireJQueryEvent(name, data) {
		if (typeof jQuery === 'undefined') { return; }

		jQuery(document).triggerHandler(name, data);
	}

	onPlainEvent(name, callback) {
		if (this.isPlainEventDuplicateByJQuery()) { return; }

		const selfConfig = this.extractEventTypeConfig('plain');

		if (selfConfig['proxy'] !== false) {
			const originalCallback = callback;

			callback = (evt) => {
				originalCallback(evt.detail);
			};
		}

		document.addEventListener(name, callback);
	}

	offPlainEvent(name, callback) {
		if (this.isPlainEventDuplicateByJQuery()) { return; }

		document.removeEventListener(name, callback); // todo unbind with proxy
	}

	firePlainEvent(name, data) {
		//if (this.isPlainEventDuplicateByJQuery()) { return; }

		document.dispatchEvent(new CustomEvent(name, { "detail": data })); // todo resolve collision with jquery
	}

	isPlainEventDuplicateByJQuery() {
		const selfConfig = this.extractEventTypeConfig('plain');

		return (selfConfig['force'] !== true && typeof jQuery !== 'undefined');
	}

	extractEventTypeConfig(type) {
		return typeof this.config[type] === 'object' && this.config[type] != null ? this.config : {}
	}

}