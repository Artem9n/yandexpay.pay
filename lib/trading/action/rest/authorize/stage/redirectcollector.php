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

	public function __invoke(State\Order $state)
	{
		$orderId = $state->order->getId();

		$accountNumber = $state->order->getField('ACCOUNT_NUMBER');

		if ((string)$accountNumber !== '')
		{
			$orderId = urlencode($accountNumber);
		}

		$this->write($this->getRedirectUrl($orderId));
	}

	protected function getRedirectUrl(string $orderId) : string
	{
		if (mb_stripos($this->successUrl, '#ORDER_ID#') !== false)
		{
			$result = str_replace('#ORDER_ID#', $orderId, '/' . $this->successUrl);
		}
		else if (preg_match('/.php|.html/', $this->successUrl))
		{
			$result = sprintf('/%s?ORDER_ID=%s', $this->successUrl, $orderId);
		}
		else
		{
			$result = sprintf('/%s/?ORDER_ID=%s', $this->successUrl, $orderId);
		}

		return $result;
	}
}

