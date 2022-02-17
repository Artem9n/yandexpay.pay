export default class Factory {

	classMap = {};

	constructor(classMap) {
		this.classMap = classMap;
	}

	create(mode) {
		const className = this.classMap[mode];

		if (className == null) { return null; }

		return new className();
	}

}