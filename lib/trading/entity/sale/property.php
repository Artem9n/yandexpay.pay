<?php

namespace YandexPay\Pay\Trading\Entity\Sale;

use YandexPay\Pay;
use Bitrix\Sale;

class Property extends Pay\Trading\Entity\Reference\Property
{
	/** @var Environment */
	protected $environment;

	public function __construct(Environment $environment)
	{
		parent::__construct($environment);
	}

	public function getEnum(int $personTypeId) : array
	{
		$result = [];

		if ($personTypeId > 0)
		{
			$query = Sale\Internals\OrderPropsTable::getList([
				'filter' => [
					'=PERSON_TYPE_ID' => $personTypeId,
					'=ACTIVE' => 'Y',
				],
				'order' => [
					'SORT' => 'asc',
					'ID' => 'asc',
				],
			]);

			while ($propertyRow = $query->fetch())
			{
				$result[] = [
					'ID' => $propertyRow['ID'],
					'VALUE' => $propertyRow['NAME'],
					'TYPE' => $this->getPropertyType($propertyRow),
					'CODE' => $propertyRow['CODE'],
				];
			}
		}

		return $result;
	}

	protected function getPropertyType(array $propertyRow) : ?string
	{
		$propertyCode = mb_strtoupper($propertyRow['CODE']);
		$propertyType = null;

		if ($propertyRow['IS_EMAIL'] === 'Y' || $this->isMatchPropertyCode($propertyCode, ['EMAIL']))
		{
			$propertyType = 'EMAIL';
		}
		else if ($propertyRow['IS_PHONE'] === 'Y' || $this->isMatchPropertyCode($propertyCode, ['PHONE', 'TEL']))
		{
			$propertyType = 'PHONE';
		}
		else if ($propertyRow['IS_LOCATION'] === 'Y')
		{
			$propertyType = 'LOCATION';
		}
		else if ($propertyRow['IS_ADDRESS'] === 'Y' || $this->isMatchPropertyCode($propertyCode, ['ADDRESS', 'COMPANY_ADR', 'COMPANY_ADDRESS']))
		{
			$propertyType = 'ADDRESS';
		}
		else if ($propertyRow['IS_ZIP'] === 'Y' || $propertyCode === 'ZIP' || $propertyCode === 'INDEX')
		{
			$propertyType = 'ZIP';
		}
		else if ($this->isMatchPropertyCode($propertyCode, ['CITY']))
		{
			$propertyType = 'CITY';
		}
		else if ($propertyCode === 'COMPANY')
		{
			$propertyType = 'COMPANY';
		}
		else if ($propertyRow['IS_PROFILE_NAME'] === 'Y' || $propertyRow['IS_PAYER'] === 'Y')
		{
			$propertyType = 'NAME';
		}

		return $propertyType;
	}

	protected function isMatchPropertyCode(string $haystack, array $needles) : bool
	{
		$haystack = mb_strtoupper($haystack);
		$result = false;

		foreach ($needles as $needle)
		{
			if (mb_strpos($haystack, $needle) !== false)
			{
				$result = true;
				break;
			}
		}

		return $result;
	}

	public function joinPropertyMultipleValue(Sale\PropertyValue $property, $value)
	{
		$propertyRow = $property->getProperty();
		$propertyType = $propertyRow['TYPE'] ?? 'STRING';
		$supportsGlue = [
			'STRING' => true,
			'ADDRESS' => ', ',
		];

		if (!isset($supportsGlue[$propertyType]))
		{
			$result = reset($value);
		}
		else
		{
			if (is_string($supportsGlue[$propertyType]))
			{
				$glue = $supportsGlue[$propertyType];
			}
			else
			{
				$propertyType = $this->getPropertyType($propertyRow);

				$glue = $this->resolvePropertyTypeValueGlue($propertyType) ?:  ', ';
			}

			$result = implode($glue, $value);
		}

		return $result;
	}

	protected function resolvePropertyTypeValueGlue($propertyType): ?string
	{
		switch ($propertyType)
		{
			case 'NAME':
				$result = ' ';
				break;

			default:
				$result = null;
				break;
		}

		return $result;
	}
}