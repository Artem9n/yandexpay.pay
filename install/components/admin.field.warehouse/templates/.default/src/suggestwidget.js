export class SuggestWidget extends BX.ui.autoComplete {

	constructor(options) {
		super(options.widget);
		this.options = options;
	}

	downloadBundle(request, onLoad, onComplete, onError) {
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
						VALUE: geo,
						DISPLAY: text,
					};
				}

				onLoad.apply(ctx, [result]);
				onComplete.call(ctx);
			});
	}

	// invokes when user selects value
	selectItem = (value) => {
		super.selectItem(value);

		let myPlacemark = new ymaps.Placemark(value, {
			balloonContentHeader: 'test',
			balloonContent: 'test',
			balloonContentFooter: 'test'
		}, {
			preset: 'islands#blueDotIcon'
		});

		this.options.map.geoObjects.add(myPlacemark);
		this.options.map.setBounds(value, {checkZoomRange:true, zoomMargin:9});
	}

}
