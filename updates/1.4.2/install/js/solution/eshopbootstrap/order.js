import Page from '../reference/page';

export default class Order extends Page {

	bootFactory(factory) {
		factory.extendDefaults({
			preserve: {
				mutation: {
					anchor: '#bx-soa-total-mobile, #bx-soa-total, .bx-soa-cart-total',
					delay: null,
				},
			},
		});
	}

	bootCart(cart) {
		this.cart = cart;
		this.handleAjaxSuccess(true);
	}

	destroyCart(cart) {
		this.cart = null;
		this.handleAjaxSuccess(false);
	}

	handleAjaxSuccess(dir: boolean) {
		this[dir ? 'onEvent' : 'offEvent']('onAjaxSuccess', this.onAjaxSuccess, this.eventConfig);
	}

	onAjaxSuccess = (response, config) => {
		if (
			config.data.indexOf('enterCoupon') !== -1
			|| config.data.indexOf('removeCoupon') !== -1
		)
		{
			this.cart.delayChangeBasket();
		}
	}
}
