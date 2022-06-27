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

			$fillData = $fillResult->getData();

			if (isset($fillData['FILLED']))
			{
				$filledMap = array_fill_keys((array)$fillData['FILLED'], true);

				if (isset($state->filledProperties))
				{
					$state->filledProperties += array_intersect_key($propertyValues, $filledMap);
				}

				if (isset($state->relatedProperties))
				{
					$state->relatedProperties += array_diff_key($propertyValues, $filledMap);
				}
			}
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

