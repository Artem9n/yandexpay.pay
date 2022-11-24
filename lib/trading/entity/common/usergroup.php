<?php

namespace YandexPay\Pay\Trading\Entity\Common;

use Bitrix\Main;
use YandexPay\Pay\Trading\Entity as TradingEntity;

class UserGroup extends TradingEntity\Reference\UserGroup
{
	/** @var array */
	protected static $groups;

	public function getGroups() : array
	{
		if (static::$groups === null)
		{
			static::$groups = $this->loadGrous();
		}

		return static::$groups;
	}

	protected function loadGrous() : array
	{
		$result = [];

		$query = Main\GroupTable::getList([
			'filter' => [
				'ACTIVE' => 'Y',
			],
		]);

		while ($group = $query->fetch())
		{
			$result[] = $group;
		}

		return $result;
	}

	public function getGroupList() : array
	{
		$result = [];

		$groups = $this->getGroups();

		foreach ($groups as $group)
		{
			$result[] = [
				'ID' => $group['ID'],
				'VALUE' => sprintf('[%s] %s', $group['ID'], $group['NAME']),
			];
		}

		return $result;
	}

	public function getDefaultGroup() : int
	{
		$groups = $this->getGroups();

		$group = array_filter($groups, static function($value) {
			return $value['ANONYMOUS'] === 'Y';
		});

		$group = empty($group) ? reset($groups) : reset($group);

		return $group['ID'];
	}
}