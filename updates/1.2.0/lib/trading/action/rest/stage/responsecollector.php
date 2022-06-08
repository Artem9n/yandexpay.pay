<?php
namespace YandexPay\Pay\Trading\Action\Rest\Stage;

use YandexPay\Pay\Trading\Action\Rest\Reference\EffectiveResponse;

abstract class ResponseCollector
{
	protected $response;
	protected $key;

	public function __construct(EffectiveResponse $response, string $key = '')
	{
		$this->response = $response;
		$this->key = $key;
	}

	protected function write($data, string $key = null) : void
	{
		$key = $key ?? $this->key;

		$this->response->setField($key, $data);
	}
}

