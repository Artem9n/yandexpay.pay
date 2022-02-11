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

		let element;
		let variant = selector.trim();

		if (variant === '') { throw new Error('widget selector is empty'); }

		element = document.querySelector(variant);

		if (element != null) { return element; }

		return document.getElementById(variant) || document.getElementsByClassName(variant)[0];
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