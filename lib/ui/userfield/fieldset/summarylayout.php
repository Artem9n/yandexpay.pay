<?php

namespace YandexPay\Pay\Ui\UserField\Fieldset;

use Bitrix\Main;
use YandexPay\Pay;
use YandexPay\Pay\Ui\UserField;

class SummaryLayout extends AbstractLayout
{
	use Pay\Reference\Concerns\HasMessage;

	public function edit($value) : string
	{
		Main\UI\Extension::load('yandexpaypay.admin.field.fieldset');

		$pluginAttributes = $this->getPluginAttributes($this->name);

		return $this->editRow($this->name, $value, $pluginAttributes);
	}

	public function editMultiple($values) : string
	{
		Main\UI\Extension::load('yandexpaypay.admin.field.fieldset');

		$valueIndex = 0;
		$inputName = preg_replace('/\[]$/', '', $this->name);
		$onlyPlaceholder = false;

		if (empty($values))
		{
			$onlyPlaceholder = true;
			$values[] = [];
		}

		$collectionAttributes = $this->getPluginAttributes($inputName) + [
			'data-plugin' => 'Field.Fieldset.SummaryCollection',
		];

		$result = sprintf('<div %s>', UserField\Helper\Attributes::stringify($collectionAttributes));

		foreach ($values as $value)
		{
			$valueName = $inputName . '[' . $valueIndex . ']';
			$rowHtml = $this->editRow($valueName, $value, [
				'class' => $this->getFieldsetName('collection__item') . ($onlyPlaceholder ? ' is--hidden' : ''),
			]);

			if ($onlyPlaceholder)
			{
				$rowHtml = UserField\Helper\Attributes::sliceInputName($rowHtml);
			}

			$result .= $rowHtml;

			++$valueIndex;
		}

		$result .= '<div class="b-field-add">';
		$result .= '<input ' . UserField\Helper\Attributes::stringify([
			'class' => 'adm-btn ' . $this->getFieldsetName('collection__item-add'),
			'type' => 'button',
			'value' => self::getMessage('ADD'),
		]) . ' />';
		$result .= '</div>';
		$result .= '</div>';

		return $result;
	}

	protected function editRow(string $name, $value, array $attributes = []) : string
	{
		$value = $this->resolveRowValues($value);
		$fields = $this->extendFields($name, $this->fields);
		$summaryTemplate = $this->userField['SETTINGS']['SUMMARY'] ?? null;
		$summary = !empty($value)
			? UserField\Helper\Summary::make($fields, $value, $summaryTemplate)
			: '';
		$placeholder = $this->userField['SETTINGS']['PLACEHOLDER'] ?? self::getMessage('SUMMARY_HOLDER');
		$useCollection = (isset($attributes['class']) && mb_strpos($attributes['class'], $this->getFieldsetName('collection__item')) !== false);

		$rootAttributes =
			$attributes
			+ array_filter([
				'data-plugin' => 'Field.Fieldset.Summary',
				'data-lang' => array_filter([
					'MODAL_TITLE' => $this->userField['NAME'],
				]),
				'data-summary' => $summaryTemplate,
				'data-modal-width' => $this->userField['SETTINGS']['MODAL_WIDTH'] ?? null,
				'data-modal-height' => $this->userField['SETTINGS']['MODAL_HEIGHT'] ?? null,
				'data-lang-placeholder' => $placeholder,
			])
			+ $this->collectFieldsSummaryAttributes($fields);

		$rootAttributes['class'] = 'b-form-pill' . (isset($rootAttributes['class']) ? ' ' . $rootAttributes['class'] : '');

		$result = '<div ' . UserField\Helper\Attributes::stringify($rootAttributes) . '>';
		$result .= sprintf('<a class="b-link action--heading target--inside %s" href="#">', $this->getFieldsetName('summary__text'));
		$result .= $summary ?: $placeholder;
		$result .= '</a>';
		$result .= sprintf('<button class="b-close %s" type="button" title=""></button>', $useCollection ? $this->getFieldsetName('collection__item-delete') : $this->getFieldsetName('summary__clear'));
		$result .= sprintf('<div class="is--hidden %s">', $this->getFieldsetName('summary__edit-modal'));
		$result .= $this->renderEditForm($fields, $value);
		$result .= '</div>';
		$result .= '</div>';

		return $result;
	}

	protected function collectFieldsSummaryAttributes(array $fields) : array
	{
		$result = [];

		foreach ($fields as $code => $field)
		{
			if (isset($field['SETTINGS']['SUMMARY']) && is_string($field['SETTINGS']['SUMMARY']))
			{
				$attributeName = 'data-field-' . mb_strtolower($code) . '-summary';

				$result[$attributeName] = $field['SETTINGS']['SUMMARY'];
			}

			if (!empty($field['SETTINGS']['UNIT']))
			{
				$attributeName = 'data-field-' . mb_strtolower($code) . '-unit';

				$result[$attributeName] = is_array($field['SETTINGS']['UNIT'])
					? implode('|', $field['SETTINGS']['UNIT'])
					: $field['SETTINGS']['UNIT'];
			}
		}

		return $result;
	}

	protected function renderEditForm(array $fields, array $values) : string
	{
		$activeGroup = null;
		$groupHtml = '';
		$hasGroupFields = false;
		[$editable, $hidden] = $this->splitHiddenFields($fields);

		$result = sprintf('<table %s>', UserField\Helper\Attributes::stringify(array_filter([
			'class' => 'edit-table ' . $this->getFieldsetName('summary__field'),
			'width' => '100%',
			'data-plugin' => 'Field.Fieldset.Row',
			'data-element-namespace' => $this->hasParentFieldset() ? '.' . $this->fieldsetName : null,
		])));
		$result .= $this->renderHiddenFields($hidden, $values);

		foreach ($editable as $fieldKey => $field)
		{
			$value = Pay\Utils\BracketChain::get($values, $fieldKey);

			$row = UserField\Helper\Renderer::getEditRow($field, $value, $values);

			// write result

			if (isset($field['GROUP']) && $field['GROUP'] !== $activeGroup)
			{
				if ($activeGroup !== null)
				{
					$result .= sprintf(
						'<tr class="heading %s"><td colspan="2">%s</td></tr>',
						$hasGroupFields ? '' : 'is--hidden',
						$activeGroup
					);
				}

				$result .= $groupHtml;
				$groupHtml = '';
				$activeGroup = $field['GROUP'];
				$hasGroupFields = false;
			}

			// row attributes

			$rowAttributes = [];

			if ($row['ROW_CLASS'] !== '')
			{
				$rowAttributes['class'] = $row['ROW_CLASS'];
			}

			if (isset($field['DEPEND']))
			{
				Main\UI\Extension::load('yandexpaypay.admin.ui.input.dependfield');

				$rowAttributes['class'] =
					(isset($rowAttributes['class']) ? $rowAttributes['class'] . ' ' : '')
					. 'js-plugin-delayed';
				$rowAttributes['data-plugin'] = 'Ui.Input.DependField';
				$rowAttributes['data-depend'] = Main\Web\Json::encode($field['DEPEND'], JSON_UNESCAPED_UNICODE);
				$rowAttributes['data-form-element'] = '.' . $this->getFieldsetName('summary__field');

				if (!Pay\Utils\UserField\DependField::test($field['DEPEND'], $values))
				{
					$rowAttributes['class'] .= ' is--hidden';
				}
				else
				{
					$hasGroupFields = true;
				}
			}
			else
			{
				$hasGroupFields = true;
			}

			// title cell

			$titleAttributes = [];

			if ($row['VALIGN'] !== '')
			{
				$titleAttributes['valign'] = $row['VALIGN'];
			}

			// control

			$control = $this->prepareFieldControl($row['CONTROL'], $fieldKey, $field);
			$control = UserField\Helper\Attributes::delayPluginInitialization($control);

			$titleCell = $field['NAME'] ?? $field['EDIT_FORM_LABEL'] ?? $field['LIST_COLUMN_LABEL'] ?? $field['LIST_FILTER_LABEL'];

			if (!empty($field['HELP_MESSAGE']))
			{
				$titleHelp = sprintf(
					'<span class="b-icon icon--question indent--right b-tag-tooltip--holder">'
					. '<span class="b-tag-tooltip--content b-tag-tooltip--content_right">%s</span>'
					. '</span>',
					$field['HELP_MESSAGE']
				);

				$titleCell = $titleHelp . $titleCell;
			}

			$groupHtml .= sprintf(
				'<tr %s>'
				. '<td class="adm-detail-content-cell-l" width="40%%" %s>%s</td>'
				. '<td class="adm-detail-content-cell-r">%s</td>'
				. '</tr>',
				UserField\Helper\Attributes::stringify($rowAttributes),
				UserField\Helper\Attributes::stringify($titleAttributes),
				$titleCell,
				$control
			);
		}

		if ($activeGroup !== null)
		{
			$result .= sprintf(
				'<tr class="heading %s"><td colspan="2">%s</td></tr>',
				$hasGroupFields ? '' : 'is--hidden',
				$activeGroup
			);
		}

		$result .= $groupHtml;
		$result .= '</table>';

		return $result;
	}

	protected function renderHiddenFields(array $fields, array $values) : string
	{
		if (empty($fields)) { return ''; }

		$controls = '';

		foreach ($fields as $fieldKey => $field)
		{
			$value = Pay\Utils\BracketChain::get($values, $fieldKey);
			$attributes = [
				'type' => 'hidden',
				'name' => $field['FIELD_NAME'],
				'value' => (string)$value,
			];

			$control = sprintf('<input %s />', UserField\Helper\Attributes::stringify($attributes));
			$control = $this->prepareFieldControl($control, $fieldKey, $field);

			$controls .= $control;
		}

		return sprintf('<tr><td colspan="2">%s</td></tr>', $controls);
	}

	protected function splitHiddenFields(array $fields) : array
	{
		$editable = [];
		$hidden = [];

		foreach ($fields as $key => $field)
		{
			if (!empty($field['HIDDEN']) && $field['HIDDEN'] !== 'N')
			{
				$hidden[$key] = $field;
			}
			else
			{
				$editable[$key] = $field;
			}
		}

		return [$editable, $hidden];
	}
}