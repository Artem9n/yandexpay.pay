<?php

namespace YandexPay\Pay\Trading\Settings\Reference;

abstract class Fieldset extends Skeleton
{
	public function getFieldDescription($environment, string $siteId) : array
	{
		return [
			'MULTIPLE' => 'N',
			'FIELDS' => $this->getFields($environment, $siteId),
		];
	}
}