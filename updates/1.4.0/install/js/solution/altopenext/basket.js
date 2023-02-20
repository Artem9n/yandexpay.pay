import EshopBasket from '../eshopbootstrap/basket';

export default class Basket extends EshopBasket {

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
			config.url
			&& config.url.indexOf('/bitrix/components/altop/sale.basket.basket/ajax.php') !== -1
		)
		{
			this.cart.delayChangeBasket();
		}
	}
}