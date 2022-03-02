import Element from '../asprocommon/element';
import ElementFast from "../asprocommon/elementfast";
import Basket from '../asprocommon/basket';
import Order from '../asprocommon/order';
import Factory from '../reference/factory';

import '../asprocommon/widget.css';

const factory = new Factory({
	element: Element,
	elementFast: ElementFast,
	basket: Basket,
	order: Order,
});

export {
	factory,
}
