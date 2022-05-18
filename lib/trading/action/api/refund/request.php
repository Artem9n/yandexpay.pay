<?php
namespace YandexPay\Pay\Trading\Action\Api\Refund;

use Bitrix\Main;
use Bitrix\Sale\Payment;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Action;
use YandexPay\Pay\Utils\Encoding;

class Request extends Action\Api\Reference\Request
{
	/** @var \Bitrix\Sale\Payment */
	protected $payment;

	public function getPath() : string
	{
		return sprintf('/api/merchant/v1/orders/%s/refund', $this->getPayment()->getOrderId());
	}

	protected function queryHeaders() : array
	{
		return [
			'X-Request-Id' => $this->getPayment()->getOrderId(),
			'X-Request-Timeout' => 20,
			'X-Request-Attempt' => 0
		];
	}

	public function getQuery() : array
	{
		if ($this->isFullRefund())
		{
			$result = [
				'refundAmount' => $this->getPayment()->getSum(),
				'orderAmount' => 0,
			];
		}
		else
		{
			$result = [
				'cart' => $this->getCart(),
				'refundAmount' => (string)($this->getPayment()->getSum() - $this->getPayment()->getOrder()->getPrice()),
				'orderAmount' => $this->getPayment()->getOrder()->getPrice(),
				'shipping' => $this->getShipping(),
			];
		}

		return $result;
	}

	protected function isFullRefund() : bool
	{
		$paymentAmount = (float)$this->getPayment()->getSum();
		$orderAmount = (float)$this->getPayment()->getOrder()->getPrice();

		return ($paymentAmount - $orderAmount) <= 0;
	}

	public function setPayment(Payment $payment) : void
	{
		$this->payment = $payment;
	}

	public function getPayment() : Payment
	{
		Assert::notNull($this->payment, 'payment','payment not set');

		return $this->payment;
	}

	protected function getCart() : array
	{
		$result = [];

		$basket = $this->getPayment()->getOrder()->getBasket();

		if ($basket === null) { return $result; }

		/** @var \Bitrix\Sale\BasketItem $item */
		foreach ($basket as $item)
		{
			$product = [
				(string)$item->getProductId(),
				(string)$item->getBasketCode()
			];

			$result['items'][] = [
				'productId' => implode(':', $product),
				'unitPrice' => (float)$item->getBasePriceWithVat(),
				'discountedUnitPrice' => (float)$item->getPriceWithVat(),
				'subtotal' => (float)$item->getBasePriceWithVat() * $item->getQuantity(),
				'total' => (float)$item->getFinalPrice(),
				'title' => (string)$item->getField('NAME'),
				'quantity' => [
					'count' => (float)$item->getQuantity(),
				],
			];
		}

		$result['total']['amount'] = $basket->getPrice();

		return $result;
	}

	protected function getShipping() : array
	{
		return [
			'methodType' => 'COURIER', //todo type
			'amount' => $this->payment->getOrder()->getDeliveryPrice()
		];
	}
}