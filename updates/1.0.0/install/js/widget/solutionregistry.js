export default class SolutionRegistry {

	static pages = {};

	static getFactory(name) {
		if (name == null) { return null; }

		const factory = window?.BX?.YandexPay?.Solution?.[name]?.factory;

		if (factory == null) {
			console?.warn(`cant find solution ${name}`);
			return;
		}

		return factory;
	}

	static getPage(name, mode) {
		if (name == null || mode == null) { return null; }

		const key = name + ':' + mode;

		if (this.pages[key] == null) {
			this.pages[key] = this.createPage(name, mode);
		}

		return this.pages[key];
	}

	static createPage(name, mode) {
		const factory = this.getFactory(name);

		if (factory == null) { return null; }

		return factory.create(mode);
	}

}