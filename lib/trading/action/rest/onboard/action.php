<?php
namespace YandexPay\Pay\Trading\Action\Rest\Onboard;

use Bitrix\Main;
use YandexPay\Pay;
use YandexPay\Pay\Trading\Action\Rest;
use YandexPay\Pay\Trading\Action\Rest\OnBoardPing\Request;

class Action extends Rest\Reference\EffectiveAction
{
	/** @var Request */
	protected $request;

	public function bootstrap() : void
	{
		parent::bootstrap();
		$this->bootAccess();
	}

	protected function bootAccess() : void
	{
		$this->request = $this->convertHttpToRequest(Request::class);
		$tokenRequest = $this->request->getMerchantAuthToken();

		$tokenSite = Pay\Config::getOption('merchant_token', null);

		if ($tokenRequest !== $tokenSite)
		{
			throw new Pay\Trading\Action\Reference\Exceptions\DtoProperty('invalid token onboard');
		}
	}

	public function process() : Main\HttpResponse
	{
		$response = $this->makeResponse();

		Pay\Config::setOption('onboard_merchant_id', $this->request->getMerchantId());
		Pay\Config::setOption('onboard_api_key', $this->request->getApiKey());

		return $this->convertResponseToHttp($response);
	}
}