import Page from "../reference/page";

export default class Element extends Page {

	bootCart(cart) {
		this.cart = cart;
		this.onCommonOffer();
	}

	onCommonOffer() {
		$(document).ajaxComplete(( event, xhr, settings ) => {
			if (
				settings.url.match(/(\/catalog.item\/)(.*)(\/ajax.php)/) != null
				|| settings.url.indexOf('act=selectSku') !== -1
			) {
				const response = xhr.responseJSON;

				if (typeof response !== "object") { return; }

				const newProductId = response[0]?.PRODUCT?.ID;

				if (newProductId == null) { return; }

				this.initialProduct = newProductId;

				this.cart.delayChangeOffer(newProductId);
			}
		});
	}
}
