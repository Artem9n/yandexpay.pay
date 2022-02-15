import Element from './element';
import Basket from '../eshopbootstrap/basket';
import Factory from '../reference/factory';

const factory = new Factory({
	element: Element,
	basket: Basket,
});

export {
	factory,
}
