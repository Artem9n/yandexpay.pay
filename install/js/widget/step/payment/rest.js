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

	createPayment(node, paymentData) {
		// Создать платеж.
		YaPay.createPayment(paymentData, { agent: { name: "CMS-Bitrix", version: "1.0" } })
			.then((payment) => {

				this.order.removeLoader();
				// Смонтировать кнопку в DOM.
				payment.mountButton(node, {
					type: YaPay.ButtonType.Pay,
					theme: this.getOption('buttonTheme') || YaPay.ButtonTheme.Black,
					width: this.getOption('buttonWidth') || YaPay.ButtonWidth.Auto,
				});

				// Подписаться на событие process.
				payment.on(YaPay.CheckoutEventType.Success, (event) => {

					node.remove();

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

					payment.complete(YaPay.CompleteReason.Success);
				});

				payment.on(YaPay.CheckoutEventType.Abort, (event) => {
					// ...
				});

				payment.on(YaPay.CheckoutEventType.Error, (event) => {
					this.order.showError('yapayPayment', 'error', event);
				});
			})
			.catch((err) => {
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