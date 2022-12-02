import Proxy from "./proxy";

export default class RestProxy extends Proxy {

	getPaymentData(data) {
		return   {
			env: this.getOption('env'),
			version: 3,
			currencyCode: YaPay.CurrencyCode.Rub,
			merchantId: this.getOption('merchantId'),
			orderId: data.id,
			cart: {
				items: data.items,
				total: {
					amount: data.total,
				},
			},
			metadata: this.getOption('metadata'),
		}
	}

	onPaymentSuccess(event) {
		this.order.element.remove();

		this.authorize(event)
			.then((result) => {
				if (result.status === 'success') {
					setTimeout(function() {
						window.location.href = result.data.redirect;
					}, 1000);
				}
				else {
					this.order.showError('authorize', result.reasonCode, result.reason);
				}
			})
	}

	onPaymentAbort(event) {

	}

	onPaymentError(event) {
		setTimeout(() => {
			window.location.href = this.getOption('failUrl');
		}, 1000);
	}

	createPayment(node, paymentData) {

		YaPay.createSession(paymentData, {
			onSuccess: this.onPaymentSuccess.bind(this),
			onAbort: this.onPaymentAbort.bind(this),
			onError: this.onPaymentError.bind(this),
			agent: { name: 'CMS-Bitrix', version: '1.0' }
		})
			.then( (paymentSession) => {

				this.order.removeLoader();

				paymentSession.mountButton(node, {
					type: YaPay.ButtonType.Pay,
					theme: this.getOption('buttonTheme') || YaPay.ButtonTheme.Black,
					width: this.getOption('buttonWidth') || YaPay.ButtonWidth.Auto,
				});
			})
			.catch( (err) => {
				this.order.showError('yapayPayment','payment not created', err);
			});
	}

	authorize(event) {
		let data = {
			orderId: event.orderId,
			hash: event.metadata,
			successUrl: this.getOption('successUrl'),
		};

		return this.order.query(this.getOption('restUrl') + 'authorize', data);
	}
}