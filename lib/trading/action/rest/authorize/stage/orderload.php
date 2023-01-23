<?php
namespace YandexPay\Pay\Trading\Action\Rest\Authorize\Stage;

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
		$this->hash = $hash;
	}

	public function __invoke(State\Order $state)
	{
		$this->loadOrder($state);
		$this->checkHash($state);
	}

	protected function loadOrder(State\Order $state) : void
	{
		$state->orderAdapter = $state->environment->getOrderRegistry()->load($this->orderId);
		$state->order = $state->orderAdapter->getOrder();
	}

	protected function checkHash(State\Order $state) : void
	{
		if ($state->order->getHash() !== $this->hash)
		{
			throw new SystemException('invalid hash order');
		}
	}
}

