<?php

namespace YandexPay\Pay\Trading\Entity\Reference;

use Bitrix\Main;

abstract class UserRegistry
{
	protected $environment;
	protected $anonymousUsers = [];
	protected $users = [];

	public function __construct(Environment $environment)
	{
		$this->environment = $environment;
	}

	public function getUser(array $data) : User
	{
		$primary = $data['ID'] ?? null;

		if ($primary === null)
		{
			$result = $this->createUser($data);
		}
		else if (isset($this->users[$primary]))
		{
			$result = $this->users[$primary];
		}
		else
		{
			$result = $this->createUser($data);
			$this->users[$primary] = $result;
		}

		return $result;
	}

	/**
	 * @param array $data
	 *
	 * @return User
	 */
	protected function createUser(array $data) : User
	{
		throw new Main\NotImplementedException('createUser is missing');
	}
}