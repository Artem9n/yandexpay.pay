<?php
namespace YandexPay\Pay\Ui\UseCase;

use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay\Exceptions;
use YandexPay\Pay\Trading;
use YandexPay\Pay\Gateway;

class AutoInstallPaySystem
{
	public static function install() : void
	{
		try
		{
			if (!Main\Loader::includeModule('sale')) { return; }
			if (static::isInstalled()) { return; }

			$saveResult = Sale\Internals\PaySystemActionTable::add(static::fields());

			Exceptions\Facade::handleResult($saveResult);

			$id = $saveResult->getId();

			Sale\PaySystem\Manager::update($id, [
				'PARAMS' => serialize(['BX_PAY_SYSTEM_ID' => $id]),
				'PAY_SYSTEM_ID' => $id,
			]);
		}
		catch (Main\SystemException $exception)
		{
			trigger_error($exception->getMessage(), E_USER_WARNING);
		}
	}
	
	protected static function fields() : array
	{
		return [
			'ACTION_FILE' => 'yandexpay',
			'ACTIVE' => 'N',
			'PS_MODE' => Gateway\Manager::PAYTURE,
			'PSA_NAME' => 'Yandex Pay',
			'NEW_WINDOW' => 'N',
			'NAME' => 'Yandex Pay',
			'ENTITY_REGISTRY_TYPE' => Sale\Registry::REGISTRY_TYPE_ORDER,
			'LOGOTIP' => static::getLogotip(),
		];
	}

	protected static function getLogotip() : int
	{
		$relativePath = BX_ROOT  . '/images/sale/sale_payments/yandexpay.png';
		$fullPath = Main\IO\Path::convertRelativeToAbsolute($relativePath);
		$file = \CFile::MakeFileArray($fullPath);

		if (!$file) { return 0; }

		$save = [
			'FILE' => $file + [
				'MODULE_ID' => 'sale',
			],
		];

		$saved = \CFile::SaveForDB($save, 'FILE', 'sale/paysystem/logotip');

		if (!$saved) { return 0; }

		return (int)$save['FILE'];
	}

	protected static function isInstalled(array $filter = []) : bool
	{
		$query = Sale\PaySystem\Manager::getList([
			'filter' => $filter + [
				'=ACTION_FILE' => 'yandexpay',
			],
			'limit' => 1,
			'select' => ['ID']
		]);

		return (bool)$query->fetch();
	}
}