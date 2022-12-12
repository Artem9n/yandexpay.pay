export class EventProxy {

	static make(config = {}) {
		return new EventProxy(config);
	}

	constructor(config = {}) {
		this.config = config;
		this.callbackMap = {};
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
		if (typeof BX === 'undefined' || BX.addCustomEvent == null) { return; }

		BX.addCustomEvent(name, callback);
	}

	offBxEvent(name, callback) {
		if (typeof BX === 'undefined' || BX.removeCustomEvent == null) { return; }

		BX.removeCustomEvent(name, callback);
	}

	fireBxEvent(name, data) {
		if (typeof BX === 'undefined' || BX.onCustomEvent == null) { return; }

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

			this.pushCallbackVariation('jquery', originalCallback, callback);
		}

		jQuery(document).on(name, callback);
	}

	offJQueryEvent(name, callback) {
		if (typeof jQuery === 'undefined') { return; }

		const selfConfig = this.typeConfig('jquery');

		if (this.canProxyCallback(selfConfig)) {
			callback = this.popCallbackVariation('jquery', callback);

			if (callback == null) { return; }
		}

		jQuery(document).off(name, callback);
	}

	fireJQueryEvent(name, data) {
		if (typeof jQuery === 'undefined') { return; }
		if (this.hasPlainAndJQueryCollision()) { return; }

		jQuery(document).triggerHandler(new CustomEvent(name, { "detail": data }));
	}

	onPlainEvent(name, callback) {
		if (this.hasPlainAndJQueryCollision()) { return; }

		const selfConfig = this.typeConfig('plain');

		if (this.canProxyCallback(selfConfig)) {
			const originalCallback = callback;

			callback = (evt) => {
				originalCallback(evt.detail);
			};

			this.pushCallbackVariation('plain', originalCallback, callback);
		}

		document.addEventListener(name, callback);
	}

	offPlainEvent(name, callback) {
		if (this.hasPlainAndJQueryCollision()) { return; }

		const selfConfig = this.typeConfig('plain');

		if (this.canProxyCallback(selfConfig)) {
			callback = this.popCallbackVariation('plain', callback);

			if (callback == null) { return; }
		}

		document.removeEventListener(name, callback);
	}

	firePlainEvent(name, data) {
		document.dispatchEvent(new CustomEvent(name, { "detail": data }));
	}

	hasPlainAndJQueryCollision() {
		if (!this.matchEvent('plain') || !this.matchEvent('jquery')) { return false; }

		const plainConfig = this.typeConfig('plain');

		return (plainConfig['force'] !== true && typeof jQuery !== 'undefined');
	}

	canProxyCallback(selfConfig) {
		return selfConfig['proxy'] !== false;
	}

	typeConfig(type) {
		return typeof this.config[type] === 'object' && this.config[type] != null ? this.config : {};
	}

	pushCallbackVariation(type, callback, proxy) {
		if (this.callbackMap[type] == null) {
			this.callbackMap[type] = new WeakMap();
		}

		this.callbackMap[type].set(callback, proxy);
	}

	popCallbackVariation(type, callback) {
		if (this.callbackMap[type] == null) { return null; }

		const map = this.callbackMap[type];
		const result = map.get(callback);

		if (result != null) {
			map.delete(callback);
		}

		return result;
	}

}