import Button from "./button";
import Widget from "./widget";

export default class Factory {

	/**
	 * @param {string} type
	 * @param {Factory} factory
	 * @param {Object} options
	 * @returns {Button|Widget}
	 * @throws {Error}
	 */
	static make(type, factory, options) {
		if (type === 'Button') {
			return new Button(factory, options);
		} else if (type === 'Widget') {
			return new Widget(factory, options);
		}

		throw new Error('unknown display ' + type);
	}

}