<?php
namespace YandexPay\Pay\Ui\UseCase;

use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay\Delivery;
use YandexPay\Pay\Exceptions;
use YandexPay\Pay\Trading;

class AutoInstallDelivery
{
	public static function install() : void
	{
		try
		{
			if (!Main\Loader::includeModule('sale')) { return; }

			if (static::isInstalled()) { return; }

			$id = static::installDelivery();
			static::installRestrict($id);
		}
		catch (Main\SystemException $exception)
		{
			trigger_error($exception->getMessage(), E_USER_WARNING);
		}
	}

	protected static function fields() : array
	{
		return [
			'NAME' => Delivery\Yandex\Handler::getClassTitle(),
			'ACTIVE' => 'Y',
			'PARENT_ID' => 0,
			'DESCRIPTION' => Delivery\Yandex\Handler::getClassDescription(),
			'CLASS_NAME' => '\\' . Delivery\Yandex\Handler::class,
			'CODE' => 'yandex_delivery_pay',
		];
	}

	protected static function isInstalled() : bool
	{
		$result = false;

		try
		{
			Sale\Delivery\Services\Manager::getObjectByCode('yandex_delivery_pay');
			$result	= true;
		}
		catch (Main\SystemException $exception)
		{
			//nothing
		}

		return $result;
	}

	protected static function installDelivery() : int
	{
		$result = Sale\Delivery\Services\Table::add(static::fields());
		Exceptions\Facade::handleResult($result);

		return $result->getId();
	}

	protected static function installRestrict(int $id) : void
	{
		$result = Sale\Internals\ServiceRestrictionTable::add([
			'SERVICE_ID' => $id,
			'SERVICE_TYPE' => 0,
			'CLASS_NAME' => '\\' . Trading\UseCase\Restrictions\ByPlatform\Delivery::class,
			'PARAMS' => [
				'INVERT' => 'N',
			],
		]);

		Exceptions\Facade::handleResult($result);
	}
}