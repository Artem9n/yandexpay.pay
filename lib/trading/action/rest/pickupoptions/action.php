<?php
namespace YandexPay\Pay\Trading\Action\Rest\PickupOptions;

use Bitrix\Main;
use YandexPay\Pay\Trading\Action\Rest;

class Action extends Rest\Reference\EffectiveAction
{
	public function process() : Main\HttpResponse
	{
		$request = $this->convertHttpToRequest(Request::class);
		$response = $this->makeResponse();
		$state = $this->makeState(Rest\State\PickupOptions::class);

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
			->pipe(new Rest\Stage\NewBasket($request->getItems()))
			->pipe(new Rest\Stage\OrderFinalizer());
	}

	protected function collectorPipeline(Rest\Reference\EffectiveResponse $response) : Rest\Pipeline
	{
		return (new Rest\Pipeline())
			->pipe(new Stage\PickupOptionsCollector($response, 'pickupOptions'));
	}
}