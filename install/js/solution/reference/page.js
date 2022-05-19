export default class Page {

	bootFactory(factory) {

	}

	bootWidget(widget) {

	}

	bootCart(cart) {

	}

	onEvent(name, callback, config = {}) {
		this.matchEvent('bx', config) && this.onBxEvent(name, callback, config);
		this.matchEvent('jquery', config) && this.onJQueryEvent(name, callback, config);
		this.matchEvent('plain', config) && this.onPlainEvent(name, callback, config);
	}

	matchEvent(type, config) {
		return config[type] != null ? !!config[type] : !config['strict'];
	}

	onBxEvent(name, callback, config) {
		if (typeof BX === 'undefined') { return; }

		BX.addCustomEvent(name, callback);
	}

	onJQueryEvent(name, callback, config) {
		if (typeof jQuery === 'undefined') { return; }

		const selfConfig = this.extractEventTypeConfig('jquery', config);

		if (selfConfig['proxy'] !== false) {
			const originalCallback = callback;

			callback = (evt, data) => {
				const proxyData = data != null ? data : evt?.originalEvent?.detail;

				originalCallback(proxyData);
			};
		}

		jQuery(document).on(name, callback);
	}

	onPlainEvent(name, callback, config) {
		const selfConfig = this.extractEventTypeConfig('plain', config);

		if (selfConfig['force'] !== true && typeof jQuery !== 'undefined') { // will be catch inside jquery
			return;
		}

		if (selfConfig['proxy'] !== false) {
			const originalCallback = callback;

			callback = (evt) => {
				originalCallback(evt.detail);
			};
		}

		document.addEventListener(name, callback);
	}

	extractEventTypeConfig(type, config) {
		return typeof config[type] === 'object' && config[type] != null ? config : {}
	}

}