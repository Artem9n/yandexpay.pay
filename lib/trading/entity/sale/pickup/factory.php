<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Pickup;

use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay\Reference\Assert;

class Factory
{
	public const SITE_STORE = 'site:store';
	public const SDEK_PICKUP = 'sdek:pickup';
	public const OZON_PICKUP = 'ozon:pickup';
	public const OZON_POSTAMAT = 'ozon:postamat';
	public const BOXBERRY_PVZ_COD = 'boxberry:pvzCode';
	public const BOXBERRY_PVZ = 'boxberry:pvz';
	public const DPD_PICKUP = 'dpd:pickup';
	public const RUSSIAN_POST = 'RussianPost';

	public static function make(Sale\Delivery\Services\Base $service) : AbstractAdapter
	{
		$result = null;

		foreach (static::getTypesPickup() as $type)
		{
			$adapter = static::getInstance($type);

			if ($adapter->isMatch($service))
			{
				$result = $adapter;
				break;
			}
		}

		if ($result === null)
		{
			throw new Main\ArgumentException(sprintf(
				'delivery service %s pickup not implemented',
				get_class($service)
			));
		}

		return $result;
	}

	protected static function getTypesPickup() : array
	{
		return [
			static::SITE_STORE,
			static::OZON_PICKUP,
			static::OZON_POSTAMAT,
			static::SDEK_PICKUP,
			static::DPD_PICKUP,
			static::BOXBERRY_PVZ_COD,
			//static::BOXBERRY_PVZ
		];
	}

	/**
	 * @template T
	 * @param $type class-string<T>
	 *
	 * @return T
	 */
	public static function getInstance(string $type) : AbstractAdapter
	{
		$className = static::makeClassName($type);

		Assert::isSubclassOf($className, AbstractAdapter::class);

		return new $className();
	}

	protected static function makeClassName(string $type) : string
	{
		[$namespace, $className] = explode(':', $type);

		return __NAMESPACE__ . '\\' . ucfirst($namespace) . '\\' . ucfirst($className);
	}
}