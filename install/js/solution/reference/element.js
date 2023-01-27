import Page from './page';

export default class Element extends Page {

	initialProduct;
	cart;

	bootFactory(factory) {
		this.handleStarterOffer(true);
	}

	bootCart(cart) {
		this.cart = cart;

		this.handleStarterOffer(false);
		this.bootInitialProduct();
		this.handleCommonOffer(true);
	}

	handleStarterOffer(dir) {
		this[dir ? 'onEvent' : 'offEvent'](this.eventName(), this.onStarterOffer);
	}

	handleCommonOffer(dir) {
		this[dir ? 'onEvent' : 'offEvent'](this.eventName(), this.onCommonOffer);
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
