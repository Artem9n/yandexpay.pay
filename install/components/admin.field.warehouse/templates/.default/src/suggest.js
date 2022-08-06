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
			onSelect: this.onSelect,
			search: this.search
		});


		this.options.map.events.add("dblclick", (e) => {

			if (this.placemark != null)
			{
				this.options.map.geoObjects.remove(this.placemark);
			}

			let coords = e.get('coords');

			ymaps.geocode(coords)
				.then((response) => {
					let text = response.geoObjects.get(0).properties.get('metaDataProperty').GeocoderMetaData.text;
					$('[data-name="WAREHOUSE"]').val(text);
				});

			this.onSelect(coords);
		});
	}

	onSelect = (value) => {

		ymaps.geocode(value).then((res) => {

			let geoObj = res.geoObjects.get(0);
			let address = geoObj.properties.get('metaDataProperty').GeocoderMetaData.Address.Components;
			let coordinates = geoObj.geometry.getCoordinates();
			let bounds = geoObj.properties.get('boundedBy');

			for (let i = 0; i < address.length; i++)
			{
				let test = address[i];

				if (test.kind === 'country')
				{
					$('input[name*="COUNTRY"]').val(test.name);
				}

				if (test.kind === 'street' || test.kind === 'district')
				{
					$('input[name*="STREET"]').val(test.name);
				}

				if (test.kind === 'house')
				{
					$('input[name*="BUILDING"]').val(test.name);
				}

				if (test.kind === 'locality')
				{
					$('input[name*="LOCALITY"]').val(test.name);
				}
			}

			$('input[name*="LOCATION_LAT"]').val(coordinates[0]);
			$('input[name*="LOCATION_LON"]').val(coordinates[1]);

			if (this.placemark != null)
			{
				this.options.map.geoObjects.remove(this.placemark);
			}

			this.placemark = new ymaps.Placemark(coordinates, {
				balloonContentHeader: 'test',
			}, {
				preset: 'islands#blueDotIcon'
			});

			this.options.map.geoObjects.add(this.placemark);
			this.options.map.setBounds(coordinates, {checkZoomRange:true, zoomMargin:5});
		});
	}

	search(request, onLoad, onComplete, onError){
		ymaps.geocode(request['QUERY'])
			.then((response) => {

				let so = this.opts, sv = this.vars, sc = this.ctrls, ctx = this;

				sv.loader.show();

				let len = response.geoObjects.getLength();
				let text, geo ,result = [];

				for (let i = 0; i < len; i++) {

					text = response.geoObjects.get(i).properties.get('metaDataProperty').GeocoderMetaData.text;
					geo = response.geoObjects.get(i).properties.get('boundedBy');

					result[i] = {
						VALUE: text,
						DISPLAY: text,
					};
				}

				onLoad.apply(ctx, [result]);
				onComplete.call(ctx);
			});
	}

}