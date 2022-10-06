<?php
namespace YandexPay\Pay\Logger\Formatter;

use Bitrix\Main;
use YandexPay\Pay\Logger\Audit;

class OutgoingResponse extends Skeleton
{
	protected $url;
	protected $response;

	public function __construct(string $url, string $response)
	{
		$this->url = $url;
		$this->response = $response;
	}

	public function message() : string
	{
		return $this->response;
	}

	public function context() : array
	{
		return [
			'AUDIT' => Audit::OUTGOING_RESPONSE,
			'URL' => $this->url,
		];
	}
}