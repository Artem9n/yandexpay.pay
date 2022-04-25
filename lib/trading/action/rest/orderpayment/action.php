<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderPayment;

use Bitrix\Main;
use YandexPay\Pay\Trading\Action\Rest;

class Action extends Rest\Reference\EffectiveAction
{
	public function bootstrap() : void
	{
		$this->bootJwt();
		$this->bootJson();
	}

	public function process() : Main\HttpResponse
	{
		$request = $this->convertHttpToRequest(Request::class);
		$response = $this->makeResponse();
		$state = $this->makeState(Rest\State\Payment::class);

		(new Rest\Pipeline())
			->pipe($this->stageLoadOrder($request))
			->pipe(new Rest\OrderPayment\Stage\OrderPay($request))
			->pipe(new Rest\OrderPayment\Stage\OrderSave())
			->process($state);

		return $this->convertResponseToHttp($response);
	}

	protected function stageLoadOrder(Request $request): Rest\Pipeline
	{
		$eventType = $request->getEvent();

		if ($eventType === 'ORDER_STATUS_UPDATED')
		{
			$orderId = $request->getOrder()->getId();
		}
		else
		{
			$orderId = $request->getOperation()->getOrderId();
		}

		return (new Rest\Pipeline())
			->pipe(new Rest\OrderPayment\Stage\OrderLoad($orderId, $request->getMerchantId()));
	}
}