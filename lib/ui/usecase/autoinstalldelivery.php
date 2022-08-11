<?php
namespace YandexPay\Pay\Ui\UseCase;

use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay\Delivery;
use YandexPay\Pay\Exceptions;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading;

class AutoInstallDelivery
{
	public static function install() : void
	{
		try
		{
			if (!Main\Loader::includeModule('sale')) { return; }

			if (static::isInstalled()) { return; }

			static::installDelivery();
		}
		catch (Main\SystemException $exception)
		{
			trigger_error($exception->getMessage(), E_USER_WARNING);
		}
	}

	protected static function isInstalled() : bool
	{
		try
		{
			$service = Sale\Delivery\Services\Manager::getObjectByCode(Delivery\Yandex\Handler::CODE);

			$result	= ($service !== null);
		}
		catch (Main\SystemException $exception)
		{
			$result = false;
		}

		return $result;
	}

	protected static function installDelivery() : void
	{
		$fields = [
			'ACTIVE' => 'Y',
			'PARENT_ID' => 0,
			'CLASS_NAME' => Delivery\Yandex\Handler::class,
		];

		$service = Sale\Delivery\Services\Manager::createObject($fields);

		Assert::notNull($service, '$service');

		$fields = $service->prepareFieldsForSaving($fields);
		$fields += [
			'NAME' => $service::getClassTitle(),
			'DESCRIPTION' => $service::getClassDescription(),
			'CODE' => $service->getCode(),
 		];

		$saveResult = Sale\Delivery\Services\Manager::add($fields);

		Exceptions\Facade::handleResult($saveResult);
	}
}