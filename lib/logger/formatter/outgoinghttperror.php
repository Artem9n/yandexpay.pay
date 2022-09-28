<?php
namespace YandexPay\Pay\Logger\Formatter;

use Bitrix\Main;
use YandexPay\Pay\Logger\Audit;
use YandexPay\Pay\Reference\Concerns;

class OutgoingHttpError extends Skeleton
{
	use Concerns\HasMessage;

	protected $url;
	protected $httpClient;

	public function __construct(string $url, Main\Web\HttpClient $httpClient)
	{
		$this->url = $url;
		$this->httpClient = $httpClient;
	}

	public function message() : string
	{
		return implode(': ', array_filter([
			$this->messageStatus(),
			$this->messageErrors()
		]));
	}

	protected function messageStatus() : string
	{
		$status = (int)$this->httpClient->getStatus();

		return self::getMessage('STATUS', [ '#CODE#' => $status ]);
	}

	protected function messageErrors() : string
	{
		$partials = [];

		foreach ($this->httpClient->getError() as $code => $error)
		{
			$partials[] = sprintf('%s #%s', $error, $code);
		}

		return implode(', ', $partials);
	}

	public function context() : array
	{
		return [
			'AUDIT' => Audit::OUTGOING_RESPONSE,
			'URL' => $this->url,
		];
	}
}