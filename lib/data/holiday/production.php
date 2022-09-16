<?php

namespace YandexPay\Pay\Data\Holiday;

use YandexPay\Pay\Reference\Concerns;

class Production extends National
{
	use Concerns\HasMessage;

	public function title() : string
	{
		return self::getMessage('TITLE');
	}

	public function holidays() : array
	{
		return array_unique(array_merge(parent::holidays(), [
			'06.03',
			'07.03',
			'08.03',
			'30.04',
			'01.05',
			'02.05',
			'03.05',
			'07.05',
			'08.05',
			'09.05',
			'10.05',
			'11.06',
			'12.06',
			'13.06',
			'04.11',
		]));
	}

	public function workdays() : array
	{
		return [
			'22.02',
			'05.03',
			'03.11',
		];
	}
}