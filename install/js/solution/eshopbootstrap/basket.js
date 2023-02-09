import Page from '../reference/page';

export default class Basket extends Page {

	cart;

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
		this.cart = cart;
		this.onEvent('OnBasketChange', this.onBasketChange);
	}

	destroyCart(cart) {
		this.cart = cart;
		this.offEvent('OnBasketChange', this.onBasketChange);
	}

	onBasketChange = () => {
		this.cart.delayChangeBasket();
	}
}
