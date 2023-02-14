import Page from '../reference/page';

export default class Element extends Page {

	initialProduct;

	eventConfig = {
		jquery: true,
		strict: true,
	};

	bootCart(cart) {
		this.cart = cart;
		this.handleCommonOffer(true);
		this.onStarterOffer();
	}

	destroyCart(cart) {
		this.cart = null;
		this.handleCommonOffer(false);
	}

	handleCommonOffer(dir: boolean) {
		this[dir ? 'onEvent' : 'offEvent']('ajaxSuccess', this.onAjaxSuccess, this.eventConfig);
	}

	onStarterOffer() {
		if (window?.elementStoresComponentParams == null) { return; }

		const firstOfferId = parseInt(window?.elementStoresComponentParams?.OFFER_ID, 10);

		if (isNaN(firstOfferId)) { return; }

		this.cart.changeOffer(firstOfferId);
	}

	onAjaxSuccess = (event, request, settings, data) => {
		if (
			typeof settings.url === 'string'
			&& settings.url.match(/(\/catalog.item\/)(.*)(\/ajax.php)/) != null
			&& (typeof settings.data === 'string' && settings.data.indexOf('act=selectSku') !== -1)
		)
		{
			const response = data;

			if (typeof response !== "object") { return; }

			let newProductId;

			for (let key in response) {

				if (!response.hasOwnProperty(key) && !response[key].hasOwnProperty('PRODUCT')) { continue; }

				newProductId = parseInt(response[key].PRODUCT?.ID, 10);

				if (!isNaN(newProductId)) { break; }
			}

			if (newProductId == null || isNaN(newProductId)) { return; }

			this.cart.delayChangeOffer(newProductId);
		}
	}
}
