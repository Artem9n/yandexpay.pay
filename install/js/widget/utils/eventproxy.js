export class EventProxy {

	static make(config = {}) {
		return new EventProxy(config);
	}

	constructor(config = {}) {
		this.config = config;
		this.callbackMap = {
			bx: new WeakMap(),
			jquery: new WeakMap(),
			plain: new WeakMap(),
		};
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

		const selfConfig = this.typeConfig('jquery');

		if (this.canProxyCallback(selfConfig)) {
			const originalCallback = callback;

			callback = (evt, data) => {
				const proxyData = data != null ? data : evt?.originalEvent?.detail;

				originalCallback(proxyData);
			};

			this.storeCallbackVariation('jquery', originalCallback, callback);
		}

		jQuery(document).on(name, callback);
	}

	offJQueryEvent(name, callback) {
		if (typeof jQuery === 'undefined') { return; }

		const selfConfig = this.typeConfig('jquery');

		if (this.canProxyCallback(selfConfig)) {
			callback = this.getCallbackVariation('jquery', callback);

			if (callback == null) { return; }
		}

		jQuery(document).off(name, callback);
	}

	fireJQueryEvent(name, data) {
		if (typeof jQuery === 'undefined') { return; }

		jQuery(document).triggerHandler(name, data);
	}

	onPlainEvent(name, callback) {
		const selfConfig = this.typeConfig('plain');

		if (this.isPlainEventDuplicateByJQuery(selfConfig)) { return; }

		if (this.canProxyCallback(selfConfig)) {
			const originalCallback = callback;

			callback = (evt) => {
				originalCallback(evt.detail);
			};

			this.storeCallbackVariation('plain', originalCallback, callback);
		}

		document.addEventListener(name, callback);
	}

	offPlainEvent(name, callback) {
		const selfConfig = this.typeConfig('plain');

		if (this.isPlainEventDuplicateByJQuery(selfConfig)) { return; }

		if (this.canProxyCallback(selfConfig)) {
			callback = this.getCallbackVariation('plain', callback);

			if (callback == null) { return; }
		}

		document.removeEventListener(name, callback);
	}

	firePlainEvent(name, data) {
		//if (this.isPlainEventDuplicateByJQuery()) { return; }

		document.dispatchEvent(new CustomEvent(name, { "detail": data })); // todo resolve collision with jquery
	}

	isPlainEventDuplicateByJQuery(selfConfig) {
		return (selfConfig['force'] !== true && typeof jQuery !== 'undefined');
	}

	canProxyCallback(selfConfig) {
		return selfConfig['proxy'] !== false;
	}

	typeConfig(type) {
		return typeof this.config[type] === 'object' && this.config[type] != null ? this.config : {}
	}

	storeCallbackVariation(type, callback, proxy) {
		this.callbackMap[type].set(callback, proxy);
	}

	getCallbackVariation(type, callback) {
		return this.callbackMap[type].get(callback);
	}

}