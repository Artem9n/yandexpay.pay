<?php

namespace YandexPay\Pay\Reference\Concerns;

use YandexPay\Pay\Trading\Entity as TradingEntity;
use YandexPay\Pay\Trading\Setup as TradingSetup;

/**
 * @property TradingSetup\Model                  $setup
 * @property TradingEntity\Reference\Environment $environment
 * @property TradingEntity\Reference\Order       $order
 */
trait HasMeaningfulProperties
{
	protected function setMeaningfulPropertyValues(TradingEntity\Reference\Order $order, $values) : void
	{
		$formattedValues = $values;
		$propertyValues = $this->combineMeaningfulPropertyValues($formattedValues);

		if (!empty($propertyValues))
		{
			$fillResult = $order->fillProperties($propertyValues);
			$fillData = $fillResult->getData();

			if (isset($fillData['FILLED']))
			{
				$filledMap = array_fill_keys((array)$fillData['FILLED'], true);

				if (isset($this->filledProperties))
				{
					$this->filledProperties += array_intersect_key($propertyValues, $filledMap);
				}

				if (isset($this->relatedProperties))
				{
					$this->relatedProperties += array_diff_key($propertyValues, $filledMap);
				}
			}

			if (!empty($fillData['CHANGES']) && \method_exists($this, 'pushChange'))
			{
				$this->pushChange('PROPERTIES', $fillData['CHANGES']);
			}
		}
	}

	protected function formatMeaningfulPropertyValues($values)
	{
		$options = $this->setup->getOptions();

		return $values;
	}

	protected function combineMeaningfulPropertyValues($values) : array
	{
		$options = $this->setup->getOptions();
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

	protected function getConfiguredMeaningfulProperties($names) : array
	{
		$options = $this->setup->getOptions();
		$result = [];

		foreach ($names as $name)
		{
			$propertyId = (string)$options->getProperty($name);

			if ($propertyId !== '')
			{
				$result[$name] = $propertyId;
			}
		}

		return $result;
	}
}