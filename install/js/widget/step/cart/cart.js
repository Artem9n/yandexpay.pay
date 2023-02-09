import AbstractStep from '../abstractstep';
import { ready } from "../../utils/ready";
import Display from "../../ui/display/factory";
import {EventProxy} from "../../utils/eventproxy";
import Rest from "./rest";
import Site from "./site";

export default class Cart extends AbstractStep {

	isBootstrap = false;
	element;
	display;
	initialContent;

	destroy() {
		const solution = this.widget.getSolution();

		if (solution == null) { return; }

		solution.destroyCart(this);
	}

	render(node, data) {
		this.element = node;
		this.display = this.getDisplay();
		this.initialContent = this.element.innerHTML;

		this.bootProxy();
		this.bootSolution();
		this.bootLocal();
		this.delayBootstrap();
	}

	bootProxy() : Rest|Site{
		this.proxy = this.isRest() ? new Rest(this) : new Site(this);
	}

	restore(node) {
		if (this.initialContent != null) {
			node.innerHTML = this.initialContent;
		}

		this.element = node;
		this.proxy.restore(node);
	}

	bootstrap() {
		console.log('boot - ' + this.isBootstrap);
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
		console.log('offer - ' + this.isBootstrap);

		if (this.isBootstrap) {
			this.proxy?.changeOffer(newProductId);
		} else {
			this.widget.setOptions({productId: newProductId});
		}
	}

	getDisplay() {
		const type = this.getOption('displayType');
		const options = this.getOption('displayParameters');
		return Display.make(type, this, options);
	}
}