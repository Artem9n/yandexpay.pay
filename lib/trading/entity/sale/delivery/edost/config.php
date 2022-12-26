<?php
namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Edost;

class Config
{
	protected $config;

	public function __construct()
	{
		$this->loadConfig();
	}

	public function loadConfig() : void
	{
		$config = \CDeliveryEDOST::GetEdostConfig('all');
		$this->config = $config['all'];
	}

	public function getConfig() : array
	{
		return $this->config;
	}

	public function getId() : string
	{
		return $this->config['id'];
	}

	public function getKey() : string
	{
		return $this->config['ps'];
	}
}