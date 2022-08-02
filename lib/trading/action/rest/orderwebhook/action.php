<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderWebhook;

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
			->pipe($this->stageAction($request))
			->pipe(new Rest\OrderWebhook\Stage\OrderSave())
			->process($state);

		return $this->convertResponseToHttp($response);
	}

	protected function stageAction(Request $request): Rest\Pipeline
	{
		$result = new Rest\Pipeline();
		$eventType = $request->getEvent();

		if ($eventType === 'ORDER_STATUS_UPDATED')
		{
			$result
				->pipe(new Rest\OrderWebhook\Stage\OrderLoad($request->getOrder()->getId(), $request->getMerchantId()))
				->pipe(new Rest\OrderWebhook\Stage\OrderPay($request))
				->pipe(new Rest\OrderWebhook\Stage\OrderDelivery($request));
		}
		elseif ($eventType === 'OPERATION_STATUS_UPDATED')
		{
			$result
				->pipe(new Rest\OrderWebhook\Stage\OrderLoad($request->getOperation()->getOrderId(), $request->getMerchantId()))
				->pipe(new Rest\OrderWebhook\Stage\OrderOperation($request));
		}

		return $result;
	}
}