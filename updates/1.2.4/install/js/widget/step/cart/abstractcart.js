import Template from '../../utils/template';
import AbstractStep from '../abstractstep';
import { ready } from "../../utils/ready";
import RestProxy from "./rest";
import SiteProxy from "./site";
import {EventProxy} from "../../utils/eventproxy";

const YaPay = window.YaPay;

export default class AbstractCart extends AbstractStep {

	static defaults = {
		loaderTemplate: '<div class="bx-yapay-skeleton-loading width--#WIDTH#"></div>',
		loaderSelector: '.bx-yapay-skeleton-loading',
	}

	render(node, data) {
		this.isBootstrap = false;
		this.element = node;
		this.paymentButton = null;
		this.proxy = this.getOption('isRest')
			? new RestProxy(this)
			: new SiteProxy(this);
		this.paymentData = this.getPaymentData();

		this.bootSolution();
		this.insertLoader();
		this.setupPaymentCash();
		this.delayBootstrap();
	}

	compile(data) {
		return Template.compile(this.options.template, data);
	}

	restore(node) {
		this.element = node;
		this.restoreButton(node);
	}

	bootstrap() {
		this.isBootstrap = true;
		this.proxy.bootstrap();
	}

	bootSolution() {
		const solution = this.widget.getSolution();

		if (solution == null) { return; }

		solution.bootCart(this);
	}

	delayChangeBasket() {
		this.delay('changeBasket');
	}

	delayChangeOffer(productId) {
		this.delay('changeOffer', [productId]);
	}

	delayBootstrap() {
		ready(() => {
			this.delay('bootstrap');
		});
	}

	changeBasket() {
		if (!this.isBootstrap) { return; }
		this.proxy?.changeBasket();
	}

	changeOffer(newProductId) {
		if (this.isBootstrap) {
			this.proxy?.changeOffer(newProductId);
		} else {
			this.widget.setOptions({productId: newProductId});
		}
	}

	setupPaymentCash(){
		this.proxy?.setupPaymentCash();
	}

	getPaymentData() {
		return this.proxy.getPaymentData();
	}

	createPayment(node, paymentData) {
		this.proxy.createPayment(node, paymentData);
	}

	mountButton(node, payment) {
		this.paymentButton = payment.createButton({
			type: YaPay.ButtonType.Checkout,
			theme: this.getOption('buttonTheme') || YaPay.ButtonTheme.Black,
			width: this.getOption('buttonWidth') || YaPay.ButtonWidth.Auto,
		});

		this.paymentButton.mount(this.element);

		this.paymentButton.on(YaPay.ButtonEventType.Click, () => {
			payment.checkout();
		});
	}

	restoreButton(node) {
		this.proxy.restoreButton(node);
	}

	amountSum(amountA, amountB) {
		return (Number(amountA) + Number(amountB)).toFixed(2);
	}

	showError(type, message, err = null) {
		let notify = type + ' - ' + message;

		if (err) {
			notify += ' ' + err;
		}

		alert(notify);
	}

	insertLoader() {
		const width = this.getOption('buttonWidth') || YaPay.ButtonWidth.Auto;

		this.element.innerHTML = Template.compile(this.getOption('loaderTemplate'), {
			width: width.toLowerCase(),
			label: this.getOption('label'),
		});
	}

	removeLoader() {
		const loader = this.element.querySelector(this.getOption('loaderSelector'));

		if (loader == null) { return; }

		loader.remove();
	}
}