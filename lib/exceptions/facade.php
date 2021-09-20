<?php

namespace YandexPay\Pay\Exceptions;

use Bitrix\Main;

class Facade
{
	public static function handleResult(Main\Result $result, $exceptionClassName = Main\SystemException::class): void
	{
		if ($result->isSuccess()) { return; }

		$addErrorMessages = $result->getErrorMessages();
		$exceptionMessage = implode(PHP_EOL, $addErrorMessages);

		throw new $exceptionClassName($exceptionMessage);
	}

	public static function fromApplication($exceptionClassName = Main\SystemException::class) : \Exception
	{
		global $APPLICATION;

		$exception = $APPLICATION->GetException();
		$message = $exception ? $exception->GetString() : 'Unknown application exception';

		return new $exceptionClassName($message);
	}
}