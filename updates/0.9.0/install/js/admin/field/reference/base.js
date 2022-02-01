(function(BX, $) {

	var Plugin = BX.namespace('YandexPay.Plugin');
	var Reference = BX.namespace('YandexPay.Field.Reference');

	var constructor = Reference.Base = Plugin.Base.extend({

		defaults: {
			baseName: null,

			inputElement: null,
		},

		initVars: function() {
			this.callParent('initVars', constructor);

			this._baseName = null;
			this._index = null;
			this._parentField = null;
		},

		destroy: function() {
			this._parentField = null;
			this.callParent('destroy', constructor);
		},

		cloneInstance: function(newInstance) {
			var valueList = this.getValue();
			var index = this.getIndex();
			var baseName = this.getBaseName();

			newInstance.setIndex(index);
			newInstance.setBaseName(baseName);
			newInstance.setValue(valueList);
		},

		initEdit: function() {
			var visibleInput = this.getElement('input').not('[type="hidden"]');
			var result = false;

			if (visibleInput.length > 0) {
				result = true;
				visibleInput.eq(0).focus();
			}

			return result;
		},

		clear: function() {
			var inputList = this.getElement('input').not('.is--persistent');
			var input;
			var tagName;
			var optionList;
			var i;

			for (i = inputList.length - 1; i >= 0; i--) {
				input = inputList[i];
				tagName = this.getInputTagName(input);

				switch (tagName) {
					case 'select':
						optionList = $('option', input);

						optionList.prop('selected', false);
						optionList.filter(function(index, element) {
							return element.getAttribute('data-default') === 'true';
						}).prop('selected', true);
					break;

					case 'checkbox':
					case 'radio':
						input.checked = false;
					break;

					case 'input':
					case 'textarea':
						input.value = '';
					break;

					default:
						input.textContent = '';
					break;
				}
			}

			inputList.trigger('change');
		},

		setBaseName: function(baseName) {
			this._baseName = baseName;
		},

		getBaseName: function() {
			return this._baseName != null ? this._baseName : this.options.baseName;
		},

		setIndex: function(index) {
			this._index = index;
		},

		getIndex: function() {
			return this._index != null ? this._index : this.options.index;
		},

		getName: function() {
			return this.options.name;
		},

		updateName: function() {
			var baseName = this.getBaseName();
			var inputList = this.getElement('input');
			var input;
			var inputName;
			var isMultiple;
			var i;
			var fullName;

			for (i = 0; i < inputList.length; i++) {
				input = inputList.eq(i);
				inputName = input.data('name');
				isMultiple = !!input.prop('multiple');
				fullName = baseName;

				if (inputName.indexOf('[') !== -1) {
					fullName += inputName;
				} else {
					fullName += '[' + inputName + ']';
				}

				if (isMultiple || this.isGroupInput(input)) {
					fullName += '[]';
				}

				input.attr('name', fullName);
			}
		},

		unsetName: function() {
			this.getElement('input').removeAttr('name');
		},

		getValue: function() {
			var result = {};
			var inputList = this.getElement('input');
			var input;
			var inputName;
			var inputTag;
			var inputValue;
			var i;
			var selectedOption;

			for (i = 0; i < inputList.length; i++) {
				input = inputList.eq(i);
				inputTag = this.getInputTagName(input);
				inputName = input.data('name');
				inputValue = '';

				if (inputTag === 'select') {
					selectedOption = input.find('option').filter(':selected');

					if (input.prop('multiple')) {
						inputValue = [];

						selectedOption.each(function(index, element) {
							inputValue.push(element.value);
						});
					} else {
						inputValue = selectedOption.val();
					}
				} else if (inputTag === 'radio' || inputTag === 'checkbox') {
					if (input.prop('checked')) {
						inputValue = input.val();
					}
				} else if (inputTag === 'input' || inputTag === 'textarea') {
					inputValue = input.val();
				} else {
					inputValue = input.text();
				}

				if (inputValue === '' && result[inputName] != null) { continue; }

				if (this.isGroupInput(input)) {
					if (result[inputName] == null) { result[inputName] = []; }

					result[inputName].push(inputValue);
				} else {
					result[inputName] = inputValue;
				}
			}

			return result;
		},

		setValue: function(valueList) {
			var inputList = this.getElement('input').not('.is--persistent');
			var input;
			var inputName;
			var inputTag;
			var inputValue;
			var i;
			var options;
			var prevSelectedOption;
			var selectedOption;
			var isMultipleValue;

			for (i = 0; i < inputList.length; i++) {
				input = inputList.eq(i);
				inputTag = this.getInputTagName(input);
				inputName = input.data('name');
				inputValue = valueList[inputName];

				if (inputValue == null) { inputValue = ''; }

				if (this.isGroupInput(input) && Array.isArray(inputValue)) {
					inputValue = inputValue.shift();
				}

				if (inputTag === 'select') {
					isMultipleValue = $.isArray(inputValue);

					options = input.find('option');
					prevSelectedOption = options.filter(':selected');
					selectedOption = options.filter(function() {
						return isMultipleValue ? inputValue.indexOf(this.value) !== -1 : this.value === inputValue;
					});

					prevSelectedOption.not(selectedOption).prop('selected', false);
					selectedOption.prop('selected', true);
				} else if (inputTag === 'radio' || inputTag === 'checkbox') {
					input.prop('checked', inputValue === input.val());
				} else if (inputTag === 'input' || inputTag === 'textarea') {
					input.val(inputValue);
				} else {
					input.text(inputValue);
				}
			}
		},

		getDisplayValue: function() {
			var result = {};
			var inputList = this.getElement('input');
			var input;
			var inputName;
			var inputTag;
			var inputValue;
			var i;
			var selectedOptionList;
			var option;
			var optionIndex;

			for (i = 0; i < inputList.length; i++) {
				input = inputList.eq(i);
				inputTag = this.getInputTagName(input);
				inputName = input.data('name');
				inputValue = '';

				if (input.closest('tr').hasClass('is--hidden')) { continue; } // depend support

				if (inputTag === 'select') {
					selectedOptionList = input.find('option').filter(':checked');

					for (optionIndex = 0; optionIndex < selectedOptionList.length; optionIndex++) {
						option = selectedOptionList.eq(optionIndex);

						if (option.prop('disabled') !== true) {
							inputValue += (inputValue.length > 0 ? ', ' : '') + option.text();
						}
					}
				} else if (inputTag === 'radio' || inputTag === 'checkbox') {
					if (input.prop('checked')) {
						inputValue = input.val();
					}
				} else if (inputTag === 'input' || inputTag === 'textarea') {
					inputValue = input.val();
				} else {
					inputValue = input.text();
				}

				if (inputValue === '' && result[inputName] != null) { continue; }

				if (this.isGroupInput(input)) {
					if (result[inputName] == null) { result[inputName] = []; }

					result[inputName].push(inputValue);
				} else {
					result[inputName] = inputValue;
				}
			}

			return result;
		},

		isGroupInput: function(input) {
			var multiple = input.data('multiple');

			return multiple != null && multiple !== false;
		},

		setParentField: function(field) {
			this._parentField = field;
		},

		getParentField: function() {
			return this._parentField;
		},

		getInput: function(name) {
			var inputs = this.getElement('input');
			var input;
			var i;
			var result;

			for (i = 0; i < inputs.length; i++) {
				input = inputs.eq(i);

				if (input.data('name') === name) {
					result = input;
					break;
				}
			}

			return result;
		},

		getInputTagName: function(element) {
			var node = element instanceof $ ? element[0] : element;
			var type;
			var result;

			if (node) {
				result = (node.tagName || '').toLowerCase();

				if (result === 'input') {
					type = (node.type || '').toLowerCase();

					if (type === 'radio' || type === 'checkbox') {
						result = type;
					}
				}
			}

			return result;
		}

	});

})(BX, jQuery);