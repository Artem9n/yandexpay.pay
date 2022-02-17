import Element from '../asprocommon/element';
import Basket from '../asprocommon/basket';
import Factory from '../reference/factory';

import '../asprocommon/widget.css';

const factory = new Factory({
	element: Element,
	basket: Basket,
});

export {
	factory,
}
