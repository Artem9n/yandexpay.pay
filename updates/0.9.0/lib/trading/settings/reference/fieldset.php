<?php

namespace YandexPay\Pay\Trading\Settings\Reference;

use YandexPay\Pay\Trading\Entity;

abstract class Fieldset extends Skeleton
{
	public function getFieldDescription(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return [
			'MULTIPLE' => 'N',
			'FIELDS' => $this->getFields($environment, $siteId),
		];
	}
}