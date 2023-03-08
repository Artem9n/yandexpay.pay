<?php

namespace YandexPay\Pay\Trading\Settings\Options\Courier;

use Bitrix\Main;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Entity;
use YandexPay\Pay\Trading\Settings;

class DateInterval extends Settings\Reference\Fieldset
{
	use Concerns\HasMessage;

	public function getFromDate() : ?string
	{
		$day = trim($this->getValue('FROM_DATE'));

		if ($day === '') { return null; }

		$date = new Main\Type\DateTime();
		$date->add(sprintf('+%s day', $day));

		return $date->format('Y-m-d');
	}

	public function getToDate() : ?string
	{
		$day = trim($this->getValue('TO_DATE'));

		if ($day === '') { return null; }

		$date = new Main\Type\DateTime();
		$date->add(sprintf('+%s day', $day));

		return $date->format('Y-m-d');
	}

	public function getFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return [
			'FROM_DATE' => [
				'TYPE' => 'string',
				'NAME' => self::getMessage('TO_DATE'),
				'HELP' => self::getMessage('TO_DATE_HELP'),
				'SETTINGS' => [
					'SIZE' => 2,
					'MAX_LENGTH' => 2,
				],
			],
			'TO_DATE' => [
				'TYPE' => 'string',
				'NAME' => self::getMessage('TO_DATE'),
				'HELP' => self::getMessage('TO_DATE_HELP'),
				'SETTINGS' => [
					'SIZE' => 2,
					'MAX_LENGTH' => 2,
				],
			],
		];
	}
}