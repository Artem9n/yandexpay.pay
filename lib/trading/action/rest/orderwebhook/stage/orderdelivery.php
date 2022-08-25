<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderWebhook\Stage;

use Bitrix\Sale;
use YandexPay\Pay\Delivery;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Action\Api;
use YandexPay\Pay\Trading\Action\Rest\OrderWebhook\Request;
use YandexPay\Pay\Trading\Action\Rest\State;

class OrderDelivery
{
	use Concerns\HasMessage;

	/** @var \YandexPay\Pay\Trading\Action\Rest\OrderWebhook\Request  */
	protected $request;
	/** @var \YandexPay\Pay\Delivery\Yandex\Handler */
	protected $delivery;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	public function __invoke(State\Payment $state)
	{
		$this->processTransportDelivery($state);
	}

	protected function processTransportDelivery(State\Payment $state) : void
	{
		$status = $this->request->getOrder()->getDeliveryStatus();

		if ($status === null) { return; }

		/** @var \Bitrix\Sale\Shipment $shipment */
		foreach ($state->order->getShipmentCollection() as $shipment)
		{
			if ($shipment->isSystem()) { continue; }

			$delivery = $shipment->getDelivery();

			if ($delivery === null) { continue; }

			$requestHandler = $delivery->getDeliveryRequestHandler();

			if (!($requestHandler instanceof Delivery\Yandex\RequestHandler)) { continue; }

			$requestId = $this->getRequestIdByShipmentId($shipment->getId());

			if ($requestId === null) { continue; }

			$requestHandler->notifyTransport($requestId, $status, $state->order->getId(), $shipment->getId());
		}
	}

	protected function getRequestIdByShipmentId(int $shipmentId) : ?int
	{
		$result = null;

		$query = Sale\Delivery\Requests\ShipmentTable::getList([
			'filter' => [
				'=SHIPMENT_ID' => $shipmentId
			],
			'limit' => 1
		]);

		if ($shipment = $query->fetch())
		{
			$result = $shipment['REQUEST_ID'];
		}

		return $result;
	}
}