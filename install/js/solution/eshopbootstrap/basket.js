import Page from '../reference/page';

export default class Basket extends Page {

	cart;
	eventConfig = {
		bx: true,
		strict: true,
	};

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
		this.handleBasketChange(true);
	}

	destroyCart(cart) {
		this.cart = null;
		this.handleBasketChange(false);
	}

	handleBasketChange(dir: boolean) {
		this[dir ? 'onEvent' : 'offEvent']('OnBasketChange', this.onBasketChange, this.eventConfig);
		this[dir ? 'onEvent' : 'offEvent']('OnCouponApply', this.onBasketChange, this.eventConfig);
	}

	onBasketChange = () => {
		this.cart.delayChangeBasket();
	}
}
