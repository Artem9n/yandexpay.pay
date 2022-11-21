<?php
namespace YandexPay\Pay\Trading\Action\Rest\OnboardPing;

use Bitrix\Main;
use YandexPay\Pay;
use YandexPay\Pay\Trading\Action\Rest;

class Action extends Rest\OnBoard\Action
{
	/** @var Request */
	protected $request;

	public function bootstrap() : void
	{
		$this->bootJson();
		$this->bootAccess();
	}

	public function process() : Main\HttpResponse
	{
		$response = $this->makeResponse();

		$onboardMerchantId = Pay\Config::getOption('onboard_merchant_id', null);
		$onboardApiKey = Pay\Config::getOption('onboard_api_key', null);

		if ($onboardMerchantId === null && $onboardApiKey === null)
		{
			throw new Rest\Exceptions\OnboardProcessed('no data received');
		}

		$response->setField('onboard', [
			'merchantId' => $onboardMerchantId,
			'apiKey' => $onboardApiKey,
		]);

		\COption::RemoveOption(Pay\Config::getModuleName(), 'onboard_merchant_id');
		\COption::RemoveOption(Pay\Config::getModuleName(), 'onboard_api_key');
		\COption::RemoveOption(Pay\Config::getModuleName(), 'merchant_token');

		return $this->convertResponseToHttp($response);
	}
}