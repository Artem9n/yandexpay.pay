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

		if ($this->httpRequest->get('orderId') === null)
		{
			$this->bootSetup();
			$this->bootMerchant();
		}
		else
		{
			$this->bootMerchantOrder();
		}
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

	protected function bootMerchant(int $paySystemId = null, string $personTypeId = null) : void
	{
		if (!Main\Loader::includeModule('sale')) { return; }

		$paySystemId = $paySystemId ?? $this->options->getPaymentCard();
		$personTypeId = $personTypeId ?? $this->setup->getPersonTypeId();

		$merchantId = Sale\BusinessValue::get(
			'YANDEX_PAY_MERCHANT_ID',
			Sale\PaySystem\Service::PAY_SYSTEM_PREFIX . $paySystemId,
			$personTypeId
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

	protected function bootMerchantOrder() : void
	{
		$orderId = $this->httpRequest->get('orderId');

		if (!Main\Loader::includeModule('sale')) { return; }

		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		/** @var \Bitrix\Sale\Order $orderClassName */
		$orderClassName = $registry->getOrderClassName();
		/** @var \Bitrix\Sale\Order $order */
		$order = $orderClassName::load($orderId);

		if ($order === null)
		{
			throw new DtoProperty('order not found', 'ORDER_NOT_FOUND');
		}

		$paySystemId = null;

		$paymentCollection = $order->getPaymentCollection();

		/** @var \Bitrix\Sale\Payment $paymentItem */
		foreach ($paymentCollection as $paymentItem)
		{
			if (!$paymentItem->isInner())
			{
				$paySystemId = $paymentItem->getPaymentSystemId();
				break;
			}
		}

		if ($paySystemId === null)
		{
			throw new DtoProperty('paySystem not found', 'PAYSYSTEM_NOT_FOUND');
		}

		$this->bootMerchant($paySystemId, $order->getPersonTypeId());
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