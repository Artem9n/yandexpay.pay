<?php
namespace YandexPay\Pay\Trading\Action\Rest\Reference;

use Bitrix\Main;
use Bitrix\Sale\BusinessValue;
use Bitrix\Sale\PaySystem\Service;
use YandexPay\Pay\Config;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Action\Rest\Exceptions\RequestAuthentication;
use YandexPay\Pay\Trading\Action\Rest\State;
use YandexPay\Pay\Utils;

abstract class EffectiveAction extends HttpAction
{
	/** @var EffectiveRequest */
	protected $request;
	/** @var EffectiveResponse */
	protected $response;

	public function bootstrap() : void
	{
		if (!$this->httpRequest->get('DEV'))
		{
			$this->bootJwt();
			$this->bootMerchant();
		}

		$this->bootJson();
	}

	protected function bootJwt() : void
	{
		$filter = new Utils\JwtBodyFilter($this->jwkUrl());
		$this->httpRequest->addFilter($filter);
	}

	protected function bootJson() : void
	{
		$filter = new Utils\JsonBodyFilter();
		$this->httpRequest->addFilter($filter);
	}

	protected function bootMerchant() : void
	{
		$merchantId = BusinessValue::get('YANDEX_PAY_MERCHANT_ID', Service::PAY_SYSTEM_PREFIX . $this->options->getPaymentCard());
		$data = $this->httpRequest->getPostList()->toArray();

		if ($data['merchantId'] !== $merchantId)
		{
			throw new RequestAuthentication('Invalid merchantId');
		}
	}

	protected function jwkUrl() : string
	{
		$optionName = $this->isTestMode ? 'SANDBOX_JWK' : 'PUBLIC_JWK';
		$result = Config::getOption($optionName);

		Assert::isString($result, sprintf('options[%s]', $optionName));

		return $result;
	}

	/**
	 * @template T
	 * @param $className class-string<T>
	 *
	 * @return T
	 */
	protected function makeState(string $className) : State\Common
	{
		Assert::isSubclassOf($className, State\Common::class);

		return $this->configureState(new $className);
	}

	protected function configureState(State\Common $state) : State\Common
	{
		$this->configureStateCommon($state);

		return $state;
	}

	protected function configureStateCommon(State\Common $state) : void
	{
		$state->setup = $this->setup;
		$state->options = $this->options;
		$state->environment = $this->setup->getEnvironment();
		$state->isTestMode = $this->isTestMode;
	}

	/**
	 * @template T
	 * @param $className class-string<T>
	 *
	 * @return T
	 */
	protected function convertHttpToRequest(string $className) : EffectiveRequest
	{
		Assert::isSubclassOf($className, EffectiveRequest::class);

		$data = $this->httpRequest->getPostList()->toArray();

		return new $className($data);
	}

	protected function makeResponse() : EffectiveResponse
	{
		return new EffectiveResponse();
	}

	protected function convertResponseToHttp(EffectiveResponse $response) : Main\HttpResponse
	{
		return new Main\Engine\Response\Json([
			'status' => 'success',
			'data' => $response->getFields(),
		]);
	}
}