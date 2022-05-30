import Page from '../reference/page';

export default class Element extends Page {

	bootCart(cart) {
		this.onEvent('onCatalogElementChangeOffer', (eventData) => {
			let newProductId = parseInt(eventData?.newId, 10);

			if (isNaN(newProductId)) { return; }

			cart.delayChangeOffer(newProductId);
		});
	}
}
