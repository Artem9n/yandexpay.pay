<?php

namespace YandexPay\Pay\Ui\UserField\Helper;

use Bitrix\Main;

class Attributes
{
	protected static $supportMultiple = [
		'class' => ' ',
		'data-plugin' => ', ',
	];

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

	public static function parse(string $attributesString) : array
	{
		$hasMatches = preg_match_all('/(?<name>[\w-]+)(?:\s*=\s*["\'](?<value>.*?)["\'])?/', $attributesString, $matches);

		if (!$hasMatches) { return []; }

		$result = [];

		foreach ($matches['name'] as $index => $name)
		{
			$result[$name] = isset($matches['value'][$index])
				? htmlspecialcharsback($matches['value'][$index])
				: true;
		}

		return $result;
	}

	public static function merge(array $first, ...$other) : array
	{
		$result = $first;

		foreach ($other as $attributes)
		{
			foreach ($attributes as $name => $value)
			{
				if (!isset($result[$name]))
				{
					$result[$name] = $value;
				}
				else if (isset(static::$supportMultiple[$name]))
				{
					$result[$name] .=
						static::$supportMultiple[$name]
						. $value;
				}
				else
				{
					$result[$name] = $value;
				}
			}
		}

		return $result;
	}

	public static function insert(string $html, array $attributes, \Closure $filter = null) : string
	{
		if (empty($attributes)) { return $html; }

		return preg_replace_callback('/(<)(input|textarea|select)(.*?)(\/?>)/si', static function($matches) use ($attributes, $filter) {
			[, $opener, $tagName, $existsAttributesString, $closer] = $matches;

			$existsAttributes = Attributes::parse($existsAttributesString);

			if ($filter !== null && !$filter($tagName, $existsAttributes)) { return $matches[0]; }
			if (isset($existsAttributes['type']) && $existsAttributes['type'] === 'button') { return $matches[0]; }

			$mergedAttributes = Attributes::merge($existsAttributes, $attributes);

			return $opener . $tagName . ' ' . Attributes::stringify($mergedAttributes) . $closer;
		}, $html);
	}

	public static function insertDataName(string $html, string $name, string $baseName, string $attributeName = 'data-name') : string
	{
		return preg_replace_callback('/(<input|<textarea|<select)(.*?)(\/?>)/si', static function($matches) use ($name, $baseName, $attributeName) {
			[, $tagStart, $attributes, $tagEnding] = $matches;
			$dataName = $name;

			if (mb_strpos($attributes, $attributeName . '=') !== false) { return $matches[0]; } // attribute already exists
			if (preg_match('/type=["\']button["\']/i', $attributes)) { return $matches[0]; }

			if (preg_match('/(^|\s)name=["\'](.*?)["\']/', $attributes, $nameMatch))
			{
				$inputName = $nameMatch[2];

				if ($inputName !== $baseName && mb_strpos($inputName, $baseName) === 0)
				{
					$leftName = mb_substr($inputName, strlen($baseName));
					$leftName = preg_replace('/\[\d*]$/', '', $leftName);

					if ($leftName !== '')
					{
						$dataName = '[' . $dataName . ']' . $leftName;
					}
				}
			}

			return
				$tagStart
				. $attributes
				. ' '
				. ($attributeName . '="' . htmlspecialcharsbx($dataName) . '"')
				. $tagEnding;
		}, $html);
	}

	public static function extractFromSettings($userFieldSettings, $settingNames = null) : array
	{
		$result = isset($userFieldSettings['ATTRIBUTES']) ? (array)$userFieldSettings['ATTRIBUTES'] : [];

		if ($settingNames === null)
		{
			$settingNames = [
				'READONLY',
				'STYLE',
				'PLACEHOLDER',
			];
		}

		foreach ($settingNames as $settingName)
		{
			if (
				isset($userFieldSettings[$settingName])
				&& $userFieldSettings[$settingName] !== ''
				&& $userFieldSettings[$settingName] !== false
			)
			{
				$setting = $userFieldSettings[$settingName];
				$attributeName = mb_strtolower($settingName, LANG_CHARSET);

				$result[$attributeName] = $setting;
			}
		}

		return $result;
	}

	public static function delayPluginInitialization(string $html) : string
	{
		return preg_replace('/([\s"\'])js-plugin([\s"\'])/', '$1js-plugin-delayed$2', $html);
	}

	public static function sliceInputName(string $html) : string
	{
		return preg_replace('/(<input|<textarea|<select)(.*?) name=".*?"(.*?\/?>)/si', '$1$2$3', $html);
	}
}