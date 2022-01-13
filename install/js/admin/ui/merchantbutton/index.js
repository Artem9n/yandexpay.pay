(function(BX, $) {

	const Plugin = BX.namespace('YandexPay.Plugin');
	const Ui = BX.namespace('YandexPay.Ui');

	Ui.ButtonField = Plugin.Base.extend({

		defaults: {
			formUrl: null,
			formTitle: null,
			formSaveTitle: null,
			formWidth: 500,
			formHeight: 350,

			confirmUrl: 'https://i1k11.csb.app/yandex-pay-console.html',
			confirmWidth: 600,
			confirmHeight: 400,
			confirmMessageSource: 'yandex-pay',
			confirmMessageType: 'merchant-data',

			fieldsetElement: 'table',
			fieldElement: 'td',
			inputElement: 'input',
			nameMerchantId: 'YANDEX_PAY_MERCHANT_ID',
			nameMerchantName: 'YANDEX_PAY_MERCHANT_NAME',
			checkboxElement: 'input[name*="DELETE"]',
		},

		handleConfirmMessage: function(dir) {
			$(window)[dir ? 'on' : 'off']('message', $.proxy(this.onConfirmMessage, this));
		},

		onConfirmMessage: function(evt) {
			const data = this.toObject(evt.originalEvent.data);

			if (
				data.source === this.options.confirmMessageSource
				&& data.type === this.options.confirmMessageType
			) {
				this.handleConfirmMessage(false);
				this.popup.close();
				this.popup = null;

				this.confirmDeferred.resolve(data);
			}
		},

		activate: function() {
			this.requestForm()
				.then(this.confirmWindow.bind(this))
				.then(this.fillData.bind(this));
		},

		requestForm: function() {
			const form = new Ui.ModalForm(this.$el, {
				url: this.options.formUrl,
				width: this.options.formWidth,
				height: this.options.formHeight,
				saveTitle: this.options.formSaveTitle,
				title: this.options.formTitle,
			});

			return form.activate();
		},

		confirmWindow: function(data) {
			const url = this.makePopupUrl(data);

			this.popup = BX.util.popup(url, this.options.confirmWidth, this.options.confirmHeight);
			this.confirmDeferred = new $.Deferred();

			this.handleConfirmMessage(true);

			return this.confirmDeferred;
		},

		makePopupUrl: function(formData) {
			const url = new URL(this.options.confirmUrl);

			url.searchParams.append('domains', formData.data.SITE_DOMAINS);
			url.searchParams.append('name', formData.data.SHOP_NAME);

			return url.href;
		},

		fillData: function(merchant) {
			const fieldset = this.getElement('fieldset', this.$el, 'closest');
			const values = {
				merchantId: merchant.merchant_id,
				merchantName: merchant.merchant_name,
			};

			for (const name in values) {
				if (!values.hasOwnProperty(name)) { continue; }

				const input = this.findInput(fieldset, name);
				const checkbox = this.findCheckbox(input);

				checkbox.prop('checked') && checkbox.trigger('click');
				input.val(values[name]);
			}
		},

		findInput: function(fieldset, name) {
			const inputName = this.options['name' + name.substr(0, 1).toUpperCase() + name.substr(1)];

			return fieldset.find(`input[name*="${inputName}"]`).filter('[type="text"]');
		},

		findCheckbox: function(input) {
			const field = this.getElement('field', input, 'closest');

			return this.getElement('checkbox', field);
		},

		toObject: function(data) {
			try {
				const response = typeof data === "string" ? JSON.parse(data) : data;

				return typeof response === "object" && response !== null ? response : {};
			} catch (err) {
				return {};
			}
		}

	}, {
		dataName: 'UiButtonField',
		pluginName: 'YandexPay.Ui.ButtonField'
	});

})(BX, jQuery, window);