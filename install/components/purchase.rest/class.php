<?php

namespace YandexPay\Pay\Components;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Sale\Handlers\PaySystem\YandexPayHandler;
use YandexPay\Pay\Config;
use YandexPay\Pay\Exceptions;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Setup as TradingSetup;
use YandexPay\Pay\Trading\Action as TradingAction;
use YandexPay\Pay\Trading\Settings as TradingSettings;
use YandexPay\Pay\Trading\Entity\Reference as EntityReference;
use YandexPay\Pay\Utils;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

Loc::loadMessages(__FILE__);

class PurchaseRest extends \CBitrixComponent
{
	private const HTTP_STATUS_200 = '200 OK';
	private const HTTP_STATUS_400 = '400 Bad Request';

	/** @var EntityReference\Environment */
	protected $environment;
	/** @var TradingSettings\Options */
	protected $options;
	/** @var TradingSetup\Model */
	protected $setup;
	/** @var YandexPayHandler */
	protected $payHandler;

	public function executeComponent()
	{
		try
		{
			$this->loadModules();
			$this->parseUrl();
			$this->bootstrap();

			$action = $this->resolveAction();

			$this->parseRequest($action);
			$this->callAction($action);
		}
		catch (Main\SystemException $exception)
		{
			$response = new Main\Engine\Response\Json([
				'status' => 'fail',
				'reasonCode' => (string)($exception->getCode() ?: 'UNKNOWN'),
				'reason' => $exception->getMessage(),
			]);
			$response->setStatus(static::HTTP_STATUS_400);

			$this->sendResponse($response);
		}
	}

	protected function rootAction() : void
	{
		$response = new Main\HttpResponse();
		$response->setStatus(static::HTTP_STATUS_200);
		$response->setContent('OK');

		$this->sendResponse($response);
	}

	protected function orderCreateAction() : void
	{
		$dto = $this->makeDto(TradingAction\Rest\OrderCreate::class);

		$order = $this->getOrder(null, $dto->getCurrencyCode());

		$this->fillPersonType($order);
		$this->fillBasket($order, $dto->getItems());

		$order->finalize();

		$this->sendResponse(new Main\Engine\Response\Json([
			'status' => 'success',
			'data' => [
				'currencyCode' => $dto->getCurrencyCode(),
				'order' => [
					'items' => $this->collectOrderItems($order),
					'discounts' => $this->collectOrderDiscounts($order),
					'totalAmount' => $this->collectOrderTotal($order),
				],
			],
		]));
	}

	protected function collectOrderItems(EntityReference\Order $order) : array
	{
		$result = [];

		foreach ($order->getOrderableItems() as $basketCode)
		{
			$basketResult = $order->getBasketItemData($basketCode);
			$basketData = $basketResult->getData();

			$result[] = [
				'id' => (string)$basketData['PRODUCT_ID'],
				'unitPrice' => (float)$basketData['BASE_PRICE'],
				'discountedUnitPrice' => (float)$basketData['PRICE'],
				'subtotalAmount' => (float)$basketData['TOTAL_BASE_PRICE'],
				'totalAmount' => (float)$basketData['TOTAL_PRICE'],
				'title' => (string)$basketData['NAME'],
				'quantity' => [
					'count' => (float)$basketData['QUANTITY'],
					//'available' => (float), todo
					//'label' => (string), todo
				],
			];
		}

		return $result;
	}

	protected function collectOrderDiscounts(EntityReference\Order $order) : array
	{
		return []; // todo
	}

	protected function collectOrderTotal(EntityReference\Order $order) : array
	{
		return [
			'amount' => $order->getOrderPrice(),
			'label' => null, // todo
		];
	}

	protected function getOrder(int $userId = null, string $currency = null) : EntityReference\Order
	{
		return $this->environment->getOrderRegistry()->createOrder(
			$this->setup->getSiteId(),
			$userId,
			$currency
		);
	}

	protected function fillPersonType(EntityReference\Order $order) : void
	{
		Assert::notNull($this->setup->getPersonTypeId(), 'personal type');

		$personTypeResult = $order->setPersonType($this->setup->getPersonTypeId());

		Exceptions\Facade::handleResult($personTypeResult);
	}

	protected function fillBasket(EntityReference\Order $order, TradingAction\Rest\Cart\Items $products) : void
	{
		$order->initEmptyBasket();

		/** @var TradingAction\Rest\Cart\Item $product */
		foreach ($products as $product)
		{
			$productId = $product->getId();
			$quantity = $product->getCount();

			$addResult = $order->addProduct($productId, $quantity);

			Exceptions\Facade::handleResult($addResult);
		}
	}

	/**
	 * @template T
	 * @param class-string<T> $className
	 *
	 * @return T
	 */
	protected function makeDto(string $className) : TradingAction\Reference\Skeleton
	{
		Assert::isSubclassOf($className, TradingAction\Reference\Skeleton::class);

		$data = $this->request->getPostList()->toArray();

		return new $className($data);
	}

	protected function loadModules() : void
	{
		$moduleName = 'yandexpay.pay';

		if (!Main\Loader::includeModule($moduleName))
		{
			$message = $this->getLang('MODULE_NOT_INSTALLED', [ '#MODULE_ID#' => $moduleName ]);

			throw new Main\SystemException($message);
		}
	}

	protected function parseUrl() : void
	{
		$url = $this->request->getRequestedPage();
		$url = $this->normalizeDirectory($url);
		[$left] = $this->sliceUrlSefFolder($url);
		[$left, $setupId] = $this->sliceUrlSetupId($left);

		$parameters = [
			'ACTION' => ltrim($left),
		];
		$parameters += array_filter([
			'SETUP_ID' => $setupId,
		]);

		$this->presetParameters($parameters);
	}

	protected function sliceUrlSefFolder(string $url) : array
	{
		$folder = $this->requireParameter('SEF_FOLDER');
		$folder = $this->normalizeDirectory($folder);

		if (mb_stripos($url, $folder) !== 0)
		{
			throw new Main\SystemException($this->getLang('REQUEST_OUTSIDE_SEF_FOLDER'));
		}

		$leftUrl = mb_substr($url, mb_strlen($folder));

		return [$leftUrl, $folder];
	}

	protected function sliceUrlSetupId(string $url) : array
	{
		if (!preg_match('#^/p(\d+)(/.*$|$)#', $url, $matches)) { return [$url, null]; }

		return [$matches[2], (int)$matches[1]];
	}

	protected function normalizeDirectory(string $path) : string
	{
		$result = Main\IO\Path::normalize($path);
		$result = preg_replace('#/index\.php$#', '', $result);

		if ($result !== '/')
		{
			$result = rtrim($result, '/');
		}

		return $result;
	}

	protected function bootstrap() : void
	{
		$this->setup = $this->loadSetup();
		$this->options = $this->setup->wakeupOptions();
		$this->environment = $this->setup->getEnvironment();
		$this->payHandler = $this->loadPayHandler();
	}

	protected function loadSetup() : TradingSetup\Model
	{
		$setupId = $this->getParameter('SETUP_ID');

		if ($setupId !== null)
		{
			$filter = [
				'=ID' => $setupId,
			];
		}
		else
		{
			$filter = [
				'=SITE_ID' => SITE_ID,
				'=ACTIVE' => true,
			];
		}

		$query = TradingSetup\RepositoryTable::getList([
			'filter' => $filter,
			'limit' => 1,
		]);

		$result = $query->fetchObject();

		if ($result === null)
		{
			throw new Main\SystemException($this->getLang('SETUP_NOT_FOUND'));
		}

		return $result;
	}

	protected function loadPayHandler() : YandexPayHandler
	{
		/** @var YandexPayHandler $result */
		$paySystemId = $this->options->getPaymentCard();
		$result = $this->environment->getPaySystem()->getHandler($paySystemId);

		Assert::typeOf($result, YandexPayHandler::class, 'paySystem');

		return $result;
	}

	protected function parseRequest(string $action) : void
	{
		if ($action === 'root') { return; }

		if ($action !== 'hello')
		{
			$this->decodeRequest();
		}
		else
		{
			$this->readRequest();
		}
	}

	protected function decodeRequest() : void
	{
		$jwkUrl = $this->getJwkUrl();
		$filter = new Utils\JwtBodyFilter($jwkUrl);

		$this->request->addFilter($filter);
	}

	protected function readRequest() : void
	{
		$filter = new Utils\JsonBodyFilter();

		$this->request->addFilter($filter);
	}

	protected function getJwkUrl() : string
	{
		$optionName = $this->payHandler->isTestMode() ? 'SANDBOX_JWK' : 'PUBLIC_JWK';
		$result = Config::getOption($optionName);

		Assert::isString($result, sprintf('options[%s]', $optionName));

		return $result;
	}

	protected function resolveAction() : string
	{
		$action = (string)$this->requireParameter('ACTION');
		$action = ltrim($action, '/');

		if ($action === '')
		{
			$action = 'root';
		}

		return $action;
	}

	protected function callAction(string $action) : void
	{
		$method = $this->actionToMethodName($action);

		if (!method_exists($this, $method))
		{
			throw new Main\NotImplementedException(sprintf('action %s not implemented', $action));
		}

		$this->{$method}();
	}

	protected function actionToMethodName(string $action) : string
	{
		$parts = explode('/', $action);
		$parts = array_map('ucfirst', $parts);
		$method = implode('', $parts);
		$method = lcfirst($method);

		return $method . 'Action';
	}

	protected function sendResponse(Main\HttpResponse $response) : void
	{
		global $APPLICATION;

		/** @var Main\Application $app */
		$app = Main\Application::getInstance();

		$APPLICATION->RestartBuffer();
		$app->end(0, $response);
	}

	protected function getLang(string $code, $replace = null, $language = null): string
	{
		return Main\Localization\Loc::getMessage('YANDEX_PAY_PURCHASE_REST_' . $code, $replace, $language);
	}

	protected function getParameter(string $name)
	{
		return $this->arParams[$name] ?? null;
	}

	protected function requireParameter(string $name)
	{
		if (!isset($this->arParams[$name]))
		{
			$message = $this->getLang('PARAMETER_REQUIRED', [ '#NAME#' => $name ]);
			throw new Main\SystemException($message);
		}

		return $this->arParams[$name];
	}

	protected function presetParameters(array $values) : void
	{
		foreach ($values as $name => $value)
		{
			if (isset($this->arParams[$name])) { continue; }

			$this->arParams[$name] = $value;
		}
	}
}
