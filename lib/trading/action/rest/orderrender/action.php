<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderRender;

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
		$setupId = $this->request->getSetupId();
		if ($setupId > 0)
		{
			$this->bootSetup($setupId);
		}

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
			->pipe(new Rest\OrderRender\Stage\OptionsCollector($response))
			->pipe(new Rest\OrderRender\Stage\ItemsCollector($response, 'cart.items'))
			->pipe(new Rest\OrderRender\Stage\DiscountsCollector($response))
			->pipe(new Rest\OrderRender\Stage\TotalCollector($response, 'cart.total'))
			->process($this->stateOrder);
	}

	protected function processCheckout(Rest\Reference\EffectiveResponse $response) : void
	{
		$state = $this->makeState(Rest\State\OrderCalculation::class);

		(new Rest\Pipeline())
			->pipe($this->calculationPipeline())
			->pipe($this->collectorYandexDelivery($response, $state))
			->pipe($this->collectorPipeline($response))
			->pipe($this->collectorDelivery($response))
			->process($state);
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
			->pipe(new Rest\Stage\OrderLocation($this->request->getAddress()))
			->pipe(new Rest\Stage\NewBasket($this->request->getItems()))
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
			->pipe(new Rest\Stage\DiscountsCollector($response))
			->pipe(new Rest\Stage\OrderTotalCollector($response, 'cart.total'));
	}

	protected function collectorDelivery(Rest\Reference\EffectiveResponse $response) : Rest\Pipeline
	{
		$result = new Rest\Pipeline();

		if ($this->request->getAddress() !== null)
		{
			$result->pipe(new Rest\Stage\OrderDeliveryCollector($response, 'shipping.availableCourierOptions'));
		}
		else
		{
			$result->pipe(new Rest\Stage\DeliveryMethodsCollector($response, 'shipping.availableMethods'));
		}

		return $result;
	}

	protected function collectorYandexDelivery(Rest\Reference\EffectiveResponse $response, Rest\State\OrderCalculation $state) : Rest\Pipeline
	{
		$pipeline = new Rest\Pipeline();
		$address = $this->request->getAddress();
		$yandexDelivery = $state->options->getDeliveryOptions()->getYandexDelivery();

		if ($yandexDelivery === null || $address === null) { return $pipeline; }

		return $pipeline
			->pipe(new Rest\Stage\YandexDeliveryCollector(
				$yandexDelivery,
				$response,
				$address,
				'shipping.yandexDelivery.warehouse'
			));
	}
}