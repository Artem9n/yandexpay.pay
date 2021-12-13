<?php

namespace YandexPay\Pay\Trading\Settings\Options;

use Bitrix\Main;
use YandexPay\Pay\Trading\Settings\Reference\FieldsetCollection;

/**
 * @property Delivery[] $collection
*/
class DeliveryCollection extends FieldsetCollection
{
	public function getItemReference() : string
	{
		return Delivery::class;
	}

	public function getServiceIds() : array
	{
		$result = [];

		foreach ($this->collection as $model)
		{
			$result[] = $model->getServiceId();
		}

		return $result;
	}

	public function getItemByServiceId(int $serviceId) : ?Delivery
	{
		$result = null;

		foreach ($this->collection as $model)
		{
			if ($model->getServiceId() === $serviceId)
			{
				$result = $model;
				break;
			}
		}

		/*if ($result === null)
		{
			throw new Main\ObjectNotFoundException();
		}*/

		return $result;
	}
}