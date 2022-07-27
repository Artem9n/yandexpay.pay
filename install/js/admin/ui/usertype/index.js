(function(BX, $) {

	const Plugin = BX.namespace('YandexPay.Plugin');
	const Ui = BX.namespace('YandexPay.Ui');

	const constructor = Ui.UserType = Plugin.Base.extend({

		defaults: {
			url: null,
			inputElement: 'input',
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
			this.handleClick(true);
		},

		unbind: function() {
			this.handleClick(false);
		},

		handleClick: function(dir) {
			this.$el[dir ? 'on' : 'off']('click', $.proxy(this.onClick, this));
		},

		onClick: function() {
			const name = this.defineGlobalCallback();

			window.open(
				this.options.url + '&JSFUNC=' + name.replace('SUV', ''),
				'',
				'scrollbars=yes,resizable=yes,width=760,height=560,top='+Math.floor((screen.height - 560)/2-14)+',left='+Math.floor((screen.width - 760)/2-5)
			);
		},

		defineGlobalCallback: function() {
			const name = this.globalCallbackName();

			window[name] = $.proxy(this.selectUserId, this);

			return name;
		},

		globalCallbackName: function() {
			return 'SUVYandexPayPassUserId';
		},

		selectUserId: function(userId) {
			const input = this.getElement('input', this.$el, 'prevAll');

			input.val(userId);
		}

	}, {
		dataName: 'UiUserType'
	});

})(BX, jQuery);