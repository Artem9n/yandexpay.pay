import Secure3d from './secure3d';
import Finish from './finish';
import Failure from './failure';
import Loader from './loader';
import Divider from './divider';
import Cart from './cart/cart';
import Payment from './payment/payment';

export default class Factory {

	/**
	 * @param {string} type
	 * @param {Widget} widget
	 * @param {Object} options
	 * @returns {Cart|Finish|Step3ds|Payment|Failure}
	 * @throws {Error}
	 */
	static make(type, widget, options = {}) {
		if (type === '3ds') {
			return new Secure3d(widget, options);
		} else if (type === 'finished') {
			return new Finish(widget, options);
		} else if (type === 'error') {
			return new Failure(widget, options);
		} else if (type === 'payment') {
			return new Payment(widget, options);
		} else if (type === 'cart') {
			return new Cart(widget, options);
		} else if (type === 'loader') {
			return new Loader(widget, options);
		}

		throw new Error('unknown step ' + type);
	}

}