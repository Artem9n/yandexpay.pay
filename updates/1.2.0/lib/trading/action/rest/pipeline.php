<?php
namespace YandexPay\Pay\Trading\Action\Rest;

use YandexPay\Pay\Trading\Action\Rest\State;

class Pipeline
{
	private $stages = [];

	public function pipe(callable $stage) : Pipeline
	{
		$this->stages[] = $stage;

		return $this;
	}

	public function process(State\Common $payload) : State\Common
	{
		foreach ($this->stages as $stage)
		{
			$stage($payload);
		}

		return $payload;
	}

	public function __invoke(State\Common $payload)
	{
		$this->process($payload);
	}
}
