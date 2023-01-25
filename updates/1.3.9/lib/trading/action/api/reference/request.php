<?php

namespace YandexPay\Pay\Trading\Action\Api\Reference;

use Bitrix\Main;
use YandexPay\Pay\Logger;
use YandexPay\Pay\Psr;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Action;

Main\Localization\Loc::loadMessages(__FILE__);

abstract class Request
	implements Psr\Log\LoggerAwareInterface
{
	public const DATA_TYPE_JSON = 'json';
	public const DATA_TYPE_HTTP = 'http';

	/** @var bool bool */
	protected $isTestMode = false;
	/** @var string */
	protected $apiKey;
	/** @var Psr\Log\LoggerInterface */
	protected $logger;

	public function __construct()
	{
		$this->logger = new Logger\NullLogger();
	}

	public function setLogger(Psr\Log\LoggerInterface $logger) : void
	{
		$this->logger = $logger;
	}

	public function getUrl() : string
	{
		return $this->getProtocol() . '://' . $this->getHost() . $this->getPath();
	}

	public function setTestMode(bool $testMode) : void
	{
		$this->isTestMode = $testMode;
	}

	public function setApiKey(string $apiKey) : void
	{
		$this->apiKey = $apiKey;
	}

	public function getFullUrl() : string
	{
		$url = $this->getUrl();

		if ($this->getMethod() === Main\Web\HttpClient::HTTP_GET)
		{
			$query = $this->getQuery();
			$url = $this->appendUrlQuery($url, $query);
		}

		return $url;
	}

	protected function appendUrlQuery($url, $query) : string
	{
		$result = $url;

		if (!empty($query))
		{
			$result .=
				(mb_strpos($result, '?') === false ? '?' : '&')
				. http_build_query($query, '', '&');
		}

		return $result;
	}

	public function getProtocol() : string
	{
		return 'https';
	}

	public function getHost() : string
	{
		return sprintf(
			'%spay.yandex.ru',
			$this->isTestMode ? 'sandbox.' : ''
		);
	}

	abstract public function getPath() : string;

	public function getQuery() : array
	{
		return [];
	}

	public function getQueryFormat() : string
	{
		return static::DATA_TYPE_JSON;
	}

	public function getMethod() : string
	{
		return Main\Web\HttpClient::HTTP_POST;
	}

	public function send() : array
	{
		$client = $this->buildClient();

		$httpResponse = $this->queryClient($client);

		$errors = $client->getError();

		if ($httpResponse === '' && !empty($errors))
		{
			throw new Action\Reference\Exceptions\DtoProperty(implode(PHP_EOL, $errors));
		}

		$data = $this->parseHttpResponse($httpResponse);
		$httpStatus = $client->getStatus();

		if ($httpStatus !== 200 && empty($data))
		{
			$logContents = $httpResponse ?: sprintf('http %s', $httpStatus);

			throw new Action\Reference\Exceptions\DtoProperty($logContents);
		}

		return $data;
	}

	/**
	 * @template T
	 *
	 * @param array $data
	 * @param class-string<T> $className
	 *
	 * @return T
	 */
	public function buildResponse(array $data, string $className) : Response
	{
		Assert::isSubclassOf($className, Response::class, Action\Reference\Exceptions\DtoProperty::class);

		$response = new $className($data);

		$response->validate();

		return $response;
	}

	protected function buildClient() : Main\Web\HttpClient
	{
		$result = new Main\Web\HttpClient();

		$result->setHeader('Authorization', sprintf('Api-key %s', $this->apiKey));

		foreach ($this->queryHeaders() as $name => $value)
		{
			$result->setHeader($name, $value);
		}

		if($this->getQueryFormat() === static::DATA_TYPE_JSON)
		{
			$result->setHeader('Content-Type', 'application/json');
		}

		return $result;
	}

	protected function queryHeaders() : array
	{
		return [];
	}

	protected function queryClient(Main\Web\HttpClient $client) : ?string
	{
		$method = $this->getMethod();
		$url = $this->getUrl();
		$queryData = $this->getQuery();

		if ($method === Main\Web\HttpClient::HTTP_GET)
		{
			$fullUrl = $this->appendUrlQuery($url, $queryData);
			$postData = null;
		}
		else
		{
			$fullUrl = $url;
			$postData = $this->formatQueryData($queryData);
		}

		$this->logger->debug(...(new Logger\Formatter\OutgoingRequest($fullUrl, $queryData))->forLogger());

		if ($client->query($method, $fullUrl, $postData))
		{
			$result = $client->getResult();
			$this->logger->debug(...(new Logger\Formatter\OutgoingResponse($fullUrl, $result))->forLogger());
		}
		else
		{
			$result = null;
			$this->logger->debug(...(new Logger\Formatter\OutgoingHttpError($fullUrl, $client))->forLogger());
		}

		return $result;
	}

	protected function formatQueryData($data)
	{
		switch ($this->getQueryFormat())
		{
			case static::DATA_TYPE_JSON:
				$result = Main\Web\Json::encode($data);
				break;

			default:
				$result = $data;
				break;
		}

		return $result;
	}

	protected function parseHttpResponse($httpResponse) : array
	{
		try
		{
			$result = Main\Web\Json::decode($httpResponse);
		}
		catch (\Exception $exception)
		{
			$result = [];
		}

		return $result;
	}
}
