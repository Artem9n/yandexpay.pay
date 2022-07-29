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
			center: [50, 50],
			zoom: 13,
		});

		return this._map;
	}

	bootSuggest() {
		const element = this.getElement('suggest');

		this._suggest = new Suggest(element, {

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