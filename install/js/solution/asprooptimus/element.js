import Page from '../reference/page';

export default class Element extends Page {

	bootstrap() {
		if (typeof BX === 'undefined' || typeof JCCatalogElement === 'undefined') { return; }

		BX.addCustomEvent('onAsproSkuSetPrice', (eventData) => {
			let newProductId = parseInt(eventData?.offer?.ID, 10);

			if (isNaN(newProductId)) { return; }

			this.cart.delayChangeOffer(newProductId);
		});
	}
}
