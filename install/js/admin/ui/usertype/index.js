(function(BX, $) {

	const Plugin = BX.namespace('YandexPay.Plugin');
	const Ui = BX.namespace('YandexPay.Ui');

	const constructor = Ui.UserType = Plugin.Base.extend({

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
			this.handleClick(true);
			this.handleChange(true);
		},

		unbind: function() {
			this.handleClick(false);
			this.handleChange(false);
		},

		handleClick: function(dir) {
			this.$el[dir ? 'on' : 'off']('click', $.proxy(this.onClick, this));
		},

		handleChange: function(dir) {
			const input = this.getElement('input', this.$el, 'prevAll');
			input[dir ? 'on' : 'off']('keyup', $.proxy(this.onChange, this));
		},

		onChange: function(event) {
			let value = event.currentTarget.value;

			fetch(`/bitrix/admin/get_user.php?ID=${value}&ajax=Y&admin_section=Y`, {
				method: 'GET',
			})
				.then(response => response.json())
				.then(result => {
					const span = this.getElement('span', this.$el, 'nextAll');

					if (result.NAME !== '') {
						const link = `[<a target="_blank" class="tablebodylink" href="/bitrix/admin/user_edit.php?ID=${result.ID}&lang=ru">${result.ID}</a>] `
							+ result.NAME;
						span.html(link);
					} else {
						span.html('');
					}
				})
				.catch(error => console.log(error) );
		},

		onClick: function() {
			const name = this.defineGlobalCallback();

			this._modal = window.open(
				this.options.url + '&JSFUNC=' + name.replace('SUV', ''),
				'',
				'scrollbars=yes,resizable=yes,width=760,height=560,top='+Math.floor((screen.height - 560)/2-14)+',left='+Math.floor((screen.width - 760)/2-5)
			);
		},

		defineGlobalCallback: function() {
			const name = this.globalCallbackName();

			window[name] = $.proxy(this.onSelectUser, this);

			return name;
		},

		globalCallbackName: function() {
			return 'SUVYandexPayPassUserId';
		},

		onSelectUser: function(userId) {
			this.selectUserId(userId);
			this.closeModal();
		},

		selectUserId: function(userId) {
			const input = this.getElement('input', this.$el, 'prevAll');

			input.val(userId).trigger('keyup');
		},

		closeModal: function() {
			if (this._modal == null) { return; }

			this._modal.close();
			this._modal = null;
		}

	}, {
		dataName: 'UiUserType'
	});

})(BX, jQuery);