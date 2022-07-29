import {SuggestWidget} from "./suggestwidget";

export class Suggest {

	static defaults = {

	};

	constructor(element: $, options: Object = {}) {
		this.$el = element;
		this.el = this.$el[0];
		this.options = Object.assign({}, this.constructor.defaults, {});

		this.initialize();
	}

	initialize() {
		console.log(SuggestWidget);

		this.widget = new SuggestWidget({
			scope: this.el,
		});
	}

}