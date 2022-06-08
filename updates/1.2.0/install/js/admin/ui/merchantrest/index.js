(function(BX, $) {

	const Plugin = BX.namespace('YandexPay.Plugin');
	const Ui = BX.namespace('YandexPay.Ui');
	const Utils = BX.namespace('YandexPay.Utils');

	Ui.RestField = Plugin.Base.extend({

		defaults: {
			formUrl: null,
			formTitle: null,
			formSaveTitle: null,
			formWidth: 500,
			formHeight: 350,

			formName: 'Form with secret',
			confirmUrl: 'https://pay.yandex.ru/web/console-registration',
			confirmWidth: 960,
			confirmHeight: 700,
			confirmMessageSource: 'yandex-pay',
			confirmMessageTypeSuccess: 'merchant-data',
			confirmMessageTypeFailure: 'error',

			fieldsetElement: 'form',
			fieldElement: 'td',
			inputElement: 'input',
			nameMerchantId: 'YANDEX_PAY_MERCHANT_ID',
			nameMerchantName: 'YANDEX_PAY_MERCHANT_NAME',
			nameMerchantApiKey: 'YANDEX_PAY_REST_API_KEY',
			checkboxElement: 'input[name*="DELETE"]',
			selectGatewayElement: 'select[name*="PS_MODE"]',

			payture: {
				gateway_merchant_id: 'YANDEX_PAY_payture_PAYMENT_GATEWAY_API_KEY',
				key: 'YANDEX_PAY_payture_PAYMENT_GATEWAY_API_KEY',
				password: 'YANDEX_PAY_payture_PAYMENT_GATEWAY_PASSWORD'
			}
		},

		handleConfirmMessage: function(dir) {
			$(window)[dir ? 'on' : 'off']('message', $.proxy(this.onConfirmMessage, this));
		},

		onConfirmMessage: function(evt) {
			const data = this.toObject(evt.originalEvent.data);
			console.log(data);
			if (data.source === this.options.confirmMessageSource) {
				this.handleConfirmMessage(false);
				this.popup.close();
				this.popup = null;

				if (data.type === this.options.confirmMessageTypeSuccess) {
					this.confirmDeferred.resolve(data);
				} else if (data.type === this.options.confirmMessageTypeFailure){
					this.confirmDeferred.reject(data);
				}
			}
		},

		activate: function() {
			this.requestForm()
				.then(this.confirmWindow.bind(this))
				.then(this.fillData.bind(this))
				.catch(data => alert(data.error));
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
			const payload = this.makePayload(data);

			this.popup = window.open(url, this.options.formName, this.getWindowFeatures([this.options.confirmWidth, this.options.confirmHeight]));;
			this.confirmDeferred = new $.Deferred();

			const form = this.createForm(this.options.formName, {
				payload: payload,
				method: 'post',
				url
			});

			document.body.appendChild(form);

			form.submit()
			document.body.removeChild(form);

			this.handleConfirmMessage(true);

			return this.confirmDeferred;
		},

		getWindowFeatures: function (formSize) {
			const { screen } = window;
			const [width, height] = formSize;

			// NB: Если экран маленький, то не отдаем параметры
			if (screen.width < width || screen.height < height) {
				return undefined;
			}

			const left = (screen.width - width) >> 1;
			const top = (screen.height - height) >> 1;

			return [
				"scrollbars=yes",
				"resizable=yes",
				"status=no",
				"location=no",
				"toolbar=no",
				"menubar=no",
				`width=${width}`,
				`height=${height}`,
				`left=${left}`,
				`top=${top}`
			].join(",");
		},

		createForm: function (target, { payload, method, url }) {
			const form = document.createElement('form');
			const input = this.createInput(payload);

			form.setAttribute('target', target);
			form.setAttribute('method', method);
			form.setAttribute('action', url);

			form.appendChild(input);

			return form;
		},

		createInput: function (payload) {
			const input = document.createElement('input');

			input.setAttribute('name', 'payload');
			input.setAttribute('type', 'hidden');
			input.setAttribute('value', JSON.stringify(payload));

			return input;
		},

		makePopupUrl: function(formData) {
			const url = new URL(this.options.confirmUrl);

			url.searchParams.append('domains', formData.data.SITE_DOMAINS);
			url.searchParams.append('name', formData.data.SHOP_NAME);
			url.searchParams.append('callback_url', formData.data.CALLBACK_URL);
			url.searchParams.append('gateway', this.makeGateway());

			return url.href;
		},

		makeGateway: function() {
			const select = document.querySelector(this.getElementSelector('selectGateway'));
			return select.options[select.selectedIndex].text.toLowerCase();
		},

		makePayload: function() {
			const fieldset = this.getElement('fieldset', this.$el, 'closest');
			const gateway = this.makeGateway();
			const selectors = this.options[gateway];

			let values = {};

			if (selectors == null) { return ''}

			for (const name in selectors) {
				if (!selectors.hasOwnProperty(name)) { continue; }

				let value = selectors[name];

				const input = fieldset.find(`input[name*="${value}"]`).filter('[type="text"]');

				values[name] = input.val();
			}

			return JSON.stringify(values);
		},

		fillData: function(merchant) {
			const fieldset = this.getElement('fieldset', this.$el, 'closest');

			const values = {
				merchantId: merchant.merchant_id,
				merchantName: merchant.merchant_name,
				merchantApiKey : merchant.key_value
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
		dataName: 'UiRestField',
		pluginName: 'YandexPay.Ui.RestField'
	});

})(BX, jQuery, window);