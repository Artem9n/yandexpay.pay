<?php

namespace YandexPay\Pay\Reference\Event;

use Bitrix\Main;

abstract class Base
{
	protected static $handlerDisallowYaPay = false;

	public static function getClassName(): string
	{
		return '\\' . static::class;
	}

	/**
	 * ��������� �������
	 *
	 * @param $handlerParams array|null ��������� �����������, �����:
	 *               module => string # �������� ������
	 *               event => string, # �������� �������
	 *               method => string, # �������� ������ (�������������)
	 *               sort => integer, # ���������� (�������������)
	 *               arguments => array # ��������� (�������������)
	 *
	 * @throws Main\NotImplementedException
	 * @throws Main\SystemException
	 * */
	public static function register($handlerParams = null): void
	{
		$className = static::getClassName();

		$handlerParams = !isset($handlerParams) ? static::getDefaultParams() : array_merge(
			static::getDefaultParams(),
			$handlerParams
		);

		Controller::register($className, $handlerParams);
	}

	/**
	 * ������� �������
	 *
	 * @param null $handlerParams
	 */
	public static function unregister($handlerParams = null): void
	{
		$className = static::getClassName();

		$handlerParams = !isset($handlerParams) ? static::getDefaultParams() : array_merge(
			static::getDefaultParams(),
			$handlerParams
		);

		Controller::unregister($className, $handlerParams);
	}

	/**
	 * @return array �������� ����������� ��� ���������� �� ���������, �����:
	 *               module => string # �������� ������
	 *               event => string, # �������� �������
	 *               method => string, # �������� ������ (�������������)
	 *               sort => integer, # ���������� (�������������)
	 *               arguments => array # ��������� (�������������)
	 * */

	public static function getDefaultParams(): array
	{
		return [];
	}
}
