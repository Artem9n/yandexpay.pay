<?php
namespace YandexPay\Pay\Trading\Action\Rest\Utils;

use YandexPay\Pay\Exceptions;
use YandexPay\Pay\Trading\Action\Rest\State;

class OrderProperties
{
	public static function setMeaningfulPropertyValues(State\OrderCalculation $state, array $values) : void
	{
		$propertyValues = static::combineMeaningfulPropertyValues($state, $values);

		if (!empty($propertyValues))
		{
			$fillResult = $state->order->fillProperties($propertyValues);
			Exceptions\Facade::handleResult($fillResult);
		}
	}

	protected static function combineMeaningfulPropertyValues(State\OrderCalculation $state, array $values) : array
	{
		$options = $state->options;
		$propertyValues = [];

		foreach ($values as $name => $value)
		{
			$propertyId = (string)$options->getProperty($name);

			if ($propertyId === '') { continue; }

			if (!isset($propertyValues[$propertyId]))
			{
				$propertyValues[$propertyId] = $value;
			}
			else
			{
				if (!is_array($propertyValues[$propertyId]))
				{
					$propertyValues[$propertyId] = [
						$propertyValues[$propertyId],
					];
				}

				if (is_array($value))
				{
					$propertyValues[$propertyId] = array_merge($propertyValues[$propertyId], $value);
				}
				else
				{
					$propertyValues[$propertyId][] = $value;
				}
			}
		}

		return $propertyValues;
	}
}

