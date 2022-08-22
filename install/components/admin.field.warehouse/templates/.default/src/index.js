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

		MapsLoader.getInstance().load(this.options.apiKey + '12321')
			.then(() => {
				this.bootMap();
				this.bootSuggest();
				this.showSavedPlacemark();
				this.focusSuggest();
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

	toggleDetails() : void {
		const $button = this.getElement('clarify');

		const newText = $button.data('alt');
		$button.data('alt', $button.html());
		$button.html(newText);

		const $details = this.getElement('details');
		const readonly = $details.hasClass('readonly');
		$details.toggleClass('readonly');
		$details.find('input').prop('readonly', !readonly);
	}

	openDetails() : void {
		const $details = this.getElement('details');
		if ($details.hasClass('readonly')) {
			this.toggleDetails();
		}
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

	showSavedPlacemark() : void {
		const lat = this.$el.find('input[data-name="LOCATION_LAT"]').val();
		const lon = this.$el.find('input[data-name="LOCATION_LON"]').val();

		if (!lat || !lon) {
			return;
		}

		this._suggest.drawPlacemark([lat, lon]);
		this._suggest.moveCenter([lat, lon]);
	}

	searchEnd = (response: Object) => {
		console.log(response);
	}

}
