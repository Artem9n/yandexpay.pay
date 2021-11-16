<?php

namespace YandexPay\Pay\Trading\injection\Reference;

use YandexPay\Pay\Trading\Entity;

abstract class Fieldset
{
	public function getFieldDescription(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return [
			'MULTIPLE' => 'N',
			'FIELDS' => $this->getFields($environment, $siteId),
		];
	}
}