<?php

namespace YandexPay\Pay\Injection\Setup;

use YandexPay\Pay\Injection;
use YandexPay\Pay\Trading;
use YandexPay\Pay\Reference\Storage;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Ui\Userfield;
use YandexPay\Pay\Utils\Userfield\DependField;

class View extends Storage\View
{
    use Concerns\HasMessage;

	protected $behaviors;

    public function getFields() : array
    {
        $result = $this->getTableFields([
			'OVERRIDES' => [
	            'TRADING_ID' => [
	                'TYPE' => 'enumeration',
	                'VALUES' => $this->getTradingEnum(),
	            ],
	            'BEHAVIOR' => [
	                'TYPE' => 'enumeration',
	                'VALUES' => $this->getBehaviorEnum(),
	            ]
			],
	        'EXCLUDE' => [
		        'SETTINGS',
	        ],
        ]);
	    $result += $this->getSettingsFields();

		return $result;
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

		foreach ($this->getBehaviors() as $type => $behavior)
		{
			$result[] = [
				'ID' => $type,
				'VALUE' => $behavior->getTitle(),
			];
		}

		return $result;
	}

	protected function getSettingsFields() : array
	{
		$result = [];

		foreach ($this->getBehaviors() as $type => $behavior)
		{
			foreach ($behavior->getFields() as $name => $field)
			{
				$fullName = sprintf('SETTINGS[%s]', mb_strtoupper($type) . '_' . $name);
				$field += [
					'LIST_COLUMN_LABEL' => $field['TITLE'],
					'DEPEND' => [
						'BEHAVIOR' => [
							'RULE' => DependField::RULE_ANY,
							'VALUE' => [ $type ],
						],
					]
				];

				$result[$fullName] = $field;
			}
		}

		return $result;
	}

	/** @return array<string, Injection\Behavior\BehaviorInterface> */
	protected function getBehaviors() : array
	{
		if ($this->behaviors === null)
		{
			$this->behaviors = $this->loadBehaviors();
		}

		return $this->behaviors;
	}

	protected function loadBehaviors() : array
	{
		$result = [];

		foreach (Injection\Behavior\Registry::getTypes() as $type)
		{
			$result[$type] = Injection\Behavior\Registry::getInstance($type);
		}

		return $result;
	}
}