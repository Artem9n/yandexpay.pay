<?php

namespace YandexPay\Pay\Trading\Entity\Sale;

use Bitrix\Sale;
use YandexPay\Pay\Trading\Entity\Reference as EntityReference;

class PaySystem extends EntityReference\PaySystem
{
	/** @var Environment */
	protected $environment;

	public function __construct(Environment $environment)
	{
		parent::__construct($environment);
	}

	public function getEnum(string $siteId = null) : array
	{
		$result = [];

		$query = Sale\PaySystem\Manager::getList([
			'filter' => [
				'=ACTIVE' => 'Y',
				'=ENTITY_REGISTRY_TYPE' => Sale\Payment::getRegistryType(),
			],
			'order' => ['SORT' => 'ASC', 'NAME' => 'ASC'],
			'select' => ['ID', 'NAME']
		]);

		while ($row = $query->fetch())
		{
			$result[] = [
				'ID' => $row['ID'],
				'VALUE' => sprintf('[%s] %s', $row['ID'], $row['NAME']),
			];
		}

		return $result;
	}
}