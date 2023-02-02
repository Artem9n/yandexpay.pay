import Element from './element';
import ElementFast from "./elementfast";
import Basket from './basket';
import Order from './order';
import Factory from '../reference/factory';

import './widget.css';

const factory = new Factory({
	element: Element,
	elementFast: ElementFast,
	basket: Basket,
	order: Order,
});

export {
	factory,
}
