import Element from '../asprocommon/element';
import Basket from '../asprocommon/basket';
import Order from '../asprocommon/order';
import Factory from '../reference/factory';

import '../asprocommon/widget.css';

const factory = new Factory({
	element: Element,
	basket: Basket,
	order: Order,
});

export {
	factory,
}
