(function(BX) {

	const Input = BX.namespace('YandexPay.Ui.Input.CalendarField');

	// class

	Input.CalendarMultiple = function() {
		Input.CalendarMultiple.superclass.constructor.apply(this, arguments);

		const originSetLayer = this._set_layer;

		this._set_layer = function() {
			originSetLayer.call(this);
			this._ymRedrawSelected();
		};

		this._ymRedrawSelected = function() {
			const values = this.params.values || [];

			for (const layerId in this._layers) {
				if (!this._layers.hasOwnProperty(layerId)) { continue; }

				const layer = this._layers[layerId];
				let selected;

				// unset selected

				do
				{
					selected = BX.findChild(layer, {
						tag: 'A',
						className: 'bx-calendar-active'
					}, true);

					selected && BX.removeClass(selected, 'bx-calendar-active');
				}
				while (selected);

				// apply values

				for (const value of values) {
					const searchDate = new Date(value.valueOf());

					searchDate.setUTCHours(0);
					searchDate.setUTCMinutes(0);
					searchDate.setUTCSeconds(0);
					searchDate.setUTCMilliseconds(0);

					const valueCell = BX.findChild(layer, {
						tag: 'A',
						attr: { 'data-date' : searchDate.valueOf() + '' }
					}, true);

					valueCell && BX.addClass(valueCell, 'bx-calendar-active');
				}
			}
		}
	};

	BX.extend(Input.CalendarMultiple, BX.JCCalendar);

	Input.CalendarMultiple.prototype.ymPassValues = function(values) {
		this.params.values = values;
		this._ymRedrawSelected();
	}

	// singleton

	let singleton;

	Input.getCalendarMultiple = function() {
		if (singleton == null) {
			singleton = new Input.CalendarMultiple();
		}

		return singleton;
	}

})(BX);