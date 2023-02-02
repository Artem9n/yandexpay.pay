import Page from "../reference/page";

export default class Element extends Page {

	initialHandler;

	bootCart(cart) {
		this.cart = cart;
		this.onStarterOffer();
		this.onCommonOffer();
	}

	onStarterOffer() {
		if (window?.elementStoresComponentParams == null) { return; }

		const firstOfferId = parseInt(window?.elementStoresComponentParams?.OFFER_ID, 10);

		if (isNaN(firstOfferId)) { return; }

		this.cart.changeOffer(firstOfferId);
	}

	onCommonOffer() {

		if (this.initialHandler != null) { return; }

		$(document).ajaxSuccess(( event, request, settings, data ) => {
			if (
				settings.url.match(/(\/catalog.item\/)(.*)(\/ajax.php)/) != null
				|| settings.url.indexOf('act=selectSku') !== -1
			) {
				const response = data;

				if (typeof response !== "object") { return; }

				let newProductId;

				for (let key in response) {

					if (!response.hasOwnProperty(key)) { continue; }

					for (let entity in response[key]) {

						if (!response[key].hasOwnProperty(entity)) { continue; }

						if (entity !== 'PRODUCT') { continue; }

						newProductId = parseInt(response[key].PRODUCT?.ID, 10);

						if (!isNaN(newProductId)) { break; }
					}
				}

				if (newProductId == null || isNaN(newProductId)) { return; }

				this.initialHandler = true;

				this.cart.delayChangeOffer(newProductId);
			}
		});
	}
}
