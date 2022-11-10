(function(BX, $) {

	const Plugin = BX.namespace('YandexPay.Plugin');
	const Ui = BX.namespace('YandexPay.Ui');

	const constructor = Ui.RangeType = Plugin.Base.extend({
		defaults: {
			url: null,
			inputElement: 'input',
			spanElement: 'span',
		},

		initialize: function() {
			this.callParent('initialize', constructor);
			this.bind();
		},

		destroy: function() {
			this.unbind();
			this.callParent('destroy', constructor);
		},

		bind: function() {
			this.handleInput(true);
		},

		unbind: function() {
			this.handleInput(false);
		},

		handleInput: function(dir) {
			this.$el[dir ? 'on' : 'off']('input', $.proxy(this.onInput, this));
		},

		onInput: function(event) {
			let value = event.currentTarget.value;
			const span = this.getElement('span', this.$el, 'nextAll');
			span.html(value + ' px');
		},
	}, {
		dataName: 'UiRangeType'
	});

})(BX, jQuery);