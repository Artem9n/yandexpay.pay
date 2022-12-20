import SolutionRegistry from './solutionregistry';
import NodePreserver from "./ui/nodepreserver";
import Utils from './utils/template';
import {EventProxy} from "./utils/eventproxy";
import {Sdkloader} from "./sdkloader";
import {Intersection} from "./intersection";
import Display from "./ui/display/factory";

export default class Factory {

	static defaults = {
		solution: null,
		template:
			'<div id="#ID#" ' +
				'class="bx-yapay-drawer-container ' +
				'yapay-behavior--#MODE# ' +
				'yapay-display--#DISPLAY# ' +
				'yapay-width--#WIDTH# ' +
				'yapay-solution--#SOLUTION#">' +
				'#STYLE#' +
				'#DIVIDER#' +
				'<div class="bx-yapay-drawer"></div>' +
			'</div>',
		divider:
			'<div class="bx-yapay-divider"> ' +
				'<span class="bx-yapay-divider__corner"></span> ' +
				'<span class="bx-yapay-divider__text">#LABEL#</span> ' +
				'<span class="bx-yapay-divider__corner at--right"></span> ' +
			'</div>',
		useDivider: false,
		containerSelector: '.bx-yapay-drawer',
		loaderSelector: '.bx-yapay-skeleton-loading',
		preserve: {
			composite: true,
		},
		waitLimit: 30,
		waitTimeout: 1000,
	}

	defaults;
	options;
	waitCount = 0;

	constructor(options = {}) {
		this.defaults = Object.assign({}, this.constructor.defaults);
		this.options = {};

		this.setOptions(options);
		this.bootSolution();
		this.bootLocal();
	}

	inject(selector, position) {
		return Promise.resolve()
			.then(() => this.waitElement(selector))
			.then((anchor) => this.checkElement(anchor))
			.then((anchor) => this.renderElement(anchor, position))
			.then((element) => this.install(element))
			.then((widget) => this.insertLoader(widget))
			.then((widget) => {
				const intersection = new Intersection(widget.el);
				
				if (this.getOption('preserve')) {
					this.preserve(selector, position, widget, intersection);
				}

				return intersection.wait().then(() => widget);
			})
			.then((widget) => Sdkloader.getInstance().load().then(() => widget));
	}

	checkElement(anchor) {
		const selector = this.containerSelector();
		const contains = (
			!!anchor.querySelector(selector)
			|| this.containsSiblingElement(anchor, selector)
		);

		if (contains) {
			throw new Error('the element already has a container');
		}

		return anchor;
	}

	containerSelector() {
		return '#' + this.getOption('containerId') + ' ' + this.getOption('containerSelector');
	}

	containsSiblingElement(anchor, selector) {
		let result = false;
		let next = anchor.parentElement?.firstElementChild;

		while (next) {
			if (next.matches(selector) || next.querySelector(selector)) {
				result = true;
				break;
			}

			next = next.nextElementSibling;
		}

		return result;
	}
	
	preserve(selector, position, widget, intersection) {
		const preserver = new NodePreserver(widget.el, Object.assign({}, this.preserveOptions(), {
			restore: () => {
				preserver.destroy();
				// noinspection JSIgnoredPromiseFromCall
				this.restore(selector, position, widget, intersection);
			},
		}));
	}

	preserveOptions() {
		const preserveOption = this.getOption('preserve');

		return typeof preserveOption === 'object' ? preserveOption : {};
	}

	restore(selector, position, widget, intersection) {
		return Promise.resolve()
			.then(() => this.waitElement(selector))
			.then((anchor) => {
				const element = this.renderElement(anchor, position);

				widget.restore(element);
				intersection?.restore(element);

				if (this.getOption('preserve')) {
					this.preserve(selector, position, widget, intersection);
				}

				return widget;
			});
	}

	install(element) {
		return new BX.YandexPay.Widget(element);
	}

	insertLoader(widget) {
		widget.go('loader', {
			label: this.getOption('label'),
		});

		return widget;
	}

	waitElement(selector) {
		return new Promise((resolve, reject) => {
			this.waitCount = 0;
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

		if (this.waitCount >= this.getOption('waitLimit')) {
			reject('cant find element by selector ' + selector);
			return;
		}

		setTimeout(this.waitElementLoop.bind(this, selector, resolve, reject), this.getOption('waitTimeout'));
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

				if (partSanitized === '' || !this.isCssSelector(partSanitized)) { continue; }

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
		const selector = this.containerSelector();
		const divider = this.getDivider();
		const display = this.getDisplay();

		const html = Utils.compile(this.getOption('template'), {
			divider: divider,
			style: display != null ? display.style() : '',
			width: display != null ? display.width().toLowerCase() : 'auto',
			id: this.getOption('containerId'),
			mode: this.getOption('mode') || 'payment',
			display: this.getOption('displayType')?.toLowerCase() || 'button',
			solution: this.getOption('solution')?.toLowerCase(),
		});

		let elements = Utils.toElements(html);
		let result = null;

		if (position.indexOf('after') === 0) { elements = elements.reverse(); }

		for (const element of elements) {
			anchor.insertAdjacentElement(position, element);

			if (result != null) { continue; }

			result = element.matches(selector) ? element : element.querySelector(selector);
		}

		if (result == null) {
			throw new Error(`cant find template container by selector ${selector}`);
		}

		return result;
	}

	bootSolution() {
		const name = this.getOption('solution');
		const mode = this.getOption('mode');
		const solution = SolutionRegistry.getPage(name, mode);

		if (solution == null) { return; }

		solution.bootFactory(this);
	}

	getDisplay() {
		const type = this.getOption('displayType');
		const options = this.getOption('displayParameters');

		if (type == null) { return null; }

		return Display.make(type, this, options);
	}

	getDivider() {
		return this.getOption('useDivider')
			? Utils.compile(this.getOption('divider'), {label: this.getOption('label')})
			: '';
	}

	bootLocal() {
		EventProxy.make().fire('bxYapayFactoryInit', {
			factory: this,
		});
	}

	extendDefaults(options) {
		this.defaults = Object.assign(this.defaults, options);
	}

	setOptions(options) {
		this.options = Object.assign(this.options, options);
	}

	getOption(name) {
		return this.options[name] ?? this.defaults[name];
	}
}