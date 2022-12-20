<?php
namespace YandexPay\Pay\Logger;

use Bitrix\Main;
use YandexPay\Pay\Psr;
use YandexPay\Pay\Trading\Setup as TradingSetup;

class Logger extends Psr\Log\AbstractLogger
{
	protected $setup;
	protected $url;
	protected $level = Level::WARNING;

	public function __construct(TradingSetup\Model $setup = null)
	{
		$this->setup = $setup;
	}

	public function setLevel(string $level = null) : void
	{
		$this->level = $level ?? Level::WARNING;
	}

	public function setSetup(TradingSetup\Model $setup) : void
	{
		$this->setup = $setup;
	}

	public function setUrl(string $path) : void
	{
		$this->url = $path;
	}

	public function log($level, $message, array $context = []) : void
	{
		if (!Level::isMatch($this->level, $level)) { return; }

		$saved = Table::add([
			'SETUP_ID' => $this->setup !== null ? $this->setup->getId() : 0,
			'LEVEL' => $level,
			'MESSAGE' => $message,
			'AUDIT' => $context['AUDIT'] ?? Audit::UNKNOWN,
			'URL' => $context['URL'] ?? $this->url ?? '',
			'TIMESTAMP_X' => new Main\Type\DateTime(),
		]);

		if (!$saved->isSuccess())
		{
			trigger_error(implode(PHP_EOL, $saved->getErrorMessages()), E_USER_WARNING);
		}
	}
}