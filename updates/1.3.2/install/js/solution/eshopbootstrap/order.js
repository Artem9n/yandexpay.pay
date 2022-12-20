import Page from '../reference/page';

export default class Order extends Page {

	bootFactory(factory) {
		factory.extendDefaults({
			preserve: {
				mutation: {
					anchor: '#bx-soa-total-mobile, .bx-soa-cart-total',
					delay: null,
				},
			},
		});
	}
}
