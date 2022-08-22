import {SuggestWidget} from "./suggestwidget";

export class Suggest {

	static defaults = {

	};

	_itemsData = {};

	constructor(element: $, options: Object = {}) {
		this.$el = element;
		this.el = this.$el[0];
		this.options = Object.assign({}, this.constructor.defaults, options);

		this.initialize();
	}

	initialize() {
		this.bootWidget();
		this.bind();
	}

	bind() {
		this.handleMapDoubleClick(true);
	}

	handleMapDoubleClick(dir: boolean) : void {
		this.options.map.events[dir ? 'add' : 'remove']("dblclick", this.onMapDoubleClick);
	}

	onMapDoubleClick = (e) => {
		const coords = e.get('coords');

		this.drawPlacemark(coords);
		this.fillCoordinates(coords);

		ymaps.geocode(coords)
			.then((response) => {
				const geoObject = response.geoObjects.get(0);
				const title = this.geoObjectTitle(geoObject);
				const address = this.geoObjectAddress(geoObject);

				this.fillSuggest(title);
				this.fillAddress(address);
			});
	}

	bootWidget() {
		this.widget = new SuggestWidget({
			widget: {
				scope: this.el,
			},
			onSelect: this.onSuggestSelect,
			search: this.onSuggestSearch
		});
	}

	onSuggestSelect = (value) => {
		if (this._itemsData[value] == null) {
			throw new Error('cant find associated data for item');
		}

		const data = this._itemsData[value];

		this.fillAddress(data.address);
		this.fillCoordinates(data.coordinates);
		this.drawPlacemark(data.coordinates);
		this.moveCenter(data.coordinates);
	}

	onSuggestSearch = (request, onLoad, onComplete, onError) => {

		this.widget.showLoading();

		ymaps.geocode(request['QUERY'])
			.then((response) => {
				const result = [];

				this._itemsData = {};

				for (let i = 0; i < response.geoObjects.getLength(); i++) {
					const geoObject = response.geoObjects.get(i);
					const title = this.geoObjectTitle(geoObject);

					result.push({
						VALUE: title,
						DISPLAY: title,
					});

					this._itemsData[title] = {
						coordinates: this.geoObjectCoordinates(geoObject),
						address: this.geoObjectAddress(geoObject),
					};
				}

				this.widget.hideLoading();
				onLoad.apply(this.widget, [result]);
				onComplete.call(this.widget);
			})
			.catch((e) => {
				this.widget.hideLoading();
				this.widget.showError('', false, e);
				onComplete && onComplete.call(this.widget);
				onError && onError.call(this.widget);
			});
	}

	geoObjectCoordinates(geoObject) : Array {
		return geoObject.geometry.getCoordinates();
	}

	geoObjectTitle(geoObject) : string {
		return geoObject.properties.get('metaDataProperty').GeocoderMetaData.text;
	}

	geoObjectAddress(geoObject) : Object {
		const address = geoObject.properties.get('metaDataProperty').GeocoderMetaData.Address.Components;
		const map = {
			country: 'COUNTRY',
			street: 'STREET',
			district: 'STREET',
			house: 'BUILDING',
			locality: 'LOCALITY',
		};
		const result = {};

		for (let i = 0; i < address.length; i++) {
			const part = address[i];
			const target = map[part.kind];

			if (target == null) { continue; }

			result[target] = part.name;
		}

		return result;
	}

	fillSuggest(text: string) : void {
		this.$el.find('input[data-name="FULL_ADDRESS"]').val(text);
	}

	fillAddress(address: Object) : void {
		for (const [type, value] of Object.entries(address)) {
			this.options.details.find(`input[data-name="${type}"]`).val(value);
		}
	}

	fillCoordinates(coordinates: Array) : void {
		this.options.details.find('input[data-name="LOCATION_LAT"]').val(coordinates[0]);
		this.options.details.find('input[data-name="LOCATION_LON"]').val(coordinates[1]);
	}

	drawPlacemark(coordinates: Array) : void {
		this.options.map.geoObjects.removeAll();
		this.options.map.geoObjects.add(new ymaps.Placemark(coordinates));
	}

	moveCenter(coordinates: Array) : void {
		this.options.map.setCenter(coordinates, 16);
	}
}