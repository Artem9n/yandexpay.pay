<?php

namespace YandexPay\Pay\Trading\Action\Api\Reference;

use Bitrix\Main;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Action;

class Response extends Action\Reference\Dto
{
	public const STATUS_SUCCESS = 'success';
	public const STATUS_FAIL = 'fail';

	use Concerns\HasMessage;

	public function validate() : void
	{
		if ($this->getStatus() === static::STATUS_FAIL)
		{
			$message = $this->getReason() ?? $this->getReasonCode();

			throw new Action\Reference\Exceptions\DtoProperty($message, $this->getReasonCode());
		}
	}

	public function getReason() : ?string
	{
		return $this->getField('reason');
	}

	public function getReasonCode() : ?string
	{
		return $this->getField('reasonCode');
	}

	public function getStatus() : string
	{
		return $this->requireField('status');
	}

	public function getData() : Action\Api\Reference\Dto\Data
	{
		return $this->getChildModel('data');
	}

	protected function modelMap() : array
	{
		return [
			'data' => Action\Api\Reference\Dto\Data::class,
		];
	}
}