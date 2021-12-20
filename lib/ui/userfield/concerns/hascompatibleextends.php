<?php

namespace YandexPay\Pay\Ui\UserField\Concerns;

use Bitrix\Main;

trait HasCompatibleExtends
{
	/** @return Main\UserField\Types\BaseType */
	public static function getCommonExtends() : string
	{
		throw new Main\NotImplementedException();
	}

	/** @return Main\UserField\TypeBase */
	public static function getCompatibleExtends() : string
	{
		throw new Main\NotImplementedException();
	}

	protected static function hasParentMethod(string $name) : bool
	{
		$result = false;
		$classes = [
			static::getCommonExtends(),
			static::getCompatibleExtends(),
		];

		foreach ($classes as $className)
		{
			if (method_exists($className, $name))
			{
				$result = true;
				break;
			}
		}

		return $result;
	}

	protected static function callParent($name, array $arguments = [])
	{
		$classes = [
			static::getCommonExtends(),
			static::getCompatibleExtends(),
		];

		foreach ($classes as $className)
		{
			if (!method_exists($className, $name)) { continue; }

			return $className::{$name}(...$arguments);
		}

		throw new Main\NotImplementedException(sprintf(
			'method %s not implemented for parents of %s',
			$name,
			static::class
		));
	}
}