<?php
namespace YandexPay\Pay\Ui\SaleInput;

use Bitrix\Main;
use Bitrix\Sale;

if (!Main\Loader::includeModule('sale')) { return; }

class Mapping
{
	public static function saveMapping($codeKey, $personTypeId, $mapping) : Main\Result
	{
		$result = new Main\Result();

		if ($codeKey === 'YANDEX_PAY_DISPLAY')
		{
			if (is_array($mapping['PROVIDER_VALUE']))
			{
				$mapping['PROVIDER_VALUE'] = static::formatDisplayFields($mapping['PROVIDER_VALUE']);
			}
		}

		$request = Main\Application::getInstance()->getContext()->getRequest();
		$consumerKey = null;

		if ((int)$request->get('ID') > 0)
		{
			$consumerKey = Sale\PaySystem\Service::PAY_SYSTEM_PREFIX.$request->get('ID');
		}

		$common = !IsModuleInstalled('bitrix24');
		Sale\BusinessValue::setMapping($codeKey, $consumerKey, $personTypeId, $mapping, $common);

		return $result;
	}

	protected static function formatDisplayFields(array $fields) : string
	{
		$displayFields = [];
		$display = mb_strtoupper($fields['DISPLAY']);

		foreach ($fields as $key => $value)
		{
			if (
				$key !== 'DISPLAY'
				&& substr($key, strlen($key) - strlen($display)) !== $display
				|| trim($value) === ''
			) { continue; }

			$displayFields[$key] = $value;
		}

		return serialize($displayFields);
	}
}