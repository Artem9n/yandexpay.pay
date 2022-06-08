<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderStatus\Stage;

use Bitrix\Main;
use Bitrix\Sale;
use Sale\Handlers\PaySystem\YandexPayHandler;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Action\Rest\State;
use YandexPay\Pay\Trading\Entity\Registry as EntityRegistry;

class Load
{
	protected $event;

	public function __construct(Main\Event $event)
	{
		$this->event = $event;
	}

	public function __invoke(State\OrderStatus $state)
	{
		$state->environment = EntityRegistry::getEnvironment();

		$this->loadOrder($state);
		$this->resolvePayment($state);
		$this->resolveHandler($state);
		$this->resolveRest($state);
		$this->resolveStatus($state);
	}

	protected function loadOrder(State\OrderStatus $state) : void
	{
		$state->order = $this->event->getParameter('ENTITY');

		if ($state->order === null)
		{
			throw new Main\SystemException('not found order');
		}
	}

	protected function resolvePayment(State\OrderStatus $state) : void
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
			throw new Main\SystemException('not found payment');
		}
	}

	protected function resolveHandler(State\OrderStatus $state) : void
	{
		$state->handler = $state->environment->getPaySystem()->getHandler($state->payment->getPaymentSystemId());

		Assert::typeOf($state->handler, YandexPayHandler::class, 'not YandexPayHandler');
	}

	protected function resolveRest(State\OrderStatus $state) : void
	{
		if (!$state->handler->wakeUpGateway($state->payment)->isRest())
		{
			throw new Main\SystemException('not rest');
		}
	}

	protected function resolveStatus(State\OrderStatus $state) : void
	{
		$state->status = $state->order->getField('STATUS_ID');
	}
}