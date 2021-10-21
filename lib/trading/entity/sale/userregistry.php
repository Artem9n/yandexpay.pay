<?php

namespace YandexPay\Pay\Trading\Entity\Sale;

use YandexPay\Pay\Trading\Entity\Reference as EntityReference;

class UserRegistry extends EntityReference\UserRegistry
{
	/** @var Environment */
	protected $environment;

	public function __construct(Environment $environment)
	{
		parent::__construct($environment);
	}

	protected function createUser(array $data) : EntityReference\User
	{
		return new User($this->environment, $data);
	}
}