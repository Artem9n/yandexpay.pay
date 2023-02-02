import ElementSkeleton from '../reference/element';

export default class Element extends ElementSkeleton {

	eventName() {
		return 'onCatalogStoreProductChange';
	}

	eventProductId(eventData) {
		let newProductId = parseInt(eventData, 10);

		return !isNaN(newProductId) ? newProductId : null;
	}
}
