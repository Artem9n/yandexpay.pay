import {MapsLoader} from "./mapsLoader";
import {Suggest} from "./suggest";

import "./index.css";

export class Warehouse extends BX.YandexPay.Plugin.Base {

	static defaults = {
		mapElement: '.js-field-warehouse__map',
		suggestElement: '.js-field-warehouse__suggest',
		clarifyElement: '.js-field-warehouse__clarify',
		detailsElement: '.js-field-warehouse__details',
		dialogElement: '.bx-core-adm-dialog-content',
		apiKey: null,
	};

	static dataName = 'uiFieldWarehouse';

	constructor(...args) {
		super(...args);
		this.postInitialize();
	}

	destroy() {
		this.unbind();
		super.destroy();
	}

	postInitialize() {
		this.bind();

		MapsLoader.getInstance().load(this.options.apiKey)
			.then(() => {
				this.bootMap();
				this.bootSuggest();
			});
	}

	bind() {
		this.handleClarifyClick(true);
		this.handleDialogResize(true);
	}

	unbind() {
		this.handleClarifyClick(false);
		this.handleDialogResize(false);
	}

	handleClarifyClick(dir: boolean) : void {
		this.getElement('clarify')[dir ? 'on' : 'off']('click', this.onClarifyClick);
	}

	handleDialogResize(dir: boolean) : void {
		BX[dir ? 'addCustomEvent' : 'removeCustomEvent']('onWindowResize', this.onDialogResize);
	}

	onDialogResize = () => {
		const dialog = this.$el.closest(this.options.dialogElement);

		if (dialog.length === 0) { return; }

		this.$el.width(dialog.width() - 1);
		this.$el.height(dialog.height() - 1);

		this._map.container.fitToViewport();
	}

	onClarifyClick = () => {
		this.toggleDetails();
	}

	toggleDetails() {
		const $button = this.getElement('clarify');

		const newText = $button.data('alt');
		$button.data('alt', $button.html());
		$button.html(newText);

		const $fields = this.getElement('details').find('input');
		const isReadonly = $fields.prop('readonly');
		$fields.prop('readonly', !isReadonly);
	}

	bootMap() {
		const element = this.getElement('map');

		this._map = new ymaps.Map(element[0], {
			center: [55.76, 37.64],
			controls: ['zoomControl'],
			behaviors: ['drag', 'scrollZoom', 'multiTouch'],
			zoom: 10
		}, {
			yandexMapDisablePoiInteractivity: true,
		});

		return this._map;
	}

	bootSuggest() {
		const element = this.getElement('suggest');

		this._suggest = new Suggest(element, {
			map: this._map,
			details: this.getElement('details')
		});
	}

	search() {
		const suggest = this.getElement('suggest');
		const query = suggest.val();

		this._mapLibrary.geocode(query).then(
			this.searchEnd,
			this.searchStop
		);
	}

	searchStop = (error: Error) => {
		// todo handle error
	}

	searchEnd = (response: Object) => {
		console.log(response);
	}

}
