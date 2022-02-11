export default class Factory {

	classMap = {};

	constructor(classMap) {
		this.classMap = classMap;
	}

	create(cart) {
		const type = cart.getOption('mode');

		const className = this.classMap[type];

		if (className == null) { return null; }

		return new className(cart);
	}

}