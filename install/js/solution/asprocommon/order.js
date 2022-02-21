import Page from '../reference/page';
import factoryLayout from './molecules/factorylayout';

export default class Order extends Page {

	bootFactory(factory) {
		factoryLayout(factory, {
			preserve: {
				mutation: {
					anchor: '#bx-soa-total, #bx-soa-total-mobile',
					delay: null,
				},
			},
		});
	}
}
