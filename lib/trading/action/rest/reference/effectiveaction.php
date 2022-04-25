<?php
namespace YandexPay\Pay\Trading\Action\Rest\Reference;

use Bitrix\Main;
use Bitrix\Sale\BusinessValue;
use Bitrix\Sale\PaySystem\Service;
use YandexPay\Pay\Config;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Action\Reference\Exceptions\DtoProperty;
use YandexPay\Pay\Trading\Action\Rest\Exceptions\RequestAuthentication;
use YandexPay\Pay\Trading\Action\Rest\State;
use YandexPay\Pay\Trading\Entity\Registry as EntityRegistry;
use YandexPay\Pay\Utils;
use YandexPay\Pay\Trading\Setup as TradingSetup;

abstract class EffectiveAction extends HttpAction
{
	/** @var EffectiveRequest */
	protected $request;
	/** @var EffectiveResponse */
	protected $response;

	public function bootstrap() : void
	{
		$this->bootJwt();
		$this->bootJson();
		$this->bootSetup();
		$this->bootMerchant();
	}

	protected function bootJwt() : void
	{
		try {
			$filter = new Utils\JwtBodyFilter($this->jwkUrl());
			$this->httpRequest->addFilter($filter);
		}
		catch (\Exception $exception)
		{
			$this->isTestMode = !$this->isTestMode;
			$filter = new Utils\JwtBodyFilter($this->jwkUrl());
			$this->httpRequest->addFilter($filter);
		}
	}

	protected function bootJson() : void
	{
		$filter = new Utils\JsonBodyFilter();
		$this->httpRequest->addFilter($filter);
	}

	protected function bootSetup() : void
	{
		$setup = $this->loadSetup();
		$this->passTrading($setup);
	}

	protected function bootMerchant() : void
	{
		if(!Main\Loader::includeModule('sale')) { return; }

		$merchantId = BusinessValue::get(
			'YANDEX_PAY_MERCHANT_ID',
			Service::PAY_SYSTEM_PREFIX . $this->options->getPaymentCard(),
			$this->setup->getPersonTypeId()
		);

		if ($merchantId === null)
		{
			throw new RequestAuthentication('not setting payment merchantId');
		}

		if ($this->httpRequest->get('merchantId') !== $merchantId)
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

	protected function loadSetup() : TradingSetup\Model
	{
		$setupId = $this->getSetupId();

		$query = TradingSetup\RepositoryTable::getList([
			'filter' => [
				'=ID' => $setupId
			],
			'limit' => 1,
		]);

		$result = $query->fetchObject();

		if ($result === null)
		{
			throw new DtoProperty('setup not found');
		}

		return $result;
	}

	protected function getSetupId() : int
	{
		[$userId, $fUserId, $setupId] = explode(':', $this->httpRequest->get('metadata'));

		return (int)$setupId;
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
		$state->environment = EntityRegistry::getEnvironment();
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