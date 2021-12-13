<?php
namespace YandexPay\Pay\Trading\Action\Incoming;

use YandexPay\Pay\Reference\Common\Model;

abstract class Common extends Skeleton
{
	public function getMode() : string
	{
		return $this->requireField('mode');
	}

	public function getUserId() : ?int
	{
		return $this->getField('userId');
	}

	public function getFUserId() : int
	{
		return $this->requireField('fUserId');
	}

	public function getSiteId() : string
	{
		return $this->requireField('siteId');
	}
}