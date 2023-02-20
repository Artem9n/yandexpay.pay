import ElementSkeleton from '../../reference/element';

export default class Element extends ElementSkeleton {
	bootFactory(factory) {

	}

	bootCart(cart) {
		this.cart = cart;
		this.onStarterOffer();
		this.handleCommonOffer(true);
	}

	destroyCart(cart) {
		this.cart = null;
		this.handleCommonOffer(false);
	}

	onStarterOffer = () => {
		const offerProp = document.querySelector(".to-cart:not(.read_more)");
		const firstOfferId = parseInt(offerProp?.dataset?.item, 10);

		if (isNaN(firstOfferId)) { return; }

		this.cart.changeOffer(firstOfferId);
	}

	eventName() {
		return 'onAsproSkuSetPrice';
	}

	eventProductId(eventData) {
		const newProductId = parseInt(eventData?.offer?.ID, 10);

		return !isNaN(newProductId) ? newProductId : null;
	}
}
