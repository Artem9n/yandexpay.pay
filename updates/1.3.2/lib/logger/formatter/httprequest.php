<?php
namespace YandexPay\Pay\Logger\Formatter;

use Bitrix\Main;
use YandexPay\Pay\Logger\Audit;

class HttpRequest extends Skeleton
{
	protected $request;

	public function __construct(Main\HttpRequest $request)
	{
		$this->request = $request;
	}

	public function message() : string
	{
		return Main\Web\Json::encode($this->request->getPostList()->toArray(), JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
	}

	public function context() : array
	{
		return [
			'AUDIT' => Audit::INCOMING_REQUEST,
		];
	}
}