import Secure3d from './secure3d';
import Finish from './finish';
import Failure from './failure';
import Payment from './payment';
import Cart from "./cart";

export default class Factory {

	static make(type) {
		if (type === '3ds') {
			return new Secure3d();
		} else if (type === 'finished') {
			return new Finish();
		} else if (type === 'error') {
			return new Failure();
		} else if (type === 'payment') {
			return new Payment();
		} else if (type === 'cart'){
			return new Cart();
		}

		throw new Error('unknown step ' + type);
	}

}