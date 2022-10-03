<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderWebhook;

use Bitrix\Main;
use YandexPay\Pay\Trading\Action\Rest;

class Action extends Rest\Reference\EffectiveAction
{
	/** @var Request */
	protected $request;
	/** @var Rest\State\Order */
	protected $state;

	public function bootstrap() : void
	{
		parent::bootstrap();
		$this->bootMerchantOrder();
	}

	protected function bootMerchantOrder() : void
	{
		$this->request = $this->convertHttpToRequest(Request::class);
		$this->state = $this->makeState(Rest\State\Order::class);

		$orderId = $this->request->getEvent() === 'ORDER_STATUS_UPDATED'
			? $this->request->getOrder()->getId()
			: $this->request->getOperation()->getOrderId();

		(new Rest\Pipeline())
			->pipe(new Rest\Stage\OrderLoad(
				$orderId,
				$this->request->getMerchantId()
			))
			->process($this->state);
	}

	public function process() : Main\HttpResponse
	{
		$response = $this->makeResponse();

		(new Rest\Pipeline())
			->pipe($this->stageAction())
			->pipe(new Rest\OrderWebhook\Stage\OrderSave())
			->pipe(new Rest\OrderWebhook\Stage\OrderLogger($this->request))
			->process($this->state);

		return $this->convertResponseToHttp($response);
	}

	protected function stageAction(): Rest\Pipeline
	{
		$result = new Rest\Pipeline();
		$eventType = $this->request->getEvent();

		if ($eventType === 'ORDER_STATUS_UPDATED')
		{
			$result
				->pipe(new Rest\OrderWebhook\Stage\OrderPay($this->request))
				->pipe(new Rest\OrderWebhook\Stage\OrderDelivery($this->request));
		}
		elseif ($eventType === 'OPERATION_STATUS_UPDATED')
		{
			$result
				->pipe(new Rest\OrderWebhook\Stage\OrderOperation($this->request));
		}

		return $result;
	}
}