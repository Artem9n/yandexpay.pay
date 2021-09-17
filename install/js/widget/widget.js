import StepFactory from './step/factory';

export class Widget {

	defaults = {
		finishedTemplate:   '<div class="alert alert-success" role="alert"><strong>#MESSAGE#</strong></div>',
		failureTemplate:      '<div class="alert alert-danger" role="alert"><strong>#MESSAGE#</strong></div>',
		modalTemplate:      '<div class="yandex-pay-modal-inner">#IFRAME#</div>',
	}

	constructor(element, options = {}) {
		this.el = element;
		this.options = Object.assign({}, this.defaults, options);
	}

	payment(data) {
		this.go('payment', data);
	}

	go(type, data) {
		const step = this.makeStep(type);

		step.render(this.el, data);
	}

	makeStep(type) {
		const step = StepFactory.make(type);

		step.setWidget(this);

		return step;
	}
}
