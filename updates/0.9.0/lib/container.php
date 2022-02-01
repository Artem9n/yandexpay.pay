<?php

namespace YandexPay\Pay;

use Psr;

class Container
{
	/** @var self */
	private static $instance;
	private $logger;

	public static function getInstance() : self
	{
		if (!isset(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function getLogger() : Logger
	{
		if ($this->logger === null)
		{
			$this->logger = new Logger();
		}

		return $this->logger;
	}
}