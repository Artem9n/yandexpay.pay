<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderCreate\Stage;

use YandexPay\Pay\Trading\Action\Rest\Utils;
use YandexPay\Pay\Trading\Action\Rest\State;

class RelatedProperties
{
	public function __invoke(State\OrderCalculation $state)
	{
		if (!empty($state->relatedProperties))
		{
			Utils\OrderProperties::setMeaningfulPropertyValues($state, $state->relatedProperties);

			$state->filledProperties += $state->relatedProperties;
			$state->relatedProperties = [];
		}
	}
}