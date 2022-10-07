export class Intersection {

	static defaults = {};

	observer;

	constructor(element: HTMLElement, options: Object = {}) {
		this.el = element;
		this.options = Object.assign({}, this.constructor.defaults, options);
	}

	wait() : Promise {
		return new Promise((resolve) => {
			if (typeof IntersectionObserver === 'undefined') {
				resolve(this.el);
				return;
			}

			this.observer = new IntersectionObserver((entries, observer) => {
				entries.forEach((entry) => {
					if (entry.isIntersecting) {
						const element = entry.target;
						observer.unobserve(element);
						resolve(element);
					}
				});
			});

			this.observer.observe(this.el);
		});
	}

	restore(node: HTMLElement) : void {
		this.observer?.unobserve(this.el);
		this.el = node;
		this.observer?.observe(this.el);
	}
}