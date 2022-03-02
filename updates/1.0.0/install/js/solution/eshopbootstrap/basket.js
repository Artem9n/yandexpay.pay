import Page from '../reference/page';

export default class Basket extends Page {

	bootFactory(factory) {
		factory.extendDefaults({
			preserve: {
				mutation: {
					anchor: '[data-entity="basket-total-block"]',
					delay: null,
				},
			},
		});
	}

	bootCart(cart) {
		if (typeof BX === 'undefined') { return; }

		BX.addCustomEvent('OnBasketChange', () => {
			cart.getProducts().then((result) => {
				cart.combineOrderWithProducts(result);
			});
		});
	}
}
