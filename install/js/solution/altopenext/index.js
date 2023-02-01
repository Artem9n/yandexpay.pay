import Element from './element';
import Basket from './basket';
import BasketFly from "./basketfly";
import Order from './order';
import Factory from '../reference/factory';

import './widget.css';

const factory = new Factory({
	element: Element,
	basket: Basket,
	basketFly: BasketFly,
	order: Order,
});

export {
	factory,
}
