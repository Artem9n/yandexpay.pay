import {MapsLoader} from "./mapsLoader";
import {Suggest} from "./suggest";

export class Warehouse extends BX.YandexPay.Plugin.Base {

	static defaults = {
		mapElement: '.js-field-warehouse__map',
		suggestElement: '.js-field-warehouse__suggest',
		apiKey: null,
	};

	static dataName = 'uiFieldWarehouse';

	initialize() {
		super.initialize();

		MapsLoader.getInstance().load(this.options.apiKey)
			.then(() => {
				this.bootMap();
				this.bootSuggest();
			});
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
