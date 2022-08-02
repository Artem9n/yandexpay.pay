import {SuggestWidget} from "./suggestwidget";

export class Suggest {

	static defaults = {

	};

	constructor(element: $, options: Object = {}) {
		this.$el = element;
		this.el = this.$el[0];
		this.options = Object.assign({}, this.constructor.defaults, options);

		this.initialize();
	}

	initialize() {
		this.widget = new SuggestWidget({
			widget: {
				scope: this.el
			},
			map: this.options.map
		});
	}

}