<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderCreate;

use Bitrix\Main;
use YandexPay\Pay\Trading\Action\Rest;

class Action extends Rest\Reference\EffectiveAction
{
	/** @var Request */
	protected $request;
	/** @var Rest\State\Order */
	protected $stateOrder;

	public function bootstrap() : void
	{
		parent::bootstrap();
		$this->bootMerchantOrder();
	}

	protected function bootMerchantOrder() : void
	{
		$this->request = $this->convertHttpToRequest(Request::class);

		if ($this->request->getOrderId() !== null)
		{
			$this->stateOrder = $this->makeState(Rest\State\Order::class);

			(new Rest\Pipeline())
				->pipe(new Rest\Stage\OrderLoad(
					$this->request->getOrderId(),
					$this->request->getMerchantId()
				))
				->process($this->stateOrder);
		}
		else
		{
			$this->bootSetup($this->request->getSetupId());
			$this->bootMerchant($this->request->getMerchantId());
		}
	}

	public function process() : Main\HttpResponse
	{
		$response = $this->makeResponse();

		if ($this->request->getOrderId() !== null)
		{
			$this->processOrder($response);
		}
		else
		{
			$this->processCheckout($response);
		}

		return $this->convertResponseToHttp($response);
	}

	protected function processOrder(Rest\Reference\EffectiveResponse $response) : void
	{
		(new Rest\Pipeline())
			->pipe(new Rest\OrderCreate\Stage\Order\FinishCollector($response, 'orderId'))
			->pipe(new Rest\OrderCreate\Stage\Order\MetaCollector($response, 'metadata'))
			->process($this->stateOrder);
	}

	protected function processCheckout(Rest\Reference\EffectiveResponse $response) : void
	{
		$state = $this->makeState(Rest\State\OrderCalculation::class);

		(new Rest\Pipeline())
			->pipe($this->calculationPipeline())
			->pipe($this->collectorPipeline($response))
			->process($state);
	}

	protected function calculationPipeline() : Rest\Pipeline
	{
		return (new Rest\Pipeline())
			->pipe(new Rest\OrderCreate\Stage\OrderUser($this->request))
			->pipe(new Rest\Stage\NewOrder(
				$this->request->getUserId(),
				$this->request->getFUserId(),
				$this->request->getCurrencyCode(),
				$this->request->getCoupons()
			))
			->pipe(new Rest\Stage\OrderInitialize())
			->pipe(new Rest\OrderCreate\Stage\OrderStatus())
			->pipe(new Rest\OrderCreate\Stage\OrderProperties($this->request))
			->pipe(new Rest\Stage\NewBasket($this->request->getItems()))
			->pipe($this->stageDelivery())
			->pipe(new Rest\Stage\OrderFinalizer())
			->pipe(new Stage\DeliveryCalculate())
			->pipe(new Rest\OrderCreate\Stage\OrderPaySystem($this->request))
			->pipe($this->stageDeliveryProperties())
			->pipe(new Rest\OrderCreate\Stage\RelatedProperties())
			->pipe(new Rest\OrderCreate\Stage\OrderCheck($this->request))
			->pipe(new Rest\OrderCreate\Stage\OrderAdd($this->request));
	}

	protected function stageDelivery() : Rest\Pipeline
	{
		$result = new Rest\Pipeline();
		$type = $this->request->getDeliveryType();

		if ($type === 'PICKUP')
		{
			$result->pipe(new Rest\OrderCreate\Stage\OrderPickup($this->request));
		}
		elseif ($type === 'YANDEX_DELIVERY')
		{
			$result
				->pipe(new Rest\Stage\OrderLocation($this->request->getAddress()))
				->pipe(new Rest\OrderCreate\Stage\OrderYandexDelivery($this->request));
		}
		else
		{
			$result
				->pipe(new Rest\Stage\OrderLocation($this->request->getAddress()))
				->pipe(new Rest\OrderCreate\Stage\OrderDelivery($this->request));
		}

		return $result;
	}

	protected function stageDeliveryProperties() : Rest\Pipeline
	{
		$result = new Rest\Pipeline();
		$type = $this->request->getDeliveryType();

		if ($type === 'PICKUP')
		{
			$result->pipe(new Rest\OrderCreate\Stage\PickupProperties($this->request->getPickup()));
		}
		else
		{
			$result->pipe(new Rest\OrderCreate\Stage\DeliveryProperties($this->request->getAddress()));
		}

		return $result;
	}

	protected function collectorPipeline(Rest\Reference\EffectiveResponse $response) : Rest\Pipeline
	{
		return (new Rest\Pipeline())
			->pipe(new Rest\OrderCreate\Stage\OrderCollector($response, 'orderId'))
			->pipe(new Rest\OrderCreate\Stage\MetaCollector($response, 'metadata'));
	}
}