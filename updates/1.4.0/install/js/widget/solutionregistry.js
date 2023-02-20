export default class SolutionRegistry {

	static getFactory(name) {
		if (name == null) { return null; }

		let namespace, type;

		[namespace, type] = name.split('.');

		let factory = window?.BX?.YandexPay?.Solution?.[namespace]?.factory;

		if (type != null)
		{
			factory = window?.BX?.YandexPay?.Solution?.[namespace]?.[type]?.factory;
		}

		if (factory == null) {
			console?.warn(`cant find solution ${name}`);
			return;
		}

		return factory;
	}

	static createPage(name, mode) {
		const factory = this.getFactory(name);

		if (factory == null) { return null; }

		return factory.create(mode);
	}

}