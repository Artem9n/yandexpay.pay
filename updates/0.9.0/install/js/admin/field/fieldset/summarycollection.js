(function(BX) {

	const Fieldset = BX.namespace('YandexPay.Field.Fieldset');

	Fieldset.SummaryCollection = Fieldset.Collection.extend({

		handleModalSave: function(childInstance, dir) {
			childInstance.$el[dir ? 'on' : 'off']('uiModalSave', $.proxy(this.onModalSave, this));
		},

		handleModalClose: function(childInstance, dir) {
			childInstance.$el[dir ? 'on' : 'off']('uiModalClose', $.proxy(this.onModalClose, this));
		},

		onItemAddClick: function(evt) {
			const instance = this.addItem();

			instance.initEdit();
			this.handleModalSave(instance, true);
			this.handleModalClose(instance, true);

			evt.preventDefault();
		},

		onModalSave: function(evt) {
			const target = evt.currentTarget;
			const instance = this.getItemInstance(target);

			this.handleModalSave(instance, false);
			this.handleModalClose(instance, false);
		},

		onModalClose: function(evt) {
			const target = evt.currentTarget;
			const instance = this.getItemInstance(target);

			this.handleModalSave(instance, false);
			this.handleModalClose(instance, false);

			this.deleteItem(instance.$el, true);
		},

		getItemAddButton: function() {
			return this.getElement('itemAdd');
		},

		getItemPlugin: function() {
			return Fieldset.Summary;
		}

	}, {
		dataName: 'FieldFieldsetSummaryCollection',
		pluginName: 'YandexPay.Field.Fieldset.SummaryCollection'
	});

})(BX);