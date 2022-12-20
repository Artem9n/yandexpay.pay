<?php
namespace YandexPay\Pay\Logger\Formatter;

use Bitrix\Main;
use YandexPay\Pay\Logger\Audit;

class HttpResponse extends Skeleton
{
	protected $response;

	public function __construct(Main\HttpResponse $response)
	{
		$this->response = $response;
	}

	public function message() : string
	{
		return $this->response->getContent();
	}

	public function context() : array
	{
		return [
			'AUDIT' => Audit::INCOMING_RESPONSE,
		];
	}
}