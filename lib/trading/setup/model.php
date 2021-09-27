<?php

namespace YandexPay\Pay\Trading\Setup;

class Model extends EO_Repository
{
	public function activate() : void
	{
		$this->setActive(true);
		$this->save();
	}

	public function deactivate() : void
	{
		$this->setActive(false);
		$this->save();
	}
}