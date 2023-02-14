import Page from './page';

export default class Element extends Page {

	initialProduct;
	cart;
	eventConfig = {
		bx: true,
		strict: true,
	};

	bootFactory(factory) {
		this.handleStarterOffer(true);
	}

	bootCart(cart) {
		this.cart = cart;
		this.handleStarterOffer(false);
		this.bootInitialProduct();
		this.handleCommonOffer(true);
	}

	destroyCart(cart) {
		this.cart = null;
		this.handleStarterOffer(false);
		this.handleCommonOffer(false);
	}

	handleStarterOffer(dir) {
		this[dir ? 'onEvent' : 'offEvent'](this.eventName(), this.onStarterOffer, this.eventConfig);
	}

	handleCommonOffer(dir) {
		this[dir ? 'onEvent' : 'offEvent'](this.eventName(), this.onCommonOffer, this.eventConfig);
	}

	onStarterOffer = (eventData) => {
		let newProductId = this.eventProductId(eventData);

		if (newProductId == null) { return; }

		this.initialProduct = newProductId;
	}

	onCommonOffer = (eventData) => {
		let newProductId = this.eventProductId(eventData);

		if (newProductId == null) { return; }

		this.cart.delayChangeOffer(newProductId);
	}

	eventName() {
		throw new Error('not implemented');
	}

	eventProductId(eventData) {
		let newProductId = parseInt(eventData?.newId, 10);

		return !isNaN(newProductId) ? newProductId : null;
	}

	bootInitialProduct() {
		if (this.initialProduct == null) { return; }

		this.cart.changeOffer(this.initialProduct);
		this.initialProduct = null;
	}
}
