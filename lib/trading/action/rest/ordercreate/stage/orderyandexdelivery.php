<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderCreate\Stage;

use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Action\Rest\OrderCreate\Request;
use YandexPay\Pay\Trading\Action\Rest\State;

class OrderYandexDelivery
{
	use Concerns\HasMessage;

	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	public function __invoke(State\OrderCalculation $state)
	{
		$this->fillDelivery($state);
	}

	protected function fillDelivery(State\OrderCalculation $state) : void
	{
		$delivery = $this->request->getYandexDelivery();
		$optionYandexDelivery = $state->options->getDeliveryOptions()->getYandexDelivery();
		$deliveryId = $state->environment->getDelivery()->getEmptyDeliveryId();
		$price = $delivery->getAmount();

		if ($optionYandexDelivery !== null)
		{
			$deliveryId = $optionYandexDelivery->getServiceId();
		}

		$comment = null;

		$fromDate = $delivery->getFromDateTime();
		$toDate = $delivery->getToDatetime();

		if ($fromDate !== null && $toDate !== null)
		{
			$comment = sprintf('%s' . PHP_EOL .'%s', $fromDate->format('d.m.Y H:i'), $toDate->format('d.m.Y H:i'));
		}

		$data = array_filter([
			'DELIVERY_NAME' => static::getMessage('NAME', ['#TITLE#' => $delivery->getTitle()]),
			'COMMENTS' => $comment,
		]);

		$state->order->createShipment($deliveryId, $price, $data);
	}
}