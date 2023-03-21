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
	public const BOXBERRY_PICKUP = 'boxberry:pickup';
	public const BOXBERRY_COURIER = 'boxberry:courier';
	public const DPD_PICKUP = 'dpd:pickup';
	public const DPD_COURIER = 'dpd:courier';
	public const RUSSIAN_POST = 'russianPost:pickup';
	public const RUSSIAN_COURIER = 'russianPost:courier';
	public const EDOST_PICKUP = 'edost:pickup';
	public const EDOST_COURIER = 'edost:courier';

	public static function make(Sale\Delivery\Services\Base $service, string $deliveryType) : ?AbstractAdapter
	{
		$result = null;

		foreach (static::getTypes() as $type)
		{
			$adapter = static::getInstance($type);

			if (
				$adapter->serviceType() === $deliveryType
				&& $adapter->isMatch($service)
			)
			{
				if (!$adapter->load()) { continue; }

				$result = $adapter;
				break;
			}
		}

		return $result;
	}

	protected static function getTypes() : array
	{
		return [
			static::SITE_STORE,
			static::SDEK_PICKUP,
			static::SDEK_COURIER,
			static::SDEK_POSTAMAT,
			static::DPD_PICKUP,
			static::DPD_COURIER,
			static::BOXBERRY_PICKUP,
			static::BOXBERRY_COURIER,
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