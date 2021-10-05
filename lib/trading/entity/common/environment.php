<?php

namespace YandexPay\Pay\Trading\Entity\Common;

use YandexPay\Pay;
use YandexPay\Pay\Trading\Entity\Reference as EntityReference ;

abstract class Environment extends EntityReference\Environment
{
	public function createSite() : EntityReference\Site
	{
		return new Site($this);
	}
}