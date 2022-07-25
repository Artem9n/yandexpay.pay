<?php
namespace YandexPay\Pay\Injection;

use Bitrix\Main\Application;
use YandexPay\Pay\Injection;

class Migration
{
	public static function reinstallEvents(array $classes = null) : void
	{
		if ($classes === null)
		{
			$classes = [
				Engine\Element::class,
				Engine\Basket::class,
				Engine\Order::class,
			];
		}

		$installed = static::fetchInstalled();

		if (empty($installed)) { return; }

		static::removeRegisteredEvents($classes);
		static::applyInstalled($installed);
	}

	protected static function fetchInstalled() : array
	{
		$queryInject = Injection\Setup\RepositoryTable::getList([]);

		$result = null;

		while ($inject = $queryInject->fetchObject())
		{
			$result[] = $inject;
		}

		return $result;
	}

	protected static function removeRegisteredEvents(array $classes) : void
	{
		foreach ($classes as $class)
		{
			$class = '\\' . $class;

			$con = Application::getConnection();
			$sqlHelper = $con->getSqlHelper();

			$strSql = sprintf(
				'DELETE FROM %s WHERE FROM_MODULE_ID="%s" AND MESSAGE_ID="%s" AND TO_MODULE_ID="%s" AND TO_CLASS="%s" AND TO_METHOD="%s"',
				$sqlHelper->quote('b_module_to_module'),
				$sqlHelper->forSql('main'),
				$sqlHelper->forSql('onEpilog'),
				$sqlHelper->forSql('yandexpay.pay'),
				$sqlHelper->forSql($class),
				$sqlHelper->forSql('onEpilog')
			);

			$managedCache = Application::getInstance()->getManagedCache();
			$managedCache->clean('b_module_to_module');

			$con->queryExecute($strSql);
		}
	}

	protected static function applyInstalled(array $installed) : void
	{
		/** @var  $injectObject \YandexPay\Pay\Injection\Setup\Model*/
		foreach ($installed as $injectObject)
		{
			$injectObject->register();
		}
	}
}