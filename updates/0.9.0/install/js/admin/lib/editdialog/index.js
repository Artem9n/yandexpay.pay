(function(BX) {

	var YandexPay = BX.namespace('YandexPay');

	// constructor

	YandexPay.EditDialog = function(arParams) {
		YandexPay.EditDialog.superclass.constructor.apply(this, arguments);
	};

	BX.extend(YandexPay.EditDialog, BX.CAdminDialog);

	// prototype

	YandexPay.EditDialog.prototype.Submit = function() {
		BX.onCustomEvent(this, 'onWindowSave', [this]);
	};

	// buttons

	YandexPay.EditDialog.prototype.btnSave = YandexPay.EditDialog.btnSave = {
		title: BX.message('JS_CORE_WINDOW_SAVE'),
		id: 'savebtn',
		name: 'savebtn',
		className: 'adm-btn-save yamarket-dialog-btn',
		action: function () {
			this.disableUntilError();
			this.parentWindow.Submit();
		}
	};

	YandexPay.EditDialog.btnCancel = YandexPay.EditDialog.superclass.btnCancel;
	YandexPay.EditDialog.btnClose = YandexPay.EditDialog.superclass.btnClose;

})(BX);