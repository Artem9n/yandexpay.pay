<?php
namespace YandexPay\Pay\Ui\UserField;

use YandexPay\Pay;
use YandexPay\Pay\Reference\Concerns;

class LogLevelType extends EnumerationType
{
	use Concerns\HasMessage;

	protected static $optionCache;

	public static function getAdminListViewHTML($arUserField, $arHtmlControl) : string
	{
		$result = '';
		$option = static::getOption($arUserField, $arHtmlControl['VALUE']);

		if ($option)
		{
			$imgType = '';

			if (isset($option['VALUE']))
			{
				switch ($option['VALUE'])
				{
					case Pay\Psr\Log\LogLevel::EMERGENCY:
					case Pay\Psr\Log\LogLevel::ALERT:
					case Pay\Psr\Log\LogLevel::CRITICAL:
					case Pay\Psr\Log\LogLevel::ERROR:
						$imgType = 'red';
						break;

					case Pay\Psr\Log\LogLevel::WARNING:
					case Pay\Psr\Log\LogLevel::NOTICE:
						$imgType = 'yellow';
						break;

					case Pay\Psr\Log\LogLevel::INFO:
						$imgType = 'green';
						break;

					default:
						$imgType = 'grey';
						break;
				}
			}

			$result .= '<nobr>';
			$result .= sprintf(
		'<img class="b-log-icon" src="/bitrix/images/%s/logger/%s.gif" width="14" height="14" 
				style="display: inline-block;
				vertical-align: middle;
				position: relative;
				top: -1px;
				margin-right: 4px;" alt="" />',
				Pay\Config::getModuleName(),
				$imgType
			);
			$result .= Pay\Logger\Level::getTitle($option['VALUE']);
			$result .= '</nobr>';
		}

		return $result;
	}

	protected static function getOption($arUserField, $id)
	{
		$result = false;

		if (static::$optionCache === null)
		{
			static::$optionCache = [];

			$query = call_user_func([ $arUserField['USER_TYPE']['CLASS_NAME'], 'GetList' ], $arUserField);

			while ($option = $query->fetch())
			{
				static::$optionCache[$option['ID']] = $option;

				if ($option['ID'] == $id)
				{
					$result = $option;
				}
			}
		}
		else if (isset(static::$optionCache[$id]))
		{
			$result = static::$optionCache[$id];
		}

		return $result;
	}
}