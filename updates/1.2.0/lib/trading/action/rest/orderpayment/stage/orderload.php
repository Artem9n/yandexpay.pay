<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderPayment\Stage;

use Bitrix\Main;
use Bitrix\Sale;
use Sale\Handlers\PaySystem\YandexPayHandler;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Action\Reference\Exceptions\DtoProperty;
use YandexPay\Pay\Trading\Action\Rest\State;

class OrderLoad
{
	protected $orderId;
	protected $merchantId;

	public function __construct(string $orderId, string $merchantId)
	{
		$this->orderId = $orderId;
		$this->merchantId = $merchantId;
	}

	public function __invoke(State\Payment $state)
	{
		$this->load($state);
		$this->resolvePayment($state);
		$this->resolveHandler($state);
		$this->validateMerchant($state);
	}

	protected function load(State\Payment $state) : void
	{
		if (!Main\Loader::includeModule('sale')) { return; }

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		/** @var \Bitrix\Sale\Order $orderClassName */
		$orderClassName = $registry->getOrderClassName();

		$state->order = $orderClassName::load($this->orderId);

		if ($state->order === null)
		{
			throw new DtoProperty('order not found', 'ORDER_NOT_FOUND');
		}
	}

	protected function resolvePayment(State\Payment $state) : void
	{
		/** @var \Bitrix\Sale\Payment $payment */
		foreach ($state->order->getPaymentCollection() as $payment)
		{
			if (!$payment->isInner())
			{
				$state->payment = $payment;
				break;
			}
		}

		if ($state->payment === null)
		{
			throw new DtoProperty('payment not found', 'OTHER');
		}
	}

	protected function resolveHandler(State\Payment $state) : void
	{
		$state->handler = $state->environment->getPaySystem()->getHandler($state->payment->getPaymentSystemId());

		Assert::typeOf($state->handler, YandexPayHandler::class, 'not YandexPayHandler');
	}

	protected function validateMerchant(State\Payment $state) : void
	{
		$merchantId = $state->handler->getParamValue($state->payment, 'MERCHANT_ID');

		if ($merchantId === null)
		{
			throw new DtoProperty('not setting payment merchantId', 'OTHER');
		}

		if ($this->merchantId !== $merchantId)
		{
			throw new DtoProperty('Invalid merchantId', 'OTHER');
		}
	}
}

