import ElementSkeleton from '../reference/element';

export default class Element extends ElementSkeleton {

	eventName() {
		return 'onCatalogElementChangeOffer';
	}

	eventProductId(eventData) {
		let newProductId = parseInt(eventData?.newId, 10);

		return !isNaN(newProductId) ? newProductId : null;
	}
}
