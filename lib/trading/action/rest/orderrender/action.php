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
		$state = $this->makeState(Rest\State\OrderCalculation::class);

		(new Rest\Pipeline())
			->pipe($this->calculationPipeline($request))
			->pipe($this->collectorPipeline($response))
			->process($state);

		return $this->convertResponseToHttp($response);
	}

	protected function calculationPipeline(Request $request) : Rest\Pipeline
	{
		return (new Rest\Pipeline())
			->pipe(new Rest\Stage\NewOrder($request->getUserId(), $request->getCurrencyCode(), $request->getCoupons()))
			->pipe(new Rest\Stage\OrderLocation($request->getAddress()))
			->pipe(new Rest\Stage\NewBasket($request->getItems()))
			->pipe(new Rest\Stage\OrderFinalizer());
	}

	protected function collectorPipeline(Rest\Reference\EffectiveResponse $response) : Rest\Pipeline
	{
		return (new Rest\Pipeline())
			->pipe(new Rest\Stage\OrderCurrencyCollector($response, 'currencyCode'))
			->pipe(new Rest\Stage\OptionsCollector($response))
			->pipe(new Rest\Stage\OrderItemsCollector($response, 'cart.items'))
			->pipe(new Rest\Stage\CouponsCollector($response,  'cart.coupons'))
			->pipe(new Rest\Stage\OrderTotalCollector($response, 'cart.total'))
			->pipe(new Rest\Stage\OrderDeliveryCollector($response, 'shipping.availableCourierOptions'));
	}
}