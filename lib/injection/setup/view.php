<?php

namespace YandexPay\Pay\Injection\Setup;

use YandexPay\Pay\Injection;
use YandexPay\Pay\Trading;
use YandexPay\Pay\Reference\Storage;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Ui\Userfield;

class View extends Storage\View
{
    use Concerns\HasMessage;

    public function getFields() : array
    {
        return $this->getTableFields([
			'OVERRIDES' => [
	            'TRADING_ID' => [
	                'TYPE' => 'enumeration',
	                'VALUES' => $this->getTradingEnum(),
	            ],
	            'BEHAVIOR' => [
	                'TYPE' => 'enumeration',
	                'VALUES' => $this->getBehaviorEnum(),
	            ],
			],
	        'EXCLUDE' => [
		        'SETTINGS',
	        ],
        ]);
    }

	protected function getTradingEnum() : array
	{
		$result = [];

		$query = Trading\Setup\RepositoryTable::getList([
			'filter' => [ '=ACTIVE' => UserField\BooleanType::VALUE_TRUE ],
			'select' => [ 'ID', 'SITE_ID' ],
		]);

		while ($row = $query->fetch())
		{
			$result[] = [
				'ID' => $row['ID'],
				'VALUE' => sprintf('[%s] %s', $row['ID'], $row['SITE_ID']),
			];
		}

		return $result;
	}

	protected function getBehaviorEnum() : array
	{
		$result = [];

		foreach (Injection\Behavior\Registry::getTypes() as $type)
		{
			$behavior = Injection\Behavior\Registry::getInstance($type);

			$result[] = [
				'ID' => $type,
				'VALUE' => $behavior->getTitle(),
			];
		}

		return $result;
	}
}