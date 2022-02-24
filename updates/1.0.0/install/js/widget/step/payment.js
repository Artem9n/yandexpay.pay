import Template from '../utils/template';
import AbstractStep from './abstractstep';

const YaPay = window.YaPay;

export default class Payment extends AbstractStep {

	static defaults = {
		template: '<div class="alert alert-success" role="alert"><strong>#MESSAGE#</strong></div>'
	}

	render(node, data) {
		const paymentData = this.getPaymentData(data);

		this.createPayment(node, paymentData);
	}

	compile(data) {
		return Template.compile(this.options.template, data);
	}

	getPaymentData(data) {
		return {
			env: this.getOption('env'),
			version: 2,
			countryCode: YaPay.CountryCode.Ru,
			currencyCode: YaPay.CurrencyCode.Rub,
			merchant: {
				id: this.getOption('merchantId'),
				name: this.getOption('merchantName')
			},
			order: {
				id: data.id,
				total: { amount: data.total },
				items: data.items
			},
			paymentMethods: [
				{
					type: YaPay.PaymentMethodType.Card,
					gateway: this.getOption('gateway'),
					gatewayMerchantId: this.getOption('gatewayMerchantId'),
					allowedAuthMethods: [YaPay.AllowedAuthMethod.PanOnly],
					allowedCardNetworks: [
						YaPay.AllowedCardNetwork.UnionPay,
						YaPay.AllowedCardNetwork.Uzcard,
						YaPay.AllowedCardNetwork.Discover,
						YaPay.AllowedCardNetwork.AmericanExpress,
						YaPay.AllowedCardNetwork.Visa,
						YaPay.AllowedCardNetwork.Mastercard,
						YaPay.AllowedCardNetwork.Mir,
						YaPay.AllowedCardNetwork.Maestro,
						YaPay.AllowedCardNetwork.VisaElectron
					]
				}
			],
		}
	}

	createPayment(node, paymentData) {
		// Создать платеж.
		YaPay.createPayment(paymentData, { agent: { name: "CMS-Bitrix", version: "1.0" } })
			.then( (payment) => {
				// Создать экземпляр кнопки.
				let button = payment.createButton({
					type: YaPay.ButtonType.Pay,
					theme: this.getOption('buttonTheme') || YaPay.ButtonTheme.Black,
					width: this.getOption('buttonWidth') || YaPay.ButtonWidth.Auto,
				});

				// Смонтировать кнопку в DOM.
				button.mount(node);

				// Подписаться на событие click.
				button.on(YaPay.ButtonEventType.Click, function onPaymentButtonClick() {
					// Запустить оплату после клика на кнопку.
					payment.checkout();
				});

				// Подписаться на событие process.
				payment.on(YaPay.PaymentEventType.Process, (event) => {
					// Получить платежный токен.
					this.notify(payment, event).then((result) => {
					});
					/*alert('Payment token — ' + event.token);

					// Опционально (если выполнить шаг 7).
					alert('Billing email — ' + event.billingContact.email);

					// Закрыть форму Yandex Pay.
					*/
					payment.complete(YaPay.CompleteReason.Success);
				});

				// Подписаться на событие error.
				payment.on(YaPay.PaymentEventType.Error, function onPaymentError(event) {
					// Вывести информацию о недоступности оплаты в данный момент
					// и предложить пользователю другой способ оплаты.

					// Закрыть форму Yandex.Pay.
					console.log({'errors': event});
					payment.complete(YaPay.CompleteReason.Error);
				});

				// Подписаться на событие abort.
				// Это когда пользователь закрыл форму Yandex Pay.
				payment.on(YaPay.PaymentEventType.Abort, function onPaymentAbort(event) {
					// Предложить пользователю другой способ оплаты.
				});
			})
			.catch(function (err) {
				// Платеж не создан.
				console.log({'payment not create': err});
			});
	}

	notify(payment, yandexPayData) {
		return fetch(this.getOption('notifyUrl'), {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
			},
			body: JSON.stringify({
				service: this.getOption('requestSign'),
				accept: 'json',
				yandexData: yandexPayData,
				externalId: this.getOption('externalId'),
				paySystemId: this.getOption('paySystemId')
			})
		})
			.then(response => response.json())
			.then(result => {
				if (result.success === true) {
					this.widget.go(result.state, result);
				} else {
					this.widget.go('error', result);
				}
			})
			.catch(error => console.log(error) );
	}
}