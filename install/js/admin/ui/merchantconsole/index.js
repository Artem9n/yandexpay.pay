(function(BX, $) {

	const Plugin = BX.namespace('YandexPay.Plugin');
	const Ui = BX.namespace('YandexPay.Ui');
	const Utils = BX.namespace('YandexPay.Utils');

	Ui.ConsoleField = Plugin.Base.extend({

		defaults: {
			formUrl: null,
			formTitle: null,
			formSaveTitle: null,
			formWidth: 500,
			formHeight: 350,

			merchantUrl: null,
			callbackUrl: null,
			merchantToken: null,

			registrationUrl: 'https://console.pay.yandex.ru/web/registration/organization',

			formName: 'Form with secret',
			confirmUrl: 'https://pay.yandex.ru/web/console-registration',
			confirmWidth: 960,
			confirmHeight: 700,
			confirmMessageSource: 'yandex-pay',
			confirmMessageTypeSuccess: 'merchant-data',
			confirmMessageTypeFailure: 'error',

			fieldsetElement: 'table',
			fieldElement: 'td',
			inputElement: 'input',
			nameMerchantId: 'YANDEX_PAY_MERCHANT_ID',
			nameMerchantName: 'YANDEX_PAY_MERCHANT_NAME',
			nameMerchantApiKey: 'YANDEX_PAY_REST_API_KEY',
			checkboxElement: 'input[name*="DELETE"]',
			selectGatewayElement: 'select[name*="PS_MODE"]',
			inputApplyElement: 'input[name="apply"]',

			payture: {
				gateway: 'payture',
				gateway_merchant_id: 'YANDEX_PAY_payture_PAYMENT_GATEWAY_API_KEY',
				key: 'YANDEX_PAY_payture_PAYMENT_GATEWAY_API_KEY',
				password: 'YANDEX_PAY_payture_PAYMENT_GATEWAY_PASSWORD',
			},

			alfabank: {
				gateway: 'alfabank',
				gateway_merchant_id: 'YANDEX_PAY_alfabank_PAYMENT_GATEWAY_USERNAME',
				username: 'YANDEX_PAY_alfabank_PAYMENT_GATEWAY_USERNAME',
				password: 'YANDEX_PAY_alfabank_PAYMENT_GATEWAY_PASSWORD',
			},

			mts: {
				gateway: 'rbs',
				gateway_merchant_id: 'YANDEX_PAY_mts_PAYMENT_GATEWAY_USERNAME',
				username: 'YANDEX_PAY_mts_PAYMENT_GATEWAY_USERNAME',
				password: 'YANDEX_PAY_mts_PAYMENT_GATEWAY_PASSWORD',
				acquirer: 'MTS',
			},
		},

		activate: function() {
			this.confirmWindow();
			this.pingId = setTimeout(this.ping.bind(this), 2000);
		},

		ping: function() {
			fetch(this.options.callbackUrl + 'onboard/ping', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({
					merchantAuthToken: this.options.merchantToken,
				})
			})
				.then(response => { return response.json() })
				.then((result) => {
					clearTimeout(this.pingId);

					let res = false;

					if (result.status === 'success') {
						this.fillData(result.data);
						res = true;
					}
					else if (result.status === 'processing') {
						this.pingId = setTimeout(this.ping.bind(this), 2000);
					}

					return res;
				})
				.then((res) => {
					if (res) {
						setTimeout(() => {
							let apply = document.querySelector(this.getElementSelector('inputApply'));
							apply.click();
						}, 1000);
					}
				})
				.catch(() => {
					clearTimeout(this.pingId);
				});
		},

		confirmWindow: function() {
			const url = new URL(this.options.registrationUrl);
			const formData = this.makeFormData();
			const gatewayData = this.makeGatewayData();
			const payload = Object.assign({}, formData, gatewayData);

			const form = this.createForm(url, '_blank', 'POST', payload);

			document.body.append(form);
			form.submit();

			document.body.removeChild(form);
		},

		createForm: function (url, target, method, data) {
			const form = document.createElement('form');

			form.setAttribute('target', target);
			form.setAttribute('method', method);
			form.setAttribute('action', url);

			for (const name in data) {

				if (!data.hasOwnProperty(name)) { continue; }

				const input = document.createElement('input');

				let value = data[name];

				input.setAttribute('name', name);
				input.setAttribute('type', 'hidden');
				input.setAttribute('value', value);

				form.appendChild(input);
			}

			return form;
		},

		makeGateway: function() {
			const select = document.querySelector(this.getElementSelector('selectGateway'));

			if (select == null) { return null; }

			return select.options[select.selectedIndex].value.toLowerCase();
		},

		makeGatewayData: function() {
			const fieldset = this.getElement('fieldset', this.$el, 'closest');
			const gateway = this.makeGateway();

			let values = {};

			if (gateway == null) { return values; }

			const selectors = this.options[gateway];

			if (selectors == null) { return values; }

			for (const name in selectors) {

				if (!selectors.hasOwnProperty(name)) { continue; }

				if (name === 'gateway') { continue; }

				let value = selectors[name];

				if (name === 'acquirer')
				{
					values[name] = value;
					continue;
				}

				const input = fieldset.find(`input[name*="${value}"]`).filter('[type="text"]');

				let inputVal = input.val();

				if (name === 'gateway_merchant_id')
				{
					inputVal = inputVal.replace('-api', '');
				}

				if (inputVal.trim() === '')
				{
					return {};
				}

				values[name] = inputVal;
			}

			return {
				gateway: this.options[gateway].gateway,
				creds: JSON.stringify(values)
			};
		},

		makeFormData: function() {
			return  {
				merchant_url: this.options.merchantUrl,
				callback_url: this.options.callbackUrl,
				merchant_auth_token: this.options.merchantToken,
				cms_type: 'Bitrix',
				onboard_supported: true,
			};
		},

		fillData: function(merchant) {
			const fieldset = this.getElement('fieldset', this.$el, 'closest');

			const values = {
				merchantId: merchant.onboard.merchantId,
				merchantApiKey : merchant.onboard.apiKey
			};

			for (const name in values) {
				if (!values.hasOwnProperty(name)) { continue; }

				const input = this.findInput(fieldset, name);
				const checkbox = this.findCheckbox(input);

				checkbox.prop('checked', false);
				checkbox.trigger('click');

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

	}, {
		dataName: 'UiConsoleField',
		pluginName: 'YandexPay.Ui.ConsoleField'
	});

})(BX, jQuery, window);