import Element from "./element";

export default class ElementFast extends Element {
	bootCart(cart) {
		this.cart = cart;
		this.handleCommonOffer(true);
	}
}
