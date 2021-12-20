(function(BX) {

	const Reference = BX.namespace('YandexPay.Field.Reference');
	const Fieldset = BX.namespace('YandexPay.Field.Fieldset');
	const utils = BX.namespace('YandexPay.Utils');

	const constructor = Fieldset.Summary = Reference.Summary.extend({

		defaults: {
			modalElement: '.js-fieldset-summary__edit-modal',
			fieldElement: '.js-fieldset-summary__field',
			clearElement: '.js-fieldset-summary__clear',
			textElement: '.js-fieldset-summary__text',
			summary: null,

			modalWidth: 500,
			modalHeight: 300,

			lang: {},
			langPrefix: 'YANDEX_MARKET_FIELD_FIELDSET_'
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
			this.handleTextClick(true);
			this.handleClearClick(true);
		},

		unbind: function() {
			this.handleTextClick(false);
			this.handleClearClick(false);
		},

		handleTextClick: function(dir) {
			const textElement = this.getElement('text');

			textElement[dir ? 'on' : 'off']('click', $.proxy(this.onTextClick, this));
		},

		handleClearClick: function(dir) {
			const clearElement = this.getElement('clear');

			clearElement[dir ? 'on' : 'off']('click', $.proxy(this.onClearClick, this));
		},

		onTextClick: function(evt) {
			this.openEditModal();

			evt.preventDefault();
		},

		onClearClick: function(evt) {
			this.clear();

			evt.preventDefault();
		},

		refreshSummary: function() {
			const template = this.options.summary;
			const displayValues = this.getDisplayValue();
			const groupedValues = this.groupValues(displayValues);
			const summaryValues = this.summaryValues(groupedValues);
			const textElement = this.getElement('text');
			let text;

			if (template) {
				text = this.renderTemplate(template, summaryValues);
			} else {
				text = this.joinValues(summaryValues);
			}

			if (text === '') {
				text = this.getLang('PLACEHOLDER');
			}

			textElement.html(text);
		},

		groupValues: function(values) {
			const result = {};

			for (let key in values) {
				if (!values.hasOwnProperty(key)) { continue; }

				let keyParts = key.split('[');
				let valueChain = result;

				if (keyParts[0] === '') { keyParts.shift(); }

				let keyPartLength = keyParts.length;

				for (let keyPartIndex = 0; keyPartIndex < keyPartLength; ++keyPartIndex) {
					let keyPart = keyParts[keyPartIndex];
					keyPart = keyPart.replace(']', '');

					if (keyPartIndex + 1 === keyPartLength) { // is last
						valueChain[keyPart] = values[key];
					} else {
						if (!(keyPart in valueChain)) {
							valueChain[keyPart] = {};
						}

						valueChain = valueChain[keyPart];
					}
				}
			}

			return result;
		},

		summaryValues: function(values) {
			for (let key in values) {
				if (!values.hasOwnProperty(key)) { continue; }
				if (typeof values[key] !== 'object' || values[key] == null) { continue; }

				const value = values[key];
				const template = this.getFieldSummaryTemplate(key);
				const unitOption = this.getFieldSummaryUnit(key);
				let displayValue;

				if (Array.isArray(value)) {
					const displayValueParts = value.map((valueItem) => this.summaryValueTemplate(valueItem, template, unitOption));
					displayValue = displayValueParts.join(', ');
				} else {
					displayValue = this.summaryValueTemplate(value, template, unitOption);
				}

				values[key] = displayValue;
			}

			return values;
		},

		summaryValueTemplate: function(value, template, unitOption) {
			let result = value;
			let unit;

			if (template != null) {
				result = this.renderTemplate(template, result);
			}

			if (unitOption != null) {
				unit = this.formatUnit(result, unitOption);

				if (unit != null) {
					result = '' + result + ' ' + unit;
				}
			}

			return result;
		},

		getFieldSummaryTemplate: function(key) {
			return this.getFieldOption(key, 'summary');
		},

		getFieldSummaryUnit: function(key) {
			let result = this.getFieldOption(key, 'unit');

			if (result != null && result.indexOf('|') !== -1) {
				result = result.split('|');
			}

			return result;
		},

		formatUnit: function(value, unit) {
			let number;
			let result;

			if (typeof value === 'number') {
				number = parseInt(value, 10);
			} else if (typeof value === 'string') {
				const numberMatch = /(\d+([.,]\d+)?)\D*$/.exec(value); // extract last number

				if (numberMatch) {
					number = parseInt(numberMatch[1], 10);
				}
			}

			if (number != null && !isNaN(number)) {
				result = Array.isArray(unit) ? utils.sklon(number, unit) : unit;
			}

			return result;
		},

		getFieldOption: function(key, type) {
			const optionKey =
				'field'
				+ key.substring(0, 1).toUpperCase()
				+ key.substring(1).toLowerCase()
				+ type.substring(0, 1).toUpperCase()
				+ type.substring(1).toLowerCase();

			return this.options[optionKey];
		},

		renderTemplate: function(template, values) {
			const usedKeys = this.getTemplateUsedKeys(template);
			const replaces = this.getTemplateReplaces(values, usedKeys);
			let result = template;

			result = this.applyTemplateRemoveVariables(result, usedKeys, replaces);
			result = this.applyTemplateReplaceVariables(result, replaces);

			return result;
		},

		getTemplateReplaces: function(values, keys) {
			var result = {};
			var keyIndex;
			var key;
			var chain;
			var chainIndex;
			var chainKey;
			var level;

			for (keyIndex = 0; keyIndex < keys.length; keyIndex++) {
				key = keys[keyIndex];
				chain = key.split('.');
				level = values;

				for (chainIndex = 0; chainIndex < chain.length; chainIndex++) {
					chainKey = chain[chainIndex];

					if (level[chainKey] == null) { break; }

					if (chainIndex < chain.length - 1) {
						level = level[chainKey];
					} else if (level[chainKey]) {
						result[key] = level[chainKey];
					}
				}
			}

			return result;
		},

		applyTemplateRemoveVariables: function(template, keys, replaces) {
			let result = template;

			for (let keyIndex = 0; keyIndex < keys.length; keyIndex++) {
				let key = keys[keyIndex];

				if (!(key in replaces)) {
					result = this.removeTemplateVariable(result, key);
				}
			}

			return result;
		},

		applyTemplateReplaceVariables: function(template, replaces) {
			let result = template;

			for (let key in replaces) {
				if (!replaces.hasOwnProperty(key)) { continue; }

				result = this.replaceTemplateVariable(result, key, replaces[key]);
			}

			return result;
		},

		replaceTemplateVariable: function(template, key, value) {
			return template.replace('#' + key + '#', value);
		},

		removeTemplateVariable: function(template, key) {
			const search = '#' + key + '#';
			const searchLength = search.length;
			let searchPosition;
			let result = template;

			while ((searchPosition = result.indexOf(search)) !== -1) {
				let before = result.substring(0, searchPosition);
				before = this.trimRightPart(before);
				let after = result.substring(searchPosition + searchLength);
				after = this.trimLeftPart(after);

				if (after[0] === ',' && before[before.length - 1] === '.') {
					after = after.substring(0, after.length - 1);
				}

				if (after[0] === '(') {
					after = ' ' + after;
				}

				result = before + after;
			}

			return result;
		},

		trimLeftPart: function(part) {
			return part.replace(/^[^#.,(]+/, '');
		},

		trimRightPart: function(part) {
			return part.replace(/[,(]?[^#.,()]*$/, '');
		},

		getTemplateUsedKeys: function(template) {
			const pattern = /#([A-Z0-9_.]+?)#/g;
			const result = [];
			let match;

			while (match = pattern.exec(template)) {
				result.push(match[1]);
			}

			return result;
		},

		joinValues: function(values) {
			return Object.values(values).join(', ');
		},

		getFieldPlugin: function() {
			return Fieldset.Row;
		}

	}, {
		dataName: 'FieldFieldsetSummary',
		pluginName: 'YandexPay.Field.Fieldset.Summary'
	});

})(BX);