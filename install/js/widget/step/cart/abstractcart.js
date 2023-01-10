import Template from '../../utils/template';
import AbstractStep from '../abstractstep';
import { ready } from "../../utils/ready";
import RestProxy from "./rest";
import SiteProxy from "./site";
import Display from "../../ui/display/factory";
import {EventProxy} from "../../utils/eventproxy";

export default class AbstractCart extends AbstractStep {

	static defaults = {
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
		this.display = this.getDisplay();
		this.initialContent = this.element.innerHTML;

		this.bootSolution();
		this.bootLocal();
		this.setupPaymentCash();
		this.delayBootstrap();
	}

	compile(data) {
		return Template.compile(this.options.template, data);
	}

	restore(node) {
		if (this.initialContent != null) {
			node.innerHTML = this.initialContent;
		}

		this.element = node;
		this.proxy.restoreButton(node);
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

	bootLocal() {
		EventProxy.make().fire('bxYapayCartInit', {
			cart: this,
		});
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

	getDisplay() {
		const type = this.getOption('displayType');
		const options = this.getOption('displayParameters');
		return Display.make(type, this, options);
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

	removeLoader() {
		const loader = this.element.querySelector(this.getOption('loaderSelector'));

		loader?.remove();
		this.initialContent = null;
	}
}