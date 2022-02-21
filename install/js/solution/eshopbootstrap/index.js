import Element from './element';
import Basket from './basket';
import Order from './order';
import Factory from '../reference/factory';

const factory = new Factory({
	element: Element,
	basket: Basket,
	order: Order,
});

export {
	factory,
}
