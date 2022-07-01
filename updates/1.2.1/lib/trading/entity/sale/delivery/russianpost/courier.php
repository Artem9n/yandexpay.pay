<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\RussianPost;

use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay\Trading\Entity\Sale\Delivery\Factory;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;

/** @property \Sale\Handlers\Delivery\RussianpostProfile $service */
class Courier extends Base
{
	protected $accountId = 'courierId';

	public function isMatch(Sale\Delivery\Services\Base $service) : bool
	{
		if (
			!($service instanceof \Sale\Handlers\Delivery\RussianpostProfile)
			&& !Main\Loader::includeModule('russianpost.post')
		) { return false; }

		$code = $service->getCode();

		$this->title = $service->getNameWithParent();

		return $code === 'COURIER';
	}

	protected function getType() : string
	{
		return Factory::RUSSIAN_COURIER;
	}

	public function getServiceType() : string
	{
		return EntitySale\Delivery::DELIVERY_TYPE;
	}

	public function markSelectedDelivery(Sale\OrderBase $order, array $address) : void
	{
		$tariff = $this->getTariff($address['zip']);

		$propZip = $order->getPropertyCollection()->getDeliveryLocationZip();

		if ($propZip !== null)
		{
			$propZip->setValue($address['zip']);
		}

		if ($tariff !== null)
		{
			/** @var \Bitrix\Sale\PropertyValue $property */
			foreach ($order->getPropertyCollection() as $property)
			{
				if ($property->getField('CODE') === 'RUSSIANPOST_TYPEDLV')
				{
					$property->setValue($tariff['type']);
					break;
				}
			}
		}
	}
}