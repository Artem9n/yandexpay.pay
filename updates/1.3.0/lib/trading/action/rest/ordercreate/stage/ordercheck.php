<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderCreate\Stage;

use YandexPay\Pay;
use YandexPay\Pay\Exceptions;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Action\Reference;
use YandexPay\Pay\Trading\Action\Rest\OrderCreate\Request;
use YandexPay\Pay\Trading\Action\Rest\State;

class OrderCheck
{
	use Concerns\HasMessage;

	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	public function __invoke(State\OrderCalculation $state)
	{
		$this->checkBasket($state);
		$this->checkDelivery($state);
		$this->checkTotal($state);
	}

	protected function checkBasket(State\OrderCalculation $state) : void
	{
		foreach ($this->request->getItems() as $index => $item)
		{
			$basketCode = $state->basketMap[$index] ?? null;

			Assert::notNull($basketCode, '$basketMap[$index]');

			$basketResult = $state->order->getBasketItemData($basketCode);
			$basketData = $basketResult->getData();

			Exceptions\Facade::handleResult($basketResult);

			Assert::notNull($basketData['QUANTITY'], '$basketData[QUANTITY]');
			Assert::notNull($basketData['PRICE'], '$basketData[PRICE]');

			$productPrice = $basketData['PRICE'] * $basketData['QUANTITY'];

			if (Pay\Data\Price::round($productPrice) !== Pay\Data\Price::round($item->getAmount()))
			{
				throw new Reference\Exceptions\DtoProperty(
					self::getMessage('ITEM_PRICE', [
						'#REQUEST_PRICE#' => $item->getAmount(),
						'#ORDER_PRICE#' => $productPrice,
					]),
					'ORDER_AMOUNT_MISMATCH'
				);
			}

			if (Pay\Data\Quantity::round($basketData['QUANTITY']) !== Pay\Data\Quantity::round($item->getCount()))
			{
				throw new Reference\Exceptions\DtoProperty(
					self::getMessage('ITEM_QUANTITY', [
						'#REQUEST_QUANTITY#' => $item->getCount(),
						'#ORDER_QUANTITY#' => $basketData['QUANTITY'],
					]),
					'ORDER_AMOUNT_MISMATCH'
				);
			}
		}
	}

	protected function checkDelivery(State\OrderCalculation $state) : void
	{
		$type = $this->request->getDeliveryType();
		$delivery = $type === 'PICKUP' ? $this->request->getPickup() : $this->request->getDelivery();

		if ($delivery === null) { return; }

		$shipmentPrice = $state->order->getShipmentPrice($delivery->getId());

		Assert::notNull($shipmentPrice, '$shipmentPrice');

		if (Pay\Data\Price::round($shipmentPrice) !== Pay\Data\Price::round($delivery->getAmount()))
		{
			throw new Reference\Exceptions\DtoProperty(
				self::getMessage('DELIVERY_PRICE', [
					'#REQUEST_DELIVERY_PRICE#' => $delivery->getAmount(),
					'#ORDER_DELIVERY_PRICE#' => $shipmentPrice
				]),
				'SHIPPING_DETAILS_MISMATCH'
			);
		}
	}

	protected function checkTotal(State\OrderCalculation $state) : void
	{
		$orderPrice = $state->order->getOrderPrice();
		$requestPrice = $this->request->getOrderAmount();

		if (Pay\Data\Price::round($orderPrice) !== Pay\Data\Price::round($requestPrice))
		{
			throw new Reference\Exceptions\DtoProperty(
				self::getMessage('ORDER_PRICE', [
					'#REQUEST_ORDER_PRICE#' => $requestPrice,
					'#ORDER_PRICE#' => $orderPrice,
				]),
				'ORDER_AMOUNT_MISMATCH'
			);
		}
	}
}