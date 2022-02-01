<?php

namespace YandexPay\Pay\Trading\Entity\Reference;

use Bitrix\Main;

abstract class User
{
	protected $environment;
	protected $data;

	public function __construct(Environment $environment, $data)
	{
		$this->environment = $environment;
		$this->data = $data;
	}

	public function checkInstall()
	{
		// nothing by default
	}

	public function isInstalled()
	{
		return ($this->getId() > 0);
	}

	/**
	 * @param array $data
	 *
	 * @return Main\Entity\AddResult
	 */
	public function install(array $data = []) : Main\Entity\AddResult
	{
		throw new Main\NotImplementedException('install is missing');
	}

	/**
	 * @param array $data
	 *
	 * @return Main\Entity\UpdateResult
	 */
	public function update(array $data)
	{
		throw new Main\NotImplementedException(static::class, 'update is missing');
	}

	/**
	 * @param string $code
	 *
	 * @return Main\Entity\UpdateResult
	 */
	public function migrate($code)
	{
		throw new Main\NotImplementedException(static::class, 'migrate is missing');
	}

	/**
	 * @param int $groupId
	 *
	 * @return Main\Result
	 */
	public function attachGroup($groupId)
	{
		throw new Main\NotImplementedException(static::class, 'attachGroup is missing');
	}

	/**
	 * @return int|null
	 */
	public function getId() : ?int
	{
		throw new Main\NotImplementedException(static::class, 'getId is missing');
	}
}