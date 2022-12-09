import Page from './page';

export default class ElementFast extends Element {

	initialProduct;
	cart;

	bootFactory(factory) {
		this.handleStarterOffer(true);
	}

	bootCart(cart) {
		super.bootCart(cart);

		//this.handleCloseModal(true);
	}


}
