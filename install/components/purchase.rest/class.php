<?php

namespace YandexPay\Pay\Components;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use YandexPay\Pay\Logger;
use YandexPay\Pay\Psr;
use YandexPay\Pay\Trading\Action as TradingAction;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

Loc::loadMessages(__FILE__);

class PurchaseRest extends \CBitrixComponent
{
	private const HTTP_STATUS_400 = '400 Bad Request';
	private const HTTP_STATUS_403 = '403 Forbidden';
	private const HTTP_STATUS_404 = '404 Not Found';
	private const HTTP_STATUS_500 = '500 Internal server error';
	private const HTTP_STATUS_200 = '200 OK';

	/** @var Psr\Log\LoggerInterface $logger */
	protected $logger;

	public function executeComponent()
	{
		try
		{
			$this->loadModules();
			$this->parseUrl();

			$path = $this->getActionPath();

			$this->bootLogger($path);

			$action = $this->resolveAction($path);
			$action->setLogger($this->logger);

			$action->passHttp($this->request);
			$action->bootstrap();

			$this->logRequest($this->request);

			$response = $action->process();

			$this->logResponse($response);
			$this->sendResponse($response);
		}
		catch (TradingAction\Rest\Exceptions\ActionNotImplemented $exception)
		{
			$response = $this->makeExceptionResponse($exception, static::HTTP_STATUS_404, [
				'reasonCode' => 'NOT_FOUND',
				'reason' => 'Action not found',
			]);

			$this->logResponse($response);
			$this->sendResponse($response);
		}
		catch (TradingAction\Rest\Exceptions\RequestAuthentication $exception)
		{
			$response = $this->makeExceptionResponse($exception, static::HTTP_STATUS_403, [
				'reasonCode' => 'FORBIDDEN',
			]);

			$this->logResponse($response, Psr\Log\LogLevel::CRITICAL);
			$this->sendResponse($response);
		}
		catch (TradingAction\Reference\Exceptions\DtoProperty $exception)
		{
			$response = $this->makeExceptionResponse($exception, static::HTTP_STATUS_400, [
				'reasonCode' => $exception->getParameter()
			]);

			$this->logResponse($response, Psr\Log\LogLevel::ERROR);
			$this->sendResponse($response);
		}
		catch (TradingAction\Rest\Exceptions\OnboardProcessed $exception)
		{
			$response = $this->makeExceptionResponse($exception, static::HTTP_STATUS_200, [
				'status' => 'processing'
			]);

			$this->sendResponse($response);
		}
		catch (\Throwable $exception)
		{
			$response = $this->makeExceptionResponse($exception, static::HTTP_STATUS_500);

			$this->logResponse($response, Psr\Log\LogLevel::ERROR);
			$this->sendResponse($response);
		}
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

	protected function bootLogger(string $path) : void
	{
		$this->logger = new Logger\Logger();
		$this->logger->setUrl($path);
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
			'status' => $overrides['status'] ?? 'fail',
			'reasonCode' => (string)($exception->getCode() ?: 'UNKNOWN'),
			'reason' => $exception->getMessage(),
			'trace' => $exception->getTraceAsString(), // todo remove
		]);
		$response->setStatus($status);

		return $response;
	}

	protected function logRequest(Main\HttpRequest $request) : void
	{
 		if ($this->logger === null) { return; }

		$this->logger->debug(...(new Logger\Formatter\HttpRequest($request))->forLogger());
	}

	protected function logResponse(Main\HttpResponse $response, string $level = Psr\Log\LogLevel::DEBUG) : void
	{
 		if ($this->logger === null) { return; }

		$this->logger->log($level, ...(new Logger\Formatter\HttpResponse($response))->forLogger());
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
