<?php
namespace YandexPay\Pay\Logger\Formatter;

use Bitrix\Main;
use YandexPay\Pay\Logger\Audit;

class OutgoingRequest extends Skeleton
{
	protected $url;
	protected $data;

	public function __construct(string $url, array $data = null)
	{
		$this->url = $url;
		$this->data = $data;
	}

	public function message() : string
	{
		if (empty($this->data)) { return ''; }

		return Main\Web\Json::encode($this->data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
	}

	public function context() : array
	{
		return [
			'AUDIT' => Audit::OUTGOING_REQUEST,
			'URL' => $this->url,
		];
	}
}