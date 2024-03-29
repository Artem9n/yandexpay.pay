import Page from "../../reference/page";

export default class Element extends Page {

	cart;
	eventConfig = {
		jquery: true,
		strict: true,
	};

	bootCart(cart) {
		this.cart = cart;
		this.onStarterOffer();
		this.handleCommonOffer(true);
	}

	destroyCart(cart) {
		this.cart = null;
		this.handleCommonOffer(false);
	}

	onStarterOffer() {
		const offerProp = document.querySelector('.sku-props.sku-props--detail');
		const firstOfferId = parseInt(offerProp?.dataset?.offerId, 10);

		if (isNaN(firstOfferId)) { return; }

		this.cart.changeOffer(firstOfferId);
	}

	handleCommonOffer(dir: boolean) {
		this[dir ? 'onEvent' : 'offEvent']('ajaxSuccess', this.onAjaxSuccess, this.eventConfig);
	}

	onAjaxSuccess = ( event, request, settings, data ) => {
		const url = settings.url;
		if (typeof url === 'string' && url.indexOf('/ajax/amount.php') === 0) {
			let response = data;

			if (typeof response !== "object") { return; }

			if (!response.success) { return; }

			let key, newProductId;

			for (key in response?.amount) {

				if (isNaN(parseInt(key, 10))) { continue; }

				newProductId = parseInt(key, 10);
			}

			if (newProductId == null) { return; }

			this.cart.delayChangeOffer(newProductId);
		}
	}
}
