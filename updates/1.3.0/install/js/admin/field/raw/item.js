(function(BX, $, window) {

	const FieldRaw = BX.namespace('YandexPay.Field.Raw');
	const FieldReference = BX.namespace('YandexPay.Field.Reference');

	const constructor = FieldRaw.Item = FieldReference.Base.extend({

		defaults: {
			inputElement: 'input:not([type="button"]), textarea, select',
		},

		updateName: function() {
			const baseName = this.getBaseName();
			const inputs = this.getElement('input');

			inputs.attr('name', baseName);
		}

	}, {
		dataName: 'FieldRawItem',
	});

})(BX, jQuery, window);