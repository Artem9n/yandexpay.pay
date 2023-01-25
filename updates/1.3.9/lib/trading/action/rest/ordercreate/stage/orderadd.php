<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderCreate\Stage;

use Bitrix\Main;
use YandexPay\Pay\Exceptions;
use YandexPay\Pay\Logger;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Action\Rest\OrderCreate\Request;
use YandexPay\Pay\Trading\Action\Rest\State;

class OrderAdd
{
	use Concerns\HasMessage;

	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	public function __invoke(State\OrderCalculation $state)
	{
		$this->addOrder($state);
	}

	protected function addOrder(State\OrderCalculation $state) : void
	{
		$saveResult = $state->order->add();

		Exceptions\Facade::handleResult($saveResult);

		$state->logger->info(
			self::getMessage('ORDER_ACCEPT', [
				'#ORDER_ID#' => $state->order->getAccountNumber(),
			]),
			[ 'AUDIT' => Logger\Audit::INCOMING_RESPONSE ]
		);
	}
}