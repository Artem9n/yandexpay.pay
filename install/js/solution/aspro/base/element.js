import ElementSkeleton from '../../reference/element';

export default class Element extends ElementSkeleton {

	eventName() {
		return 'onAsproSkuSetPrice';
	}

	eventProductId(eventData) {
		const newProductId = parseInt(eventData?.offer?.ID, 10);

		return !isNaN(newProductId) ? newProductId : null;
	}
}
