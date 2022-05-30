<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderRender\Stage;

use YandexPay\Pay\Trading\Action\Rest\State;
use YandexPay\Pay\Trading\Action\Rest\Stage\ResponseCollector;

class TotalCollector extends ResponseCollector
{
	/** @var array */
	protected $items = [];

	public function __invoke(State\Order $state)
	{
		$this->write([
			'amount' => $state->order->getPrice(),
			'label' => null, // todo
		]);
	}
}

