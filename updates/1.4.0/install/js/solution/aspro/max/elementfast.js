import Element from './element';

export default class ElementFast extends Element {

	onStarterOffer = () => {
		const offerProp = document.querySelector(".fastview-product .to-cart:not(.read_more)");
		const firstOfferId = parseInt(offerProp?.dataset?.item, 10);

		if (isNaN(firstOfferId)) { return; }

		this.cart.changeOffer(firstOfferId);
	}
}
