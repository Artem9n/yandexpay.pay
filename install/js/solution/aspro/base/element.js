import ElementSkeleton from '../../reference/element';
import factoryLayout from './molecules/factorylayout';

export default class Element extends ElementSkeleton {

	bootFactory(factory) {
		super.bootFactory(factory);
		factoryLayout(factory);
	}

	eventName() {
		return 'onAsproSkuSetPrice';
	}

	eventProductId(eventData) {
		const newProductId = parseInt(eventData?.offer?.ID, 10);

		return !isNaN(newProductId) ? newProductId : null;
	}
}
