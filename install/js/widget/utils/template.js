export default class Template {

	/**
	 * @param {string=} template
	 * @param {Object} vars
	 * @returns {string}
	 */
	static compile(template, vars) {
		let key;
		let replaceKey;
		let replaceValue;
		let result = template;

		for (key in vars) {
			if (!vars.hasOwnProperty(key)) { continue; }

			replaceKey = '#' + key.toUpperCase() + '#';
			replaceValue = vars[key];

			do {
				result = result.replace(replaceKey, replaceValue);
			} while (result.indexOf(replaceKey) !== -1);
		}

		return result;
	}

	static toElement(html) {
		const context = document.createElement('div');
		context.innerHTML = html;

		return context.firstElementChild;
	}

	static toElements(html) {
		const context = document.createElement('div');
		context.innerHTML = html;

		return [...context.children];
	}

}