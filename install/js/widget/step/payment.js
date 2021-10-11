import Template from '../utils/template';
import AbstractStep from './abstractstep';
import StepFactory from "./factory";

const YaPay = window.YaPay;

export default class Payment extends AbstractStep {

	static optionPrefix = 'payment';

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
					allowedCardNetworks: this.getOption('cardNetworks') || [
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
		// ������� ������.
		YaPay.createPayment(paymentData)
			.then( (payment) => {
				// ������� ��������� ������.
				let button = payment.createButton({
					type: YaPay.ButtonType.Pay,
					theme: this.getOption('buttonTheme') || YaPay.ButtonTheme.Black,
					width: this.getOption('buttonWidth') || YaPay.ButtonWidth.Auto,
				});

				// ������������ ������ � DOM.
				button.mount(node);

				// ����������� �� ������� click.
				button.on(YaPay.ButtonEventType.Click, function onPaymentButtonClick() {
					// ��������� ������ ����� ����� �� ������.
					payment.checkout();
				});

				// ����������� �� ������� process.
				payment.on(YaPay.PaymentEventType.Process, (event) => {
					// �������� ��������� �����.
					console.log(event);

					this.notify(payment, event);

					/*alert('Payment token � ' + event.token);

					// ����������� (���� ��������� ��� 7).
					alert('Billing email � ' + event.billingContact.email);

					// ������� ����� Yandex Pay.
					*/
					//payment.complete(YaPay.CompleteReason.Success);
				});

				// ����������� �� ������� error.
				payment.on(YaPay.PaymentEventType.Error, function onPaymentError(event) {
					// ������� ���������� � ������������� ������ � ������ ������
					// � ���������� ������������ ������ ������ ������.

					// ������� ����� Yandex.Pay.
					console.log({'errors': event});
					payment.complete(YaPay.CompleteReason.Error);
				});

				// ����������� �� ������� abort.
				// ��� ����� ������������ ������ ����� Yandex Pay.
				payment.on(YaPay.PaymentEventType.Abort, function onPaymentAbort(event) {
					// ���������� ������������ ������ ������ ������.
				});
			})
			.catch(function (err) {
				// ������ �� ������.
				console.log({'payment not create': err});
			});
	}

	notify(payment, yandexPayData) {
		fetch(this.getOption('YANDEX_PAY_NOTIFY_URL'), {
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
				payment.complete(YaPay.CompleteReason.Success);

				if (result.success === true) {
					this.widget.go(result.state, result);
				} else {
					this.widget.go('error', result);
				}
			});
	}
}