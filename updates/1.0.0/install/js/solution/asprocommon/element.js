import Page from '../reference/page';
import factoryLayout from './molecules/factorylayout';

export default class Element extends Page {

	bootFactory(factory) {
		factoryLayout(factory);
	}

	bootCart(cart) {
		if (typeof BX === 'undefined' || typeof JCCatalogElement === 'undefined') { return; }

		BX.addCustomEvent('onAsproSkuSetPrice', (eventData) => {
			let newProductId = parseInt(eventData?.offer?.ID, 10);

			if (isNaN(newProductId)) { return; }

			cart.delayChangeOffer(newProductId);
		});
	}
}
