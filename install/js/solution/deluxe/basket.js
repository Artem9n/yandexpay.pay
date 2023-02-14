import EshopBasket from '../eshopbootstrap/basket';

export default class Basket extends EshopBasket {

	eventConfig = {
		jquery: true,
		strict: true,
	};

	bootCart(cart) {
		this.cart = cart;
		this.handleAjaxSuccess(true);
	}

	destroyCart(cart) {
		this.cart = null;
		this.handleAjaxSuccess(false);
	}

	handleAjaxSuccess(dir: boolean) {
		this[dir ? 'onEvent' : 'offEvent']('ajaxSuccess', this.onAjaxSuccess, this.eventConfig);
	}

	onAjaxSuccess = (event, request, settings, data) => {
		if (
			typeof settings.url === 'string'
			&& settings.url.match(/(\/sale.basket.basket\/)(.*)(\/ajax.php)/) != null
		) {
			this.cart.delayChangeBasket();
		}
	}
}
