<?php
namespace YandexPay\Pay\Trading\Action\Rest\Authorize\Stage;

use YandexPay\Pay\Trading\Action\Rest\Reference\EffectiveResponse;
use YandexPay\Pay\Trading\Action\Rest\Stage\ResponseCollector;
use YandexPay\Pay\Trading\Action\Rest\State;

class RedirectCollector extends ResponseCollector
{
	protected $successUrl;

	public function __construct(EffectiveResponse $response, string $successUrl, string $key = '')
	{
		parent::__construct($response, $key);
		$this->successUrl = trim($successUrl, '/');
	}

	public function __invoke(State\Payment $state)
	{
		$orderId = $state->order->getId();

		$accountNumber = $state->order->getField('ACCOUNT_NUMBER');

		if ((string)$accountNumber !== '')
		{
			$orderId = urlencode($accountNumber);
		}

		$this->write(sprintf('/%s/?ORDER_ID=%s', $this->successUrl, $orderId));
	}
}

