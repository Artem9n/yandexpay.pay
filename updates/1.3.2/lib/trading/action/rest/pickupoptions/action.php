<?php
namespace YandexPay\Pay\Trading\Action\Rest\PickupOptions;

use Bitrix\Main;
use YandexPay\Pay\Trading\Action\Rest;

class Action extends Rest\Reference\EffectiveAction
{
	/** @var Request */
	protected $request;

	public function bootstrap() : void
	{
		parent::bootstrap();
		$this->request = $this->convertHttpToRequest(Request::class);
		$this->bootSetup($this->request->getSetupId());
		$this->bootMerchant($this->request->getMerchantId());
	}

	public function process() : Main\HttpResponse
	{
		$response = $this->makeResponse();
		$state = $this->makeState(Rest\State\PickupOptions::class);

		(new Rest\Pipeline())
			->pipe($this->calculationPipeline())
			->pipe(new Stage\PickupOptionsCollector($response, 'pickupOptions', $this->request->getBounds()->getFields()))
			->process($state);

		return $this->convertResponseToHttp($response);
	}

	protected function calculationPipeline() : Rest\Pipeline
	{
		return (new Rest\Pipeline())
			->pipe(new Rest\Stage\NewOrder(
				$this->request->getUserId(),
				$this->request->getFUserId(),
				$this->request->getCurrencyCode(),
				$this->request->getCoupons()
			))
			->pipe(new Rest\Stage\OrderInitialize())
			->pipe(new Rest\Stage\NewBasket($this->request->getItems()))
			->pipe(new Rest\Stage\OrderPaySystem())
			->pipe(new Rest\Stage\OrderFinalizer());
	}
}