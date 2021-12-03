import StepFactory from './step/factory';

export default class Widget {

	/**
	 * @type {{failureTemplate: string, modalTemplate: string, finishedTemplate: string}}
	 */
	defaults = {
		finishedTemplate:   '<div class="alert alert-success" role="alert"><strong>#MESSAGE#</strong></div>',
		failureTemplate:      '<div class="alert alert-danger" role="alert"><strong>#MESSAGE#</strong></div>',
		modalTemplate:      '<div class="yandex-pay-modal-inner">#IFRAME#</div>',
	}

	/**
	 * @param {Object<Element>} element
	 */
	constructor(element){
		this.el = element;
	}

	setOptions(options) {
		this.options = Object.assign({}, this.defaults, options);
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

	/**
	 * @param {string} type
	 * @param {Object} data
	 */
	go(type, data) {
		const step = this.makeStep(type);

		step.render(this.el, data);
	}

	/**
	 * @param {String} type
	 * @returns {Cart|Finish|Step3ds|Payment|Failure}
	 * @throws {Error}
	 */
	makeStep(type) {
		const step = StepFactory.make(type);

		step.setWidget(this);

		return step;
	}
}
