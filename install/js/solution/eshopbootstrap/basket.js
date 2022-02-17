import Page from '../reference/page';

export default class Basket extends Page {

	bootCart(cart) {
		if (typeof BX === 'undefined') { return; }

		BX.addCustomEvent('OnBasketChange', () => {
			cart.getProducts().then((result) => {
				cart.combineOrderWithProducts(result);
			});
		});
	}
}
