<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderWebhook\Stage;

use Bitrix\Sale;
use Bitrix\Main\Type;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Entity\Sale as EntitySale;
use YandexPay\Pay\Trading\Action\Api;
use YandexPay\Pay\Trading\Action\Reference\Exceptions\DtoProperty;
use YandexPay\Pay\Trading\Action\Rest\OrderWebhook\Request;
use YandexPay\Pay\Trading\Action\Rest\State;

class OrderOperation
{
	use Concerns\HasMessage;

	/** @var \YandexPay\Pay\Trading\Action\Rest\OrderWebhook\Request  */
	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	public function __invoke(State\Payment $state)
	{
		$this->processOperation($state);
	}

	protected function processOperation(State\Payment $state) : void
	{
		$result = new Sale\Result();

		$operation = $this->request->getOperation();

		if ($operation->getStatus() === 'FAIL')
		{
			$message = static::getMessage('OPERATION_TYPE_' . $operation->getType());
			$result->addWarnings([new \Bitrix\Main\Error($message)]);
		}

		\Bitrix\Sale\EntityMarker::addMarker($state->order, $state->payment, $result);
	}
}