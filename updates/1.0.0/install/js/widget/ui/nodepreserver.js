import MutationFactory from "./nodepreserver/mutationfactory";
import Subscriber from "./nodepreserver/subscriber";

export default class NodePreserver {

	static defaults = {
		restore: null,
		subscriber: null,
		mutation: true,
	}

	/** @var MutationSkeleton */
	mutation;
	/** @var Subscriber */
	subscriber;

	constructor(element, options = {}) {
		this.el = element;
		this.options = Object.assign({}, this.constructor.defaults, options);

		this.install();
	}

	destroy() {
		this.uninstall();
		this.options = {};
		this.el = null;
	}

	install() {
		this.installMutation();
		this.installSubscriber();
	}

	uninstall() {
		this.uninstallMutation();
		this.uninstallSubscriber();
	}

	installMutation() {
		if (!this.isEnabled('mutation')) { return; }

		this.mutation = MutationFactory.make(this.el, this.driverOptions('mutation'));
	}

	uninstallMutation() {
		if (this.mutation == null) { return; }

		this.mutation.destroy();
	}

	installSubscriber() {
		if (!this.isEnabled('subscriber')) { return; }

		this.subscriber = new Subscriber(this.el, this.driverOptions('subscriber'));
	}

	uninstallSubscriber() {
		if (this.subscriber == null) { return; }

		this.subscriber.destroy();
		this.subscriber = null;
	}

	isEnabled(type) {
		return !!this.options[type];
	}

	driverOptions(type) {
		const option = typeof this.options[type] === 'object' ? this.options[type] : {};
		const overrides = {
			check: this.check,
		};

		return Object.assign({}, option, overrides);
	}

	check = () => {
		const found = document.body.contains(this.el);

		if (!found) {
			this.options.restore();
		}

		return found;
	}
}