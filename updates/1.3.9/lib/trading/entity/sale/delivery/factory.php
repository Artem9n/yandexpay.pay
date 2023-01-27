<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery;

use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay\Reference\Assert;

class Factory
{
	public const SITE_STORE = 'site:store';
	public const SDEK_PICKUP = 'sdek:pickup';
	public const SDEK_POSTAMAT = 'sdek:postamat';
	public const SDEK_COURIER = 'sdek:courier';
	public const BOXBERRY_PVZ = 'boxberry:pvz';
	public const DPD_PICKUP = 'dpd:pickup';
	public const DPD_COURIER = 'dpd:courier';
	public const RUSSIAN_POST = 'russianPost:pickup';
	public const RUSSIAN_COURIER = 'russianPost:courier';
	public const EDOST_PICKUP = 'edost:pickup';
	public const EDOST_COURIER = 'edost:courier';

	public static function make(Sale\Delivery\Services\Base $service, string $deliveryType = null) : AbstractAdapter
	{
		$result = null;

		foreach (static::getTypesPickup() as $type)
		{
			$adapter = static::getInstance($type);

			if ($adapter->isMatch($service))
			{
				if ($deliveryType !== null && $adapter->getServiceType() !== $deliveryType) { continue; }
				if (!$adapter->load()) { continue; }

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
			static::SDEK_PICKUP,
			static::SDEK_COURIER,
			static::SDEK_POSTAMAT,
			static::DPD_PICKUP,
			static::DPD_COURIER,
			static::BOXBERRY_PVZ,
			static::RUSSIAN_POST,
			static::RUSSIAN_COURIER,
			static::EDOST_PICKUP,
			static::EDOST_COURIER,
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