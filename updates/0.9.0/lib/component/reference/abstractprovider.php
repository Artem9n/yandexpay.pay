<?php

namespace YandexPay\Pay\Component\Reference;

use Bitrix\Main;

abstract class AbstractProvider
{
	protected $component;

	public function __construct(\CBitrixComponent $component)
	{
		$this->component = $component;
	}

	public function prepareComponentParams(array $params) : array
	{
		return $params;
	}

	/** @return String[] */
	public function getRequiredParams() : array
	{
		return [];
	}

	/** @return String[] */
	public function getRequiredModules() : array
	{
		return [];
	}

	public function getComponentResult($key)
	{
		return $this->component->arResult[$key] ?? null;
	}

	public function getComponentParam($key)
	{
		return $this->component->arParams[$key] ?? null;
	}

	public function setComponentParam($key, $value) : void
	{
		$this->component->arParams[$key] = $value;
	}

	public function getComponentLang($key, $replaces = null)
	{
		return $this->component->getLang($key, $replaces);
	}

	public function processAjaxAction(string $action, array $data) : void
	{
		throw new Main\SystemException('ACTION_NOT_FOUND');
	}
}