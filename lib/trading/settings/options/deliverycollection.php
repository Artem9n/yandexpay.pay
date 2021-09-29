<?php

namespace YandexPay\Pay\Trading\Settings\Options;

use YandexPay\Pay\Trading\Settings\Reference\FieldsetCollection;

class DeliveryCollection extends FieldsetCollection
{
	public function getItemReference() : string
	{
		return Delivery::class;
	}
}