import Page from '../reference/page';

export default class Basket extends Page {

	bootstrap() {
		if (typeof BX === 'undefined') { return; }

		BX.addCustomEvent('OnBasketChange', () => {
			this.cart.getProducts().then((result) => {
				this.cart.combineOrderWithProducts(result);
			});
		});
	}
}
