import Page from '../reference/page';

export default class Order extends Page {

	bootFactory(factory) {
		factory.extendDefaults({
			preserve: {
				mutation: {
					anchor: '.bx-soa-cart-total',
					delay: null,
				},
			},
		});
	}
}
