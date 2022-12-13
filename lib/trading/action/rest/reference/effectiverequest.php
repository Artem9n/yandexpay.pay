<?php
namespace YandexPay\Pay\Trading\Action\Rest\Reference;

use YandexPay\Pay\Trading\Action\Reference\Dto;

class EffectiveRequest extends Dto
{
	public function getMetadata() : array
	{
		return explode(':', $this->getField('metadata'));
	}

	public function getUserId() : int
	{
		[$userId, $fUserId, $setupId, $httpHost] = $this->getMetadata();

		return (int)$userId;
	}

	public function getFUserId() : int
	{
		[$userId, $fUserId, $setupId, $httpHost] = $this->getMetadata();

		return (int)$fUserId;
	}

	public function getSetupId() : int
	{
		[$userId, $fUserId, $setupId, $httpHost] = $this->getMetadata();

		return (int)$setupId;
	}

	public function getHttpHost() : string
	{
		[$userId, $fUserId, $setupId, $httpHost] = $this->getMetadata();

		return (string)$httpHost;
	}

	public function getRegionId() : string
	{
		[$userId, $fUserId, $setupId, $httpHost, $regionId] = $this->getMetadata();

		return (string)$regionId;
	}

	public function getMerchantId() : string
	{
		return $this->requireField('merchantId');
	}

	public function getCurrencyCode()
	{
		return $this->requireField('currencyCode');
	}
}