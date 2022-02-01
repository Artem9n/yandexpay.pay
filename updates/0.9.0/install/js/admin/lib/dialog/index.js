(function(BX) {

	const YandexPay = BX.namespace('YandexPay');

	// constructor

	YandexPay.Dialog = function(arParams) {
		YandexPay.Dialog.superclass.constructor.apply(this, arguments);
	};

	BX.extend(YandexPay.Dialog, BX.CAdminDialog);

	YandexPay.Dialog.prototype.SetContent = function(html) {
		let contents;
		let callback;

		YandexPay.Dialog.superclass.SetContent.call(this, html);

		if (html != null) {
			contents = this.PARTS.CONTENT_DATA;
			callback = function() {
				BX.removeCustomEvent('onAjaxSuccessFinish', callback);
				BX.onCustomEvent(BX(contents), 'onYaPayContentUpdate', [
					{ target: contents }
				]);
				BX.adminPanel && BX.adminPanel.modifyFormElements(contents);
			};

			BX.addCustomEvent('onAjaxSuccessFinish', callback);
		}
	};

})(BX, window);