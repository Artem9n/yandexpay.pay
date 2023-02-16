import StepFactory from './step/factory';
import Loader from './step/loader';
import Page from "../solution/reference/page";
import NodePreserver from "./ui/nodepreserver";

export default class Widget {

	static defaults = {}

	defaults;
	options;
	loader;
	el;
	step;
	/** @var Page|null */
	solution;
	/** @var NodePreserver|null */
	preserver;

	/**
	 * @param {Object<Element>} element
	 * @param {Object} options
	 */
	constructor(element, options = {}) {
		this.defaults = Object.assign({}, this.constructor.defaults);
		this.options = {};
		this.el = element;

		this.setOptions(options);
	}

	boot() {
		this.bootSolution();
	}

	destroy() {
		this.destroyStep()
		this.destroySolution();
		this.destroyPreserver();
	}

	/**
	 * @param {Object} data
	 */
	payment(data) {
		this.go('payment', data);
	}

	/**
	 * @param {Object} data
	 */
	cart(data){
		this.go('cart', data);
	}

	restore(element) {
		this.el = element;
		this.step?.restore(element);
	}

	/**
	 * @param {string} type
	 * @param {Object} data
	 */
	go(type, data) {
		this.step = this.makeStep(type);
		this.step.render(this.el, data);
	}

	destroyStep() {
		this.step?.destroy();
	}

	bootLoader() {
		if (this.loader != null) { return; }
		this.loader = new Loader(this);
		this.loader.render(this.el);
	}

	removeLoader() {
		if (this.loader == null) { return; }
		this.loader.remove(this.el);
		this.loader = null;
	}

	/**
	 * @param {String} type
	 * @returns {Cart|Finish|Step3ds|Payment|Failure}
	 * @throws {Error}
	 */
	makeStep(type) {
		const options = this.getOption(type) || {};

		return StepFactory.make(type, this, options);
	}

	setPreserver(preserver: NodePreserver) : void {
		this.preserver = preserver;
	}

	destroyPreserver() : void {
		this.preserver?.destroy();
	}

	setSolution(solution: Page) : void {
		this.solution = solution;
	}

	getSolution() : Page {
		return this.solution;
	}

	bootSolution() {
		this.getSolution()?.bootWidget(this);
	}

	destroySolution() {
		this.getSolution()?.destroyWidget(this);
	}

	extendDefaults(options) {
		this.defaults = Object.assign(this.defaults, options);
	}

	setOptions(options) {
		this.options = Object.assign(this.options, options);
	}

	getOption(name) {
		return this.options[name] ?? this.defaults[name];
	}
}
