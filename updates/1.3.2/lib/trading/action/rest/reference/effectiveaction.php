<?php
namespace YandexPay\Pay\Trading\Action\Rest\Reference;

use Bitrix\Main;
use Bitrix\Sale;
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
		$this->request = $this->convertHttpToRequest(Request::class);
		$this->bootHttpHost($this->request->getHttpHost());
	}

	protected function bootJwt() : void
	{
		try
		{
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

	protected function bootSetup(int $setupId) : void
	{
		$setup = $this->loadSetup($setupId);
		$this->passTrading($setup);
	}

	protected function bootHttpHost(string $httpHost) : void
	{
		$isSetHttpHost = (string)Config::getOption('set_http_host', 'N');

		if ($isSetHttpHost === 'Y' && $httpHost !== '')
		{
			$_SERVER['HTTP_HOST'] = $httpHost;
		}
	}

	protected function bootMerchant(string $merchantId) : void
	{
		if (!Main\Loader::includeModule('sale')) { return; }

		$paySystemId = $this->options->getPaymentCard();
		$personTypeId = $this->setup->getPersonTypeId();

		$optionMerchantId = Sale\BusinessValue::get(
			'YANDEX_PAY_MERCHANT_ID',
			Sale\PaySystem\Service::PAY_SYSTEM_PREFIX . $paySystemId,
			$personTypeId
		);

		if ($optionMerchantId === null)
		{
			throw new RequestAuthentication('not setting payment merchantId');
		}

		if ($merchantId !== $optionMerchantId)
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

	protected function loadSetup(int $setupId) : TradingSetup\Model
	{
		$query = TradingSetup\RepositoryTable::getList([
			'filter' => [
				'=ID' => $setupId,
				'ACTIVE' => true,
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
		$state->logger = $this->logger;
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