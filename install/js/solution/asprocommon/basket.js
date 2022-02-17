import EshopBasket from '../eshopbootstrap/basket';
import factoryLayout from './molecules/factorylayout';

export default class Basket extends EshopBasket {

	bootFactory(factory) {
		factoryLayout(factory);
	}

}