<?php
namespace YandexPay\Pay\Trading\Action\Rest\Stage;

use Bitrix\Sale;
use Bitrix\Main\SystemException;
use YandexPay\Pay\Trading\Action\Rest\State;

class OrderLoad
{
	protected $orderId;
	protected $hash;

	public function __construct(string $orderId, string $hash = null)
	{
		$this->orderId = $orderId;
		//$this->hash = $hash;
	}

	public function __invoke(State\Payment $state)
	{
		$this->loadOrder($state);
		//$this->checkHash($state);
	}

	protected function loadOrder(State\Payment $state) : void
	{
		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		/** @var \Bitrix\Sale\Order $orderClassName */
		$orderClassName = $registry->getOrderClassName();

		$state->order = $orderClassName::load($this->orderId);

		if ($state->order === null)
		{
			throw new SystemException('order not found');
		}
	}

	protected function checkHash(State\Payment $state) : void
	{
		if ($state->order->getHash() !== $this->hash)
		{
			throw new SystemException('invalid hash order');
		}
	}
}

