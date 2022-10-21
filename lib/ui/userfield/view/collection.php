<?php

namespace YandexPay\Pay\Ui\UserField\View;

use Bitrix\Main;
use YandexPay\Pay\Ui\UserField\Helper;

class Collection
{
	public static function render($name, array $values, \Closure $renderer, array $attributes = []) : string
	{
		Main\UI\Extension::load('yandexpaypay.admin.field.raw');

		$valueIndex = 0;

		if (empty($values)) { $values[] = ''; }

		$attributes += [
			'class' => 'js-plugin',
			'data-plugin' => 'Field.Raw.Collection',
		];

		if (!isset($attributes['data-name']))
		{
			$attributes['data-base-name'] = $name;
		}

		$result = sprintf('<table %s>', Helper\Attributes::stringify($attributes));

		foreach ($values as $value)
		{
			$itemName = $name . '[' . $valueIndex . ']';

			$result .= '<tr class="js-input-collection__item"><td>';
			$result .= $renderer($itemName, $value);
			$result .= ' ';
			$result .= static::renderDeleteButton();
			$result .= '</td></tr>';

			++$valueIndex;
		}

		$result .= '<tr><td>';
		$result .= static::renderAddButton();
		$result .= '</td></tr>';
		$result .= '</table>';

		return $result;
	}

	protected static function renderDeleteButton() : string
	{
		return sprintf(
			'<a class="b-remove js-input-collection__delete" href="#"></a>'
		);
	}

	protected static function renderAddButton() : string
	{
		return sprintf(
			'<input class="js-input-collection__add" type="button" value="%s">',
			Main\Localization\Loc::getMessage('USER_TYPE_PROP_ADD')
		);
	}
}