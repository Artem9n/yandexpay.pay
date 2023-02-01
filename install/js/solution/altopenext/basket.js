import EshopBasket from '../eshopbootstrap/basket';

export default class Basket extends EshopBasket {
	bootCart(cart) {

		if (typeof BX === 'undefined') { return; }

		if (this.initialized != null) { return; }

		this.onEvent('onAjaxSuccess', (response, config) => {

			if (
				config.url
				&& config.url.indexOf('/bitrix/components/altop/sale.basket.basket/ajax.php') !== -1
			)
			{
				cart.delayChangeBasket();
				this.initialized = true;
			}
		});
	}
}