<?php
namespace YandexPay\Pay\Ui\SaleInput;

use Bitrix\Main;
use Bitrix\Sale;

if (!Main\Loader::includeModule('sale')) { return; }

class Mapping
{
	public static function saveMapping($codeKey, $personTypeId, $mapping) : Main\Result
	{
		if (($codeKey === 'YANDEX_PAY_DISPLAY') && is_array($mapping['PROVIDER_VALUE']))
		{
			$mapping['PROVIDER_VALUE'] = static::formatDisplayFields($mapping['PROVIDER_VALUE']);
		}

		$application = Main\Application::getInstance();
		$paySystemId = $application !== null ? $application->getContext()->getRequest()->get('ID') : null;
		$consumerKey = null;

		if ((int)$paySystemId > 0)
		{
			$consumerKey = Sale\PaySystem\Service::PAY_SYSTEM_PREFIX.$paySystemId;
		}

		$common = !IsModuleInstalled('bitrix24');

		return Sale\BusinessValue::setMapping($codeKey, $consumerKey, $personTypeId, $mapping, $common);
	}

	protected static function formatDisplayFields(array $fields) : string
	{
		$displayFields = [];
		$display = mb_strtoupper($fields['DISPLAY']);

		foreach ($fields as $key => $value)
		{
			if (
				trim($value) === ''
				|| ($key !== 'DISPLAY'
					&& substr($key, strlen($key) - strlen($display)) !== $display)
			) { continue; }

			$displayFields[$key] = $value;
		}

		return serialize($displayFields);
	}
}