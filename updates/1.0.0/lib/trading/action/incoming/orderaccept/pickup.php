<?php
namespace YandexPay\Pay\Trading\Action\Incoming\OrderAccept;

use Bitrix\Main\Web;
use YandexPay\Pay\Trading\Action\Incoming;

class Pickup extends Incoming\Skeleton
{
	public function getId() : int
	{
		$result = Web\Json::decode($this->requireField('id'));

		return (int)$result['deliveryId'];
	}

	public function getStoreId() : int
	{
		$result = Web\Json::decode($this->requireField('id'));

		return (int)$result['storeId'];
	}

	public function getLocationId() : int
	{
		$result = Web\Json::decode($this->requireField('id'));

		return (int)$result['locationId'];
	}

	public function getLabel() : string
	{
		return (string)$this->requireField('label');
	}

	public function getAmount() : float
	{
		return (float)$this->requireField('amount');
	}

	public function getProvider() : string
	{
		return (string)$this->requireField('provider');
	}

	public function getCategory() : string
	{
		return (string)$this->requireField('category');
	}

	public function getDate() : int
	{
		return (int)$this->requireField('date');
	}
}