<?php
namespace YandexPay\Pay\Trading\Action\Rest\Root;

use Bitrix\Main;
use YandexPay\Pay\Trading\Action\Rest;
use YandexPay\Pay\Trading\Action\Rest\Dto;

class Action extends Rest\Reference\HttpAction
{
	public function process() : Main\HttpResponse
	{
		$response = new Main\HttpResponse();
		$response->setContent('OK');

		return $response;
	}
}