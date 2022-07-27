<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderRender;

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
			->pipe(new Rest\OrderRender\Stage\OptionsCollector($response))
			->pipe(new Rest\OrderRender\Stage\ItemsCollector($response, 'cart.items'))
			->pipe(new Rest\OrderRender\Stage\TotalCollector($response, 'cart.total'))
			->process($state);
	}

	protected function processCheckout(Request $request, Rest\Reference\EffectiveResponse $response) : void
	{
		$state = $this->makeState(Rest\State\OrderCalculation::class);

		(new Rest\Pipeline())
			->pipe($this->calculationPipeline($request))
			->pipe($this->collectorPipeline($response))
			->pipe($this->collectorDelivery($response, $request))
			->pipe($this->collectorYandexDelivery($response, $request, $state))
			->process($state);
	}

	protected function calculationPipeline(Request $request) : Rest\Pipeline
	{
		return (new Rest\Pipeline())
			->pipe(new Rest\Stage\NewOrder($request->getUserId(), $request->getFUserId(), $request->getCurrencyCode(), $request->getCoupons()))
			->pipe(new Rest\Stage\OrderInitialize())
			->pipe(new Rest\Stage\OrderLocation($request->getAddress()))
			->pipe(new Rest\Stage\NewBasket($request->getItems()))
			->pipe(new Rest\Stage\OrderPaySystem())
			->pipe(new Rest\Stage\OrderFinalizer());
	}

	protected function collectorPipeline(Rest\Reference\EffectiveResponse $response) : Rest\Pipeline
	{
		return (new Rest\Pipeline())
			->pipe(new Rest\Stage\OrderCurrencyCollector($response, 'currencyCode'))
			->pipe(new Rest\Stage\OptionsCollector($response))
			->pipe(new Rest\Stage\OrderItemsCollector($response, 'cart.items'))
			->pipe(new Rest\Stage\CouponsCollector($response,  'cart.coupons'))
			->pipe(new Rest\Stage\OrderTotalCollector($response, 'cart.total'));
	}

	protected function collectorDelivery(Rest\Reference\EffectiveResponse $response, Request $request) : Rest\Pipeline
	{
		$result = new Rest\Pipeline();

		if ($request->getAddress() !== null)
		{
			$result->pipe(new Rest\Stage\OrderDeliveryCollector($response, 'shipping.availableCourierOptions'));
		}

		return $result;
	}

	protected function collectorYandexDelivery(Rest\Reference\EffectiveResponse $response, Request $request, Rest\State\OrderCalculation $state) : Rest\Pipeline
	{
		$result = new Rest\Pipeline();
		$yandexDelivery = $state->options->getDeliveryOptions()->getYandexDelivery();

		if ($yandexDelivery !== null && $request->getAddress() !== null)
		{
			$result
				->pipe(new Rest\OrderRender\Stage\ContactCollector($yandexDelivery, $response, 'shipping.yandexDelivery.warehouse'))
				->pipe(new Rest\OrderRender\Stage\WarehouseCollector($yandexDelivery, $response, 'shipping.yandexDelivery.warehouse.address'));
		}

		return $result;
	}
}