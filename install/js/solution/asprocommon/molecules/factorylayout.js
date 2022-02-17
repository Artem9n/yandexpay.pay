import './divider.css';

export default (factory) => {
	factory.extendDefaults({
		template:
			'<div class="bx-yapay-divider width--#WIDTH#">' +
				'<span class="bx-yapay-divider__corner bx-yapay-divider__corner--left"></span>' +
				'<span class="bx-yapay-divider__text">#LABEL#</span>' +
				'<span class="bx-yapay-divider__corner bx-yapay-divider__corner--right"></span>' +
			'</div>'
			+ factory.getOption('template'),
	});
}