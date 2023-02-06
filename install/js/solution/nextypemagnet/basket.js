import EshopBasket from '../eshopbootstrap/basket';

export default class Basket extends EshopBasket {

	bootFactory(factory) {
		super.bootFactory(factory);
	}

	bootCart(cart) {
		if (typeof BX === 'undefined') { return; }

		this.onEvent('OnBasketChange', () => {
			cart.delayChangeBasket();
		});
	}
}