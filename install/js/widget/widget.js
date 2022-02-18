import StepFactory from './step/factory';
import SolutionRegistry from "./solutionregistry";

export default class Widget {

	static defaults = {}

	defaults;
	options;
	el;
	step;

	/**
	 * @param {Object<Element>} element
	 * @param {Object} options
	 */
	constructor(element, options = {}) {
		this.defaults = Object.assign({}, this.constructor.defaults);
		this.options = {};
		this.el = element;

		this.setOptions(options);
		this.bootSolution();
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

	/**
	 * @param {String} type
	 * @returns {Cart|Finish|Step3ds|Payment|Failure}
	 * @throws {Error}
	 */
	makeStep(type) {
		const options = this.getOption(type) || {};

		return StepFactory.make(type, this, options);
	}

	getSolution() {
		const name = this.getOption('solution');
		const mode = this.getOption('mode');

		return SolutionRegistry.getPage(name, mode);
	}

	bootSolution() {
		const solution = this.getSolution();

		if (solution == null) { return; }

		solution.bootWidget(this);
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
