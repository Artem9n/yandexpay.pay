<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderCreate;

use Bitrix\Main;
use YandexPay\Pay\Trading\Action\Rest;

class Action extends Rest\Reference\EffectiveAction
{
	public function process() : Main\HttpResponse
	{
		$request = $this->convertHttpToRequest(Request::class);
		$response = $this->makeResponse();

		if ($request->getOrderId() !== null)
		{
			$this->processOrder($request, $response);
		}
		else
		{
			$this->processCheckout($request, $response);
		}

		return $this->convertResponseToHttp($response);
	}

	protected function processOrder(Request $request, Rest\Reference\EffectiveResponse $response) : void
	{
		$state = $this->makeState(Rest\State\Order::class);

		(new Rest\Pipeline())
			->pipe(new Rest\OrderRender\Stage\OrderLoad($request->getOrderId()))
			->pipe(new Rest\OrderCreate\Stage\Order\FinishCollector($response, 'orderId'))
			->pipe(new Rest\OrderCreate\Stage\Order\MetaCollector($response, 'metadata'))
			->process($state);
	}

	protected function processCheckout(Request $request, Rest\Reference\EffectiveResponse $response) : void
	{
		$state = $this->makeState(Rest\State\OrderCalculation::class);

		(new Rest\Pipeline())
			->pipe($this->calculationPipeline($request))
			->pipe($this->collectorPipeline($response))
			->process($state);
	}

	protected function calculationPipeline(Request $request) : Rest\Pipeline
	{
		return (new Rest\Pipeline())
			->pipe(new Rest\OrderCreate\Stage\OrderUser($request))
			->pipe(new Rest\Stage\NewOrder($request->getUserId(), $request->getFUserId(), $request->getCurrencyCode(), $request->getCoupons()))
			->pipe(new Rest\Stage\OrderInitialize())
			->pipe(new Rest\OrderCreate\Stage\OrderStatus())
			->pipe(new Rest\OrderCreate\Stage\OrderProperties($request))
			->pipe(new Rest\Stage\NewBasket($request->getItems()))
			->pipe(new Rest\OrderCreate\Stage\OrderPaySystem($request))
			->pipe($this->stageDelivery($request))
			->pipe(new Rest\Stage\OrderFinalizer())
			->pipe(new Stage\DeliveryCalculate())
			->pipe(new Rest\OrderCreate\Stage\OrderCheck($request))
			->pipe(new Rest\OrderCreate\Stage\OrderAdd($request));
	}

	protected function stageDelivery(Request $request) : Rest\Pipeline
	{
		if ($request->getDeliveryType() === 'PICKUP')
		{
			return (new Rest\Pipeline())
				->pipe(new Rest\OrderCreate\Stage\OrderPickup($request));
		}

		return (new Rest\Pipeline())
			->pipe(new Rest\Stage\OrderLocation($request->getAddress()))
			->pipe(new Rest\OrderCreate\Stage\OrderDelivery($request));
	}

	protected function collectorPipeline(Rest\Reference\EffectiveResponse $response) : Rest\Pipeline
	{
		return (new Rest\Pipeline())
			->pipe(new Rest\OrderCreate\Stage\OrderCollector($response, 'orderId'))
			->pipe(new Rest\OrderCreate\Stage\MetaCollector($response, 'metadata'));
	}
}