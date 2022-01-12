<?php

namespace YandexPay\Pay\Trading\Entity\Sale;

use Bitrix\Sale;
use Bitrix\Main;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Trading\Entity\Reference as EntityReference;

class PaySystem extends EntityReference\PaySystem
{
	use Concerns\HasMessage;

	/** @var Environment */
	protected $environment;

	public function __construct(Environment $environment)
	{
		parent::__construct($environment);
	}

	public function getEnum(string $siteId = null, array $dataFilter = []) : array
	{
		$result = [];

		$filter = [
			//'=ACTIVE' => 'Y',
			'=ENTITY_REGISTRY_TYPE' => Sale\Payment::getRegistryType(),
		];

		if (!empty($dataFilter))
		{
			$filter += $dataFilter;
		}

		$query = Sale\PaySystem\Manager::getList([
			'filter' => $filter,
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

	public function getHandler(int $paySystemId) : Sale\PaySystem\BaseServiceHandler
	{
		$service = $this->getService($paySystemId);

		Assert::notNull($service, 'service', static::getMessage('NOT_LOAD_SERVICE'));

		$actionFile = $service->getField('ACTION_FILE');

		if (method_exists(Sale\PaySystem\Manager::class, 'includeHandler'))
		{
			[$className, $handlerType] = Sale\PaySystem\Manager::includeHandler($actionFile);
		}
		else
		{
			[$className, $handlerType] = $this->includeHandler($actionFile);
		}

		return new $className($handlerType, $service);
	}

	protected function getService(int $paySystemId) : ?Sale\PaySystem\Service
	{
		$result = null;

		$query = Sale\PaySystem\Manager::getList([
			'filter' => [
				'=ID' => $paySystemId,
				'=ACTION_FILE' => 'yandexpay',
				'ACTIVE' => 'Y'
			],
			'select' => ['*']
		]);

		if ($item = $query->fetch())
		{
			$result = new Sale\PaySystem\Service($item);
		}

		return $result;
	}

	protected function includeHandler(string $actionFile) : array
	{
		$handlerType = '';
		$className = '';

		$name = Sale\PaySystem\Manager::getFolderFromClassName($actionFile);

		foreach (Sale\PaySystem\Manager::getHandlerDirectories() as $type => $path)
		{
			$documentRoot = Main\Application::getDocumentRoot();
			if (Main\IO\File::isFileExists($documentRoot.$path.$name.'/handler.php'))
			{
				$className = Sale\PaySystem\Manager::getClassNameFromPath($actionFile);
				if (!class_exists($className))
					require_once($documentRoot.$path.$name.'/handler.php');

				if (class_exists($className))
				{
					$handlerType = $type;
					break;
				}

				$className = '';
			}
		}

		if ($className === '')
		{
			if (Sale\PaySystem\Manager::isRestHandler($actionFile))
			{
				$className = Sale\PaySystem\RestHandler::class;
				if (!class_exists($actionFile))
				{
					class_alias($className, $actionFile);
				}
			}
			else
			{
				$className = Sale\PaySystem\CompatibilityHandler::class;
			}
		}

		return [
			$className,
			$handlerType,
		];
	}
}