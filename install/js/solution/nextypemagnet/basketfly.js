import Basket from './basket';

export default class BasketFly extends Basket {

	bootFactory(factory) {
		factory.setOptions({
			preserve: {
				mutation: {
					anchor: '#bx_basketFKauiI',
					delay: 100,
				}
			},
		});
	}
}