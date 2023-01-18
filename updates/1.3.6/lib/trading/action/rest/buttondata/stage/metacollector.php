<?php
namespace YandexPay\Pay\Trading\Action\Rest\ButtonData\Stage;

use YandexPay\Pay\Config;
use YandexPay\Pay\Trading\Action\Rest\ButtonData\Request;
use YandexPay\Pay\Trading\Action\Rest\Reference\EffectiveResponse;
use YandexPay\Pay\Trading\Action\Rest\Stage\ResponseCollector;
use YandexPay\Pay\Trading\Action\Rest\State;

class MetaCollector extends ResponseCollector
{
	protected $setupId;

	public function __construct(EffectiveResponse $response, Request $request, string $key = '')
	{
		parent::__construct($response, $key);
		$this->setupId = $request->getSetupId();
	}

	public function __invoke(State\OrderCalculation $state)
	{
		$userData = [
			(string)$state->order->getUserId(),
			(string)$state->fUserId,
			(string)$this->setupId,
		];

		$isSetHttpHost = (string)Config::getOption('set_http_host', 'N');
		$regionCookie = (string)Config::getOption('region_cookie', '');

		$userData[] = $isSetHttpHost === 'Y' ? $_SERVER['HTTP_HOST'] : '';
		$userData[] = $regionCookie !== '' ? $_COOKIE[$regionCookie] : '';

		$this->write(implode(':', $userData));
	}
}

