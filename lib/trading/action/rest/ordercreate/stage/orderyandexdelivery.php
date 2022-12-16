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
		$data = [];

		if ($optionYandexDelivery !== null)
		{
			$deliveryId = $optionYandexDelivery->getServiceId();
		}

		$comment = null;

		$fromDate = $delivery->getFromDateTime();
		$toDate = $delivery->getToDatetime();

		if ($fromDate !== null && $toDate !== null)
		{
			$comment = sprintf('%s' . PHP_EOL .'%s', (string)$fromDate, (string)$toDate);
		}

		$data += array_filter([
			'COMMENTS' => $comment,
		]);

		$state->order->setShipments($deliveryId, $price, $data);
	}
}