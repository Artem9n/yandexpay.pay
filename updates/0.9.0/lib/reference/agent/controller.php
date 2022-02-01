<?php

namespace YandexPay\Pay\Reference\Agent;

use Bitrix\Main;
use CAgent;
use YandexPay\Pay\Config;

class Controller
{
	public const UPDATE_RULE_STRICT = 'strict';
	public const UPDATE_RULE_FUTURE = 'future';

	/**
	 * ��������� �����
	 *
	 * @param $className string
	 * @param $agentParams array|null
	 *
	 * @throws Main\NotImplementedException
	 * @throws Main\SystemException
	 * */
	public static function register($className, $agentParams): void
	{
		$agentDescription = static::getAgentDescription($className, $agentParams);
		$registeredAgent = static::getRegisteredAgent($agentDescription);

		static::saveAgent($agentDescription, $registeredAgent);
	}

	/**
	 * ������� �����
	 *
	 * @param $className
	 * @param $agentParams
	 *
	 * @throws \Bitrix\Main\NotImplementedException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function unregister($className, $agentParams): void
	{
		$agentDescription = static::getAgentDescription($className, $agentParams);
		$registeredAgent = static::getRegisteredAgent($agentDescription);

		if ($registeredAgent)
		{
			static::deleteAgent($registeredAgent);
		}
	}

	/**
	 * ��������� �������� ���������� �������
	 *
	 * @throws Main\NotImplementedException
	 * @throws Main\SystemException
	 * */
	public static function updateRegular(): void
	{
		$baseClassName = Regular::getClassName();

		$classList = static::getClassList($baseClassName);
		$agentList = static::getClassAgents($classList);
		$registeredList = static::getRegisteredAgents($baseClassName);

		static::saveAgents($agentList, $registeredList);
		static::deleteAgents($agentList, $registeredList);
	}

	/**
	 * ������� ��� ������
	 *
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function deleteAll(): void
	{
		$namespace = Config::getNamespace();
		$registeredList = static::getRegisteredAgents($namespace, true);

		static::deleteAgents([], $registeredList);
	}

	/**
	 * ������� ������ ������� � ������� ������ ��� ������
	 *
	 * @param $classList array ������ �������
	 *
	 * @return array ������ ������� ��� �����������
	 * @throws Main\NotImplementedException
	 */
	public static function getClassAgents($classList): array
	{
		$agentList = [];

		/** @var Regular $className */
		foreach ($classList as $className)
		{
			$normalizedClassName = $className::getClassName();
			$agents = $className::getAgents();

			foreach ($agents as $agent)
			{
				$agentDescription = static::getAgentDescription(
					$normalizedClassName,
					$agent
				);
				$agentKey = mb_strtolower($agentDescription['name']);

				$agentList[$agentKey] = $agentDescription;
			}
		}

		return $agentList;
	}

	/**
	 * ���������� �������� ������ ��� �����������, ��������� ������������� ������
	 *
	 * @param $className string
	 * @param $agentParams array|null ��������� ������
	 *
	 * @return array
	 * @throws Main\NotImplementedException
	 */
	public static function getAgentDescription($className, $agentParams): array
	{
		$method = $agentParams['method'] ?? 'run';

		if (!method_exists($className, $method))
		{
			throw new Main\NotImplementedException(
				'Method ' . $method
				. ' not defined in ' . $className
				. ' and cannot be registered as agent'
			);
		}

		$agentFnCall = static::getAgentCall(
			$className,
			$method, $agentParams['arguments'] ?? null
		);

		return [
			'name'      => $agentFnCall,
			'sort'      => isset($agentParams['sort']) ? (int)$agentParams['sort'] : 100,
			'interval'  => isset($agentParams['interval']) ? (int)$agentParams['interval'] : 86400,
			'next_exec' => $agentParams['next_exec'] ?? '',
			'update'    => $agentParams['update'] ?? null,
		];
	}

	/**
	 * �������� ������ ����� ������������������ �������
	 *
	 * @param $baseClassName string �������� ������, ����������� �������� ���������� ��������
	 * @param $isBaseNamespace bool ������ �������� �� �������� �������
	 *
	 * @return array ������ ������������������ �������
	 * */
	public static function getRegisteredAgents($baseClassName, $isBaseNamespace = false): array
	{
		$registeredList = [];
		$namespaceLower = mb_strtolower(Config::getNamespace());
		$query = CAgent::GetList(
			[],
			[
				'NAME' => $namespaceLower . '%'
			]
		);

		while ($agentRow = $query->fetch())
		{
			$agentCallParts = explode('::', $agentRow['NAME']);
			$agentClassName = trim($agentCallParts[0]);

			if (
				$isBaseNamespace
				|| $agentClassName === ''
				|| !class_exists($agentClassName)
				|| is_subclass_of($agentClassName, $baseClassName)
			)
			{
				$agentKey = mb_strtolower($agentRow['NAME']);
				$registeredList[$agentKey] = $agentRow;
			}
		}

		return $registeredList;
	}

	/**
	 * �������� ������������������ ����� ��� ������ ������
	 *
	 * @param $agentDescription array
	 *
	 * @return array|null ������������������ �����
	 * */
	public static function getRegisteredAgent($agentDescription): ?array
	{
		$result = null;
		$variants = array_unique([
			$agentDescription['name'],
			str_replace(PHP_EOL, '', $agentDescription['name']), // new line removed after edit agent in admin form
		]);

		foreach ($variants as $variant)
		{
			$query = CAgent::GetList([], [
				'NAME' => $variant
			]);

			if ($row = $query->Fetch())
			{
				$result = $row;
				break;
			}
		}

		return $result;
	}

	/**
	 * ���������� ������ ��� ������ ������ callAgent ������ ����� eval
	 *
	 * @param $className string
	 * @param $method string
	 * @param $arguments array|null
	 *
	 * @return string ����� ������
	 * */
	public static function getAgentCall($className, $method, $arguments = null): string
	{
		return static::getFunctionCall(
			$className,
			'callAgent',
			isset($arguments)
				? [$method, $arguments]
				: [$method]
		);
	}

	/**
	 * ���������� ������ ��� ������ ����� ����� ����� eval
	 *
	 * @param $className string
	 * @param $method string
	 * @param $arguments array|null
	 *
	 * @return string ����� ������
	 * */
	public static function getFunctionCall($className, $method, $arguments = null): string
	{
		$argumentsString = '';

		if (is_array($arguments))
		{
			$isFirstArgument = true;

			foreach ($arguments as $argument)
			{
				if (!$isFirstArgument)
				{
					$argumentsString .= ', ';
				}

				$argumentsString .= var_export($argument, true);

				$isFirstArgument = false;
			}
		}

		return $className . '::' . $method . '(' . $argumentsString . ');';
	}

	/**
	 * ���������� ������ ���� ���������� ���������� Agent
	 *
	 * @param string �������� ������, ���������� �������� ���� �������
	 *
	 * @return array ������ ��� ������� ��� ������
	 * */
	protected static function getClassList($baseClassName): array
	{
		$baseDir = Config::getModulePath();
		$baseNamespace = Config::getNamespace();
		$directory = new \RecursiveDirectoryIterator($baseDir);
		$iterator = new \RecursiveIteratorIterator($directory);
		$result = [];

		/** @var \DirectoryIterator $entry */
		foreach ($iterator as $entry)
		{
			if (
				$entry->isFile()
				&& $entry->getExtension() === 'php'
			)
			{
				$relativePath = str_replace($baseDir, '', $entry->getPath());
				$className = $baseNamespace . str_replace('/', '\\', $relativePath) . '\\' . $entry->getBasename('.php');
				$tableClassName = $className . 'Table';

				if (
					!empty($relativePath)
					&& !class_exists($tableClassName)
					&& class_exists($className)
					&& is_subclass_of($className, $baseClassName)
				)
				{
					$result[] = $className;
				}
			}
		}

		return $result;
	}

	/**
	 * ������������ ��� ������ � ���� ������
	 *
	 * @param $agentList array ������ ������� ��� �����������
	 * @param $registeredList array ������ ����� ������������������ �������
	 *
	 * @throws Main\SystemException
	 * */
	protected static function saveAgents($agentList, $registeredList): void
	{
		foreach ($agentList as $agentKey => $agent)
		{
			static::saveAgent(
				$agent, $registeredList[$agentKey] ?? null
			);
		}
	}

	/**
	 * ������������ ����� � ���� ������
	 *
	 * @param $agent array ����� ��� �����������
	 * @param $registeredAgent array ����� ������������������ �����
	 *
	 * @throws Main\SystemException
	 * */
	protected static function saveAgent($agent, $registeredAgent): void
	{
		global $APPLICATION;

		$agentData = [
			'NAME'           => $agent['name'],
			'MODULE_ID'      => Config::getModuleName(),
			'SORT'           => $agent['sort'],
			'ACTIVE'         => 'Y',
			'AGENT_INTERVAL' => $agent['interval'],
			'IS_PERIOD'      => 'N',
			'USER_ID'        => 0
		];

		if (!empty($agent['next_exec']))
		{
			$agentData['NEXT_EXEC'] = $agent['next_exec'];
		}

		if (!isset($registeredAgent)) // ��������� �����, ���� �����������
		{
			$saveResult = CAgent::Add($agentData);
		}
		else if (!static::isNeedUpdateAgent($agentData, $registeredAgent, $agent['update']))
		{
			$saveResult = true;
		}
		else
		{
			$updateData = array_diff_key($agentData, [ 'ACTIVE' => true ]);
			$saveResult = CAgent::Update($registeredAgent['ID'], $updateData);
		}

		if (!$saveResult)
		{
			$exception = $APPLICATION->GetException();

			throw new Main\SystemException(
				'agent '
				. $agent['name']
				. ' register error'
				. ($exception ? ': ' . $exception->GetString() : '')
			);
		}
	}

	/**
	 * ���������� ��������� ����� � ���� ������
	 *
	 * @param $agentRow
	 * @param $registeredRow
	 * @param $rule
	 *
	 * @return bool
	 */
	protected static function isNeedUpdateAgent($agentRow, $registeredRow, $rule = self::UPDATE_RULE_FUTURE): bool
	{
		$result = false;

		if (isset($agentRow['NEXT_EXEC']))
		{
			$nextExec = MakeTimeStamp($agentRow['NEXT_EXEC']);
			$scheduledExec = MakeTimeStamp($registeredRow['NEXT_EXEC']);

			switch ($rule)
			{
				case self::UPDATE_RULE_STRICT:
					$result = ($nextExec !== $scheduledExec);
				break;

				case self::UPDATE_RULE_FUTURE:
				default:
					$result = ($nextExec + $agentRow['AGENT_INTERVAL'] < $scheduledExec); // scheduled next exec in future
				break;
			}
		}

		return $result;
	}

	/**
	 * ������� �������������� ������ �� ���� ������
	 *
	 * @param $agentList array ������ ������� ��� �����������
	 * @param $registeredList array ������ ����� ������������������ �������
	 *
	 * @throws Main\SystemException
	 * */
	protected static function deleteAgents($agentList, $registeredList): void
	{
		foreach ($registeredList as $agentKey => $agentRow)
		{
			if (!isset($agentList[$agentKey]))
			{
				static::deleteAgent($agentRow);
			}
		}
	}

	/**
	 * ������� ����� �� ���� ������
	 *
	 * @param $registeredRow array ����� ������������������ �����
	 *
	 * @throws Main\SystemException
	 * */
	protected static function deleteAgent($registeredRow): void
	{
		$deleteResult = CAgent::Delete($registeredRow['ID']);

		if (!$deleteResult)
		{
			throw new Main\SystemException('agent ' . $registeredRow['NAME'] . ' not deleted');
		}
	}
}

