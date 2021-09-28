<?php

namespace YandexPay\Pay\Ui\Userfield\Helper;

use Bitrix\Main;

class Attributes
{
	public static function stringify(array $attributes) : string
	{
		$htmlAttributes = [];

		foreach ($attributes as $key => $value)
		{
			if (is_numeric($key))
			{
				$htmlAttributes[] = $value;
			}
			else if ($value === false || $value === null)
			{
				continue;
			}
			else if (is_array($value))
			{
				$valueEncoded = Main\Web\Json::encode($value);

				$htmlAttributes[] = htmlspecialcharsbx($key) . '="' . htmlspecialcharsbx($valueEncoded) . '"';
			}
			else if ($value === true || (string)$value === '')
			{
				$htmlAttributes[] = htmlspecialcharsbx($key);
			}
			else
			{
				$htmlAttributes[] = htmlspecialcharsbx($key) . '="' . htmlspecialcharsbx($value) . '"';
			}
		}

		return implode(' ', $htmlAttributes);
	}
}