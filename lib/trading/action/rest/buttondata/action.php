<?php
namespace YandexPay\Pay\Trading\Action\Rest\ButtonData;

use Bitrix\Main;
use YandexPay\Pay\Trading\Action\Rest;

class Action extends Rest\Reference\EffectiveAction
{
	/** @var Request */
	protected $request;

	public function bootstrap() : void
	{
		$this->bootJson();
		$this->request = $this->convertHttpToRequest(Request::class);
		$this->bootSetup($this->request->getSetupId());
	}

	public function process() : Main\HttpResponse
	{
		$response = $this->makeResponse();
		$state = $this->makeState(Rest\State\OrderCalculation::class);

		(new Rest\Pipeline())
			->pipe($this->calculationPipeline())
			->pipe($this->collectorPipeline($response))
			->pipe(new Rest\ButtonData\Stage\MetaCollector($response, $this->request, 'metadata'))
			->process($state);

		return $this->convertResponseToHttp($response);
	}

	protected function calculationPipeline() : Rest\Pipeline
	{
		return (new Rest\Pipeline())
			->pipe(new Rest\ButtonData\Stage\SearchUser())
			->pipe(new Rest\Stage\NewOrder())
			->pipe(new Rest\ButtonData\Stage\WakeUpBasket($this->request->getMode(), $this->request->getProductId()));
	}

	protected function collectorPipeline(Rest\Reference\EffectiveResponse $response) : Rest\Pipeline
	{
		return (new Rest\Pipeline())
			->pipe(new Rest\Stage\OrderTotalCollector($response, 'total'))
			->pipe(new Rest\Stage\OrderItemsCollector($response, 'items'));
	}
}