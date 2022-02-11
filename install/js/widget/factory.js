import Utils from './utils/template';

export default class Factory {

	defaults = {
		template: '<div id="yandexpay" class="yandex-pay"></div>',
	}

	inject(selector, position) {
		return Promise.resolve()
			.then(() => this.waitElement(selector))
			.then((anchor) => {
				const element = this.renderElement(anchor, position);

				return this.install(element);
			});
	}

	install(element) {
		return new BX.YandexPay.Widget(element);
	}

	waitElement(selector) {
		return new Promise((resolve, reject) => {
			this.waitCount = 0;
			this.waitLimit = 10;
			this.waitElementLoop(selector, resolve, reject);
		});
	}

	waitElementLoop(selector, resolve, reject) {
		const anchor = this.findElement(selector);

		if (anchor) {
			resolve(anchor);
			return;
		}

		++this.waitCount;

		if (this.waitCount >= this.waitLimit) {
			reject('cant find element by selector ' + selector);
			return;
		}

		setTimeout(this.waitElementLoop.bind(this, selector, resolve, reject));
	}

	findElement(selector) {
		let elementList;
		let variant = selector.trim();
		let result;

		if (variant === '') { throw new Error('widget selector is empty'); }

		elementList = this.searchBySelector(variant) ?? this.searchById(selector) ?? this.searchByClassName(selector);

		if (elementList == null) { return null; }

		if (elementList.length > 1) {
			result = this.reduceVisible(elementList);
		}

		if (result == null) {
			result = elementList[0];
		}

		return result;
	}

	searchBySelector(selector) {
		try {
			const result = [];

			for (const part of selector.split(',')) { // first selector
				const partSanitized = part.trim();

				if (partSanitized === '') { continue; }

				const collection = document.querySelectorAll(partSanitized);

				for (const element of collection) {
					result.push(element);
				}
			}

			return result.length > 0 ? result : null;
		} catch (e) {
			return null;
		}
	}

	searchById(selector) {
		try {
			const element = document.getElementById(selector);

			return element != null ? [element] : null;
		} catch (e) {
			return null;
		}
	}

	searchByClassName(selector) {
		try {
			const collection = document.getElementsByClassName(selector);

			return collection.length > 0 ? collection : null;
		} catch (e) {
			return null;
		}
	}

	reduceVisible(collection) {
		let result = null;
		
		for (const element of collection) {
			if (this.testVisible(element)) {
				result = element;
				break;
			}
		}

		return result;
	}
	
	testVisible(element) {
		return (element.offsetWidth || element.offsetHeight || element.getClientRects().length );
	}

	isCssSelector(selector) {
		return /^[.#]/.test(selector);
	}

	renderElement(anchor, position) {
		const result = Utils.toElement(this.defaults.template);

		anchor.insertAdjacentElement(position, result);

		return result;
	}
}