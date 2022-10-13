<?php

namespace YandexPay\Pay\Utils;

use Bitrix\Main;

class PsModeFilter implements Main\Type\IRequestFilter
{
	protected $psMode;

	public function filter(array $values) : array
	{
		if ($this->psMode === null) { return $values; }

		$values['post']['PS_MODE'] = $this->psMode;

		return $values;
	}

	public function setPsMode(string $mode) : void
	{
		$this->psMode = $mode;
	}
}