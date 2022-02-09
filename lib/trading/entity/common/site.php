<?php

namespace YandexPay\Pay\Trading\Entity\Common;

use YandexPay\Pay;

class Site extends Pay\Trading\Entity\Reference\Site
{
	public function getVariants() : array
	{
		return Pay\Data\Site::getVariants();
	}

	public function getTitle(string $siteId) : string
	{
		return Pay\Data\Site::getTitle($siteId);
	}

	public function getOptions() : array
	{
		return Pay\Data\Site::getOptions();
	}

	public function getDefault() : string
	{
		return Pay\Data\Site::getDefault();
	}

	public function getTemplate(string $siteId) : array
	{
		return Pay\Data\Site::getTemplate($siteId);
	}

	public function getDir(string $siteId)
	{
		return Pay\Data\Site::getDir($siteId);
	}
}