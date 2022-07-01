<?php
namespace YandexPay\Pay\Trading\Action\Incoming;

use Bitrix\Main\Web;

class PickupDetail extends Common
{
	public function getId() : int
	{
		$result = Web\Json::decode($this->requireField('pickupId'));

		return (int)$result['deliveryId'];
	}

	public function getStoreId() : string
	{
		$result = Web\Json::decode($this->requireField('pickupId'));

		return (string)$result['storeId'];
	}

	public function getLocationId() : string
	{
		$result = Web\Json::decode($this->requireField('pickupId'));

		return (string)$result['locationId'];
	}

	public function getZip() : ?string
	{
		$result = Web\Json::decode($this->requireField('pickupId'));

		return $result['zip'];
	}

	public function getItems() : Items
	{
		return $this->getChildCollection('items');
	}

	public function getPaySystemId() : int
	{
		return $this->requireField('paySystemId');
	}

	protected function collectionMap() : array
	{
		return [
			'items' => Items::class,
		];
	}
}