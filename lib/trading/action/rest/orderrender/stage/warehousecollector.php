<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderRender\Stage;

use YandexPay\Pay\Trading\Action\Rest\Reference\EffectiveResponse;
use YandexPay\Pay\Trading\Action\Rest\State;
use YandexPay\Pay\Trading\Action\Rest\Stage\ResponseCollector;
use YandexPay\Pay\Trading\Settings\Options;

class WarehouseCollector extends ResponseCollector
{
	/** @var Options\Delivery */
	protected $yandexDelivery;

	public function __construct(Options\Delivery $yandexDelivery, EffectiveResponse $response, string $key = '')
	{
		$this->yandexDelivery = $yandexDelivery;
		parent::__construct($response, $key);
	}

	public function __invoke(State\OrderCalculation $state)
	{
		$this->write([
			'country' => $this->yandexDelivery->getWarehouse()->getCountry(),
			'locality' => $this->yandexDelivery->getWarehouse()->getLocality(),
			'street' => $this->yandexDelivery->getWarehouse()->getStreet(),
			'building' => $this->yandexDelivery->getWarehouse()->getBuilding(),
			'entrance' => $this->yandexDelivery->getWarehouse()->getEntrance(),
			'floor' => $this->yandexDelivery->getWarehouse()->getFloor(),
			'location' => [
				'longitude' => $this->yandexDelivery->getWarehouse()->getLon(),
				'latitude' => $this->yandexDelivery->getWarehouse()->getLat(),
			],
		]);
	}
}

