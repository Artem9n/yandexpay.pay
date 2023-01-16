import EshopBasket from '../eshopbootstrap/basket';
import factoryLayout from './molecules/factorylayout';

export default class Basket extends EshopBasket {

	bootFactory(factory) {
		factoryLayout(factory, {
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

		this.onEvent('OnBasketChange', () => {
			cart.delayChangeBasket();
		});
	}
}