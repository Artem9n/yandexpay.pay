<?php
namespace YandexPay\Pay\Ui\UserField;

use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Reference\Event;

class Publisher extends Event\Regular
{
	public static function getHandlers() : array
	{
		$result = [];

		foreach (['warehouse', 'user'] as $type)
		{
			$result[] = [
				'module' => 'main',
				'event' => 'OnUserTypeBuildList',
				'method' => 'onUserTypeBuildList',
				'arguments' => [ $type ],
			];
		}

		return $result;
	}

	public static function onUserTypeBuildList(string $type) : array
	{
		$namespace = __NAMESPACE__ . '\\';
		/** @var class-string<FieldsetType|UserType> $className */
		$className = $namespace . ucfirst($type) . 'Type';

		Assert::methodExists($className, 'getUserTypeDescription');

		return $className::getUserTypeDescription();
	}
}