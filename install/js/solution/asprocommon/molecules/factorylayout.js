import './divider.css';

export default (factory, options) => {

	const template = {
		template:
			'<div class="bx-yapay-divider-container width--#WIDTH#">' +
				'<div class="bx-yapay-divider width--#WIDTH#">' +
					'<span class="bx-yapay-divider__corner"></span>' +
					'<span class="bx-yapay-divider__text">#LABEL#</span>' +
					'<span class="bx-yapay-divider__corner at--right"></span>' +
				'</div>'
				+ factory.getOption('template') +
			'</div>',
	};

	factory.extendDefaults(Object.assign({}, options, template));
}