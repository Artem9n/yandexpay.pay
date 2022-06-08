<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Ozon;

use Ipol;
use Bitrix\Sale;
use YandexPay\Pay\Trading\Entity\Sale\Delivery\Factory;

/** @property Ipol\Ozon\Bitrix\Handler\DeliveryHandlerPostamat $service */
class Postamat extends Base
{
	protected $typeVariant = 'postamat';

	public function isMatch(Sale\Delivery\Services\Base $service) : bool
	{
		if (!($service instanceof Ipol\Ozon\Bitrix\Handler\DeliveryHandlerPostamat)) { return false; }

		$code = $service->getCode();

		$this->title = $service->getName();

		return $code === 'postamat';
	}

	protected function getType() : string
	{
		return Factory::OZON_POSTAMAT;
	}
}