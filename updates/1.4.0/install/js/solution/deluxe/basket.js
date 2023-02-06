import Page from '../reference/page';

export default class Basket extends Page {

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
		$(document).ajaxComplete(( event, xhr, settings ) => {
			if (settings.url.match(/(\/sale.basket.basket\/)(.*)(\/ajax.php)/) != null) {
				cart.delayChangeBasket();
			}
		});
	}
}
