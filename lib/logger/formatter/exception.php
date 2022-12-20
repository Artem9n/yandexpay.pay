<?php
namespace YandexPay\Pay\Logger\Formatter;

class Exception extends Skeleton
{
	protected $throwable;
	protected $context;

	public function __construct(\Throwable $throwable, array $context = [])
	{
		$this->throwable = $throwable;
		$this->context = $context;
	}

	public function message() : string
	{
		return $this->throwable->getMessage();
	}

	public function context() : array
	{
		return $this->context + [
			'TRACE' => $this->throwable->getTraceAsString(),
		];
	}
}