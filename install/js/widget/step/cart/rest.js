import Proxy from "./proxy";

export default class RestProxy extends Proxy {

	bootstrap() {
		this.getButtonData()
			.then((result) => {

				if (result.status === 'fail') { throw new Error(result.reason); }

				this.combineOrderWithData(result.data);
				this.createPayment(this.cart.element, this.cart.paymentData);

			})
			.catch((error) => {

			});
	}

	getButtonData() {

		let data = {
			productId: this.getOption('productId'),
			mode: this.getOption('mode'),
			currencyCode: this.getOption('currencyCode'),
		};

		return this.cart.query(this.getOption('restUrl'), data);
	}

	getPaymentData() {
		return {
			env: this.getOption('env'),
			version: 2,
			merchant: {
				id: this.getOption('merchantId'),
				name: this.getOption('merchantName'),
			},
			order: { id: '0' },
		}
	}

	createPayment(node, paymentData) {
		// Создать платеж.
		YaPay.createPayment(paymentData, { agent: { name: "CMS-Bitrix", version: "1.0" } })
			.then((payment) => {
				this.cart.removeLoader();
				this.cart.mountButton(node, payment);

				// Подписаться на событие process.
				payment.on(YaPay.PaymentEventType.Process, (event) => {
					// Получить платежный токен.
					payment.complete(YaPay.CompleteReason.Success);
					this.cart.paymentButton.destroy();

					console.log("Process", event);
				});

				// Подписаться на событие error.
				payment.on(YaPay.PaymentEventType.Error, (event) => {

					//this.cart.showError('yapayError', 'service temporary unavailable');
					payment.complete(YaPay.CompleteReason.Error);
					this.cart.paymentButton.destroy();

					console.log("Process", event);
				});
			})
			.catch((err) => {
				node.remove();
				this.cart.showError('yapayPayment','payment not created', err);
			});
	}

	combineOrderWithData(data) {
		const { order } = this.cart.paymentData;

		let exampleData = {
			order: {
				items: data.items,
				total: {
					amount: data.total.amount,
				},
				...order
			},
			metadata: data.metadata
		};

		Object.assign(this.cart.paymentData, exampleData);
	}

	changeOffer(newProductId) {
		let productId = this.getOption('productId');

		if (productId !== newProductId) { // todo in items
			this.cart.widget.setOptions({productId: newProductId});
			this.getButtonData().then((result) => {
				this.combineOrderWithData(result);
			});
		}
	}
}