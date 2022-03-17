<?php

namespace YandexPay\Pay\Components;

use Bitrix\Main;
use Bitrix\Sale;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Order;
use Sale\Handlers\PaySystem\YandexPayHandler;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Setup as TradingSetup;
use YandexPay\Pay\Trading\Action as TradingAction;
use YandexPay\Pay\Trading\Settings as TradingSettings;
use YandexPay\Pay\Trading\Entity\Reference as EntityReference;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

Loc::loadMessages(__FILE__);

class PurchaseRest extends \CBitrixComponent
{
	private const HTTP_STATUS_400 = '400 Bad Request';
	private const HTTP_STATUS_403 = '403 Forbidden';
	private const HTTP_STATUS_404 = '404 Not Found';
	private const HTTP_STATUS_500 = '500 Internal server error';

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

			$path = $this->getActionPath();
			$action = $this->resolveAction($path);

			$action->passTrading($this->setup, $this->payHandler->isTestMode());
			$action->passHttp($this->request);
			$action->bootstrap();

			$response = $action->process();

			$this->sendResponse($response);
		}
		catch (TradingAction\Rest\Exceptions\ActionNotImplemented $exception)
		{
			$response = $this->makeExceptionResponse($exception, static::HTTP_STATUS_404, [
				'reasonCode' => 'NOT_FOUND',
				'reason' => 'Action not found',
			]);

			$this->sendResponse($response);
		}
		catch (TradingAction\Rest\Exceptions\RequestAuthentication $exception)
		{
			$response = $this->makeExceptionResponse($exception, static::HTTP_STATUS_403, [
				'reasonCode' => 'FORBIDDEN',
			]);

			$this->sendResponse($response);
		}
		catch (TradingAction\Reference\Exceptions\DtoProperty $exception)
		{
			$response = $this->makeExceptionResponse($exception, static::HTTP_STATUS_400, [
				'reasonCode' => $exception->getParameter()
			]);

			$this->sendResponse($response);
		}
		catch (\Throwable $exception)
		{
			$response = $this->makeExceptionResponse($exception, static::HTTP_STATUS_500);

			$this->sendResponse($response);
		}
	}

	protected function webhookAction() : void
	{
		$dto = $this->makeDto(TradingAction\Rest\Webhook::class);
		$this->callWebhook($dto);
	}

	protected function transactionStatusUpdateRefundEvent(TradingAction\Rest\Webhook $request) : void
	{
		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		/** @var Order $orderClassName */
		$orderClassName = $registry->getOrderClassName();
		$order = $orderClassName::load($request->getExternalOrderId());

		if ($order === null)
		{
			$errorMessage = 'order not undefined';//$this->getLang('ORDER_ACCEPT_SAVE_RESULT_ID_NOT_SET');
			Sale\PaySystem\Logger::addError($errorMessage);
			throw new Main\SystemException($errorMessage);
		}

		if ($order->isCanceled())
		{
			$errorMessage = 'order canceled';
			Sale\PaySystem\Logger::addError($errorMessage);
			throw new Main\SystemException($errorMessage);
		}

		if (!$order->isPaid())
		{
			$errorMessage = 'order is not paid';//$this->getLang('ORDER_ACCEPT_SAVE_RESULT_ID_NOT_SET');
			Sale\PaySystem\Logger::addError($errorMessage);
			throw new Main\SystemException($errorMessage);
		}

		$collection = $order->getPaymentCollection();
		$payment = null;

		/** @var Sale\Payment $model */
		foreach ($collection as $model)
		{
			if ($model->isInner()) { continue; }
			$payment = $model;
			break;
		}

		if ($payment === null)
		{
			$errorMessage = 'payment not exist';
			Sale\PaySystem\Logger::addError($errorMessage);
			throw new Main\SystemException($errorMessage);
		}
	}

	protected function transactionStatusUpdateSuccessEvent(TradingAction\Rest\Webhook $request) : void
	{
		$registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);
		/** @var Order $orderClassName */
		$orderClassName = $registry->getOrderClassName();
		$order = $orderClassName::load($request->getExternalOrderId());

		if ($order === null)
		{
			$errorMessage = 'order not undefined';//$this->getLang('ORDER_ACCEPT_SAVE_RESULT_ID_NOT_SET');
			Sale\PaySystem\Logger::addError($errorMessage);
			throw new Main\SystemException($errorMessage);
		}

		if ($order->isCanceled())
		{
			$errorMessage = 'order canceled';
			Sale\PaySystem\Logger::addError($errorMessage);
			throw new Main\SystemException($errorMessage);
		}

		if ($order->isPaid())
		{
			$errorMessage = 'order is paid';//$this->getLang('ORDER_ACCEPT_SAVE_RESULT_ID_NOT_SET');
			Sale\PaySystem\Logger::addError($errorMessage);
			throw new Main\SystemException($errorMessage);
		}

		$collection = $order->getPaymentCollection();
		$payment = null;

		/** @var Sale\Payment $model */
		foreach ($collection as $model)
		{
			if ($model->isInner()) { continue; }
			$payment = $model;
			break;
		}

		if ($payment === null)
		{
			$errorMessage = 'payment not exist';
			Sale\PaySystem\Logger::addError($errorMessage);
			throw new Main\SystemException($errorMessage);
		}

		$resultFieldPayment = $payment->setFields([
			'PAID'              => 'Y',
			'PS_STATUS'         => 'Y',
			'DATE_PAID'         => new Main\Type\DateTime(),
			'PS_RESPONSE_DATE'  => new Main\Type\DateTime($request->getEventTime())
		]);

		if (!$resultFieldPayment->isSuccess())
		{
			$errorMessage = 'PAYMENT SET PAID: '.implode(' ', $resultFieldPayment->getErrorMessages());
			Sale\PaySystem\Logger::addError($errorMessage);
			throw new Main\SystemException($errorMessage);
		}

		$saveResult = $order->save();

		if (!$saveResult->isSuccess())
		{
			$errorMessage = 'ORDER SAVE: '.implode(' ', $saveResult->getErrorMessages());
			Sale\PaySystem\Logger::addError($errorMessage);
			throw new Main\SystemException($errorMessage);
		}
	}

	protected function callWebhook(TradingAction\Rest\Webhook $request) : void
	{
		$method = $this->eventToMethodName($request->getEvent(), $request->getStatus());

		if (!method_exists($this, $method))
		{
			throw new Main\NotImplementedException(sprintf('event webhook %s not implemented', $request->getEvent()));
		}

		$this->{$method}($request);
	}

	protected function eventToMethodName(string $event, string $status) : string
	{
		$parts = explode('_', mb_strtolower($event));
		$parts = array_map('ucfirst', $parts);
		$method = implode('', $parts);
		$method = lcfirst($method);

		return $method . ucfirst(mb_strtolower($status)) .'Event';
	}

	/**
	 * @template T
	 * @param class-string<T> $className
	 *
	 * @return T
	 */
	protected function makeDto(string $className) : TradingAction\Reference\Dto
	{
		Assert::isSubclassOf($className, TradingAction\Reference\Dto::class);

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
		$this->options = $this->setup->wakeupOptions(); // todo remove
		$this->environment = $this->setup->getEnvironment(); // todo remove
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

	protected function resolveAction(string $path) : TradingAction\Rest\Reference\HttpAction
	{
		$router = new TradingAction\Rest\Router();

		return $router->getAction($path);
	}

	protected function getActionPath() : string
	{
		$action = (string)$this->requireParameter('ACTION');
		$action = ltrim($action, '/');

		if ($action === '')
		{
			$action = 'root';
		}

		return $action;
	}

	protected function makeExceptionResponse(\Throwable $exception, $status, array $overrides = []) : Main\Engine\Response\Json
	{
		$response = new Main\Engine\Response\Json($overrides + [
			'status' => 'fail',
			'reasonCode' => (string)($exception->getCode() ?: 'UNKNOWN'),
			'reason' => $exception->getMessage(),
			'trace' => $exception->getTraceAsString(),
		]);
		$response->setStatus($status);

		return $response;
	}

	protected function convertEncoding(string $message) : string
	{
		$isUtf8Config = Main\Application::isUtfMode();

		if ($isUtf8Config) { return $message; }

		return Main\Text\Encoding::convertEncoding($message, 'WINDOWS-1251', 'UTF-8');
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
