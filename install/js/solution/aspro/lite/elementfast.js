import Element from './element';

export default class ElementFast extends Element {

	bootFactory(factory) {

		super.bootFactory(factory);

		factory.setOptions({
			event: 'bxYapayFastViewInit',
			eventConfig: {
				strict: true,
				plain: true,
			},
			preserve: {
				mutation: {
					anchor: '.catalog-detail__cart',
					delay: 100,
				}
			},
		});

		let timer = setInterval(function(){
			if($('.fast_view_frame.popup .jqmClose.top-close').css('z-index') == 2) {
				clearInterval(timer);
				setTimeout(function() {
					document.dispatchEvent(new CustomEvent('bxYapayFastViewInit'));
				}, 250);
			}
		}, 100);
	}

}