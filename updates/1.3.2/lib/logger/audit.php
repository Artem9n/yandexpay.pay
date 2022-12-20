<?php
namespace YandexPay\Pay\Logger;

use YandexPay\Pay\Reference\Concerns;

class Audit
{
	use Concerns\HasMessage;

	public const INCOMING_REQUEST = 'incoming_request';
	public const INCOMING_RESPONSE = 'incoming_response';
	public const OUTGOING_REQUEST = 'outgoing_request';
	public const OUTGOING_RESPONSE = 'outgoing_response';
	public const INJECTION_ELEMENT = 'injection_element';
	public const YANDEX_DELIVERY = 'yandex_delivery';
	public const DELIVERY_COLLECTOR = 'delivery_collector';
	public const UNKNOWN = 'unknown';

	public static function getTitle(string $name) : string
	{
		return self::getMessage(sprintf('OPTION_NAME_%s', mb_strtoupper($name)), null, $name);
	}
}