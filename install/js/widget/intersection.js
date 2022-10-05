export class Intersection {

	static defaults = {};

	constructor(element: HTMLElement, options: Object = {}) {
		this.el = element;
		this.options = Object.assign({}, this.constructor.defaults, options);
	}

	wait() {
		return new Promise((resolve) => {
			if (typeof IntersectionObserver === 'undefined') {
				resolve(this.el);
				return;
			}

			const observer = new IntersectionObserver((entries, observer) => {
				entries.forEach((entry) => {
					if (entry.isIntersecting) {
						const element = entry.target;
						observer.unobserve(element);
						resolve(element);
					}
				});
			});

			observer.observe(this.el);
		});
	}
}