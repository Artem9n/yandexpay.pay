<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderWebhook\Stage;

use Bitrix\Sale;
use Bitrix\Main;
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

		$shipmentIds = null;
		$result = null;

		/** @var \Bitrix\Sale\Shipment $item */
		foreach ($state->order->getShipmentCollection() as $item)
		{
			$shipmentIds[] = $item->getId();
		}

		if ($shipmentIds === null) { return; }

		$query = Sale\Delivery\Requests\ShipmentTable::getList([
			'filter' => [
				'=SHIPMENT_ID' => $shipmentIds
			]
		]);

		while ($shipment = $query->fetch())
		{
			$result[] = $shipment['REQUEST_ID'];
		}

		if ($result === null) { return; }

		$existStates = [];

		$queryState = Delivery\Yandex\Internals\RepositoryTable::getList([
			'filter' => [
				'=REQUEST_ID' => $result
			],
		]);

		while ($state = $queryState->fetch())
		{
			$existStates[] = $state['ID'];
		}

		if (empty($existStates))
		{
			foreach ($result as $requestId)
			{
				Delivery\Yandex\Internals\RepositoryTable::add([
					'REQUEST_ID' => $requestId,
					'STATUS' => $status,
				]);
			}
		}
		else
		{
			foreach ($existStates as $id)
			{
				Delivery\Yandex\Internals\RepositoryTable::update($id, [
					'STATUS' => $status,
					'TIMESTAMP_X' => new Main\Type\DateTime(),
				]);
			}
		}
	}
}