<?php
namespace YandexPay\Pay\Trading\Action\Rest\ButtonData;

use Bitrix\Main;
use YandexPay\Pay\Trading\Action\Rest;

class Action extends Rest\Reference\EffectiveAction
{
	public function bootstrap() : void
	{
		$this->bootJson();
		$this->bootSetup();
	}

	protected function getSetupId() : int
	{
		return $this->httpRequest->get('setupId');
	}

	public function process() : Main\HttpResponse
	{
		$request = $this->convertHttpToRequest(Request::class);
		$response = $this->makeResponse();
		$state = $this->makeState(Rest\State\OrderCalculation::class);

		(new Rest\Pipeline())
			->pipe($this->calculationPipeline($request))
			->pipe($this->collectorPipeline($response))
			->pipe(new Rest\ButtonData\Stage\MetaCollector($response, $request, 'metadata'))
			->process($state);

		return $this->convertResponseToHttp($response);
	}

	protected function calculationPipeline(Request $request) : Rest\Pipeline
	{
		return (new Rest\Pipeline())
			->pipe(new Rest\ButtonData\Stage\SearchUser())
			->pipe(new Rest\Stage\NewOrder())
			->pipe(new Rest\ButtonData\Stage\WakeUpBasket($request->getMode(), $request->getProductId()));
	}

	protected function collectorPipeline(Rest\Reference\EffectiveResponse $response) : Rest\Pipeline
	{
		return (new Rest\Pipeline())
			->pipe(new Rest\Stage\OrderTotalCollector($response, 'total'))
			->pipe(new Rest\Stage\OrderItemsCollector($response, 'items'));
	}
}