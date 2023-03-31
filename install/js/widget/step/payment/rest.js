import Proxy from "./proxy";
import {EventProxy} from "../../utils/eventproxy";

export default class Rest extends Proxy {

	getPaymentData(data) {
		return {
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
		this.unmount();
		this.authorize(event)
			.then((result) => {
				if (result.status === 'success') {
					setTimeout(function() {
						window.location.href = result.data.redirect;
					}, 1000);
				}
				else {
					this.payment.showError('authorize', result.reasonCode, result.reason);
				}
			})
	}

	onPaymentAbort(event) {
		EventProxy.make().fire('bxYapayPaymentAbort', event);
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
				this.widget.removeLoader();
				this.mount(paymentSession);
			})
			.catch( (err) => {
				this.payment.showError('yapayPayment','payment not created', err);
			});
	}

	mount(payment) {
		this.paymentSession = payment;
		this.payment.display.mount(this.payment.element, payment, YaPay.ButtonType.Pay);
		EventProxy.make().fire('bxYapayMountButton');
	}

	unmount() {
		this.payment.display.unmount(this.payment.element, this.paymentSession);
		this.widget.removeToolsDisplay();
	}

	authorize(event) {
		let data = {
			orderId: event.orderId,
			hash: event.metadata,
			successUrl: this.getOption('successUrl'),
		};

		return this.payment.query(this.getOption('restUrl') + 'authorize', data);
	}
}