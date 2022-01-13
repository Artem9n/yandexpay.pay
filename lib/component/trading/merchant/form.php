<?php

namespace YandexPay\Pay\Component\Trading\Merchant;

use Bitrix\Main;
use YandexPay\Pay;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Entity as TradingEntity;

class Form extends Pay\Component\Plain\Form
{
	use Concerns\HasMessage;

	protected $environment;

	public function prepareComponentParams(array $params) : array
	{
		$params['FIELDS'] = [
			'SHOP_NAME' => [
				'TYPE' => 'string',
				'MANDATORY' => 'Y',
				'NAME' => self::getMessage('SHOP_NAME'),
				'SETTINGS' => [
					'SIZE' => 30,
					'DEFAULT_VALUE' => $this->getShopName(),
				],
			],
			'SITE_DOMAINS' => [
				'TYPE' => 'string',
				'MANDATORY' => 'Y',
				'MULTIPLE' => 'Y',
				'NAME' => self::getMessage('SITE_DOMAINS'),
				'SETTINGS' => [
					'SIZE' => 30,
					'MIN_COUNT' => 3,
					'DEFAULT_VALUE' => $this->getDomains(),
				],
			],
		];

		return $params;
	}

	public function validate(array $data, array $fields = null) : Main\Result
	{
		$result = parent::validate($data, $fields);

		if (!$result->isSuccess()) { return $result; }

		if (isset($data['SITE_DOMAINS'], $fields['SITE_DOMAINS']))
		{
			$result = $this->validateDomains((array)$data['SITE_DOMAINS']);
		}

		return $result;
	}

	protected function validateDomains(array $domains): Main\Result
	{
		return new Main\Result(); // todo
	}

	public function load($primary, array $select = [], bool $isCopy = false) : array
	{
		return [];
	}

	public function add(array $values) : Main\ORM\Data\AddResult
	{
		$save = new Main\ORM\Data\AddResult();
		$save->setId(1);
		return $save;
	}

	public function update($primary, array $values) : Main\ORM\Data\UpdateResult
	{
		return new Main\ORM\Data\UpdateResult();
	}

	protected function getEnvironment() : TradingEntity\Reference\Environment
	{
		if ($this->environment === null)
		{
			$this->environment = TradingEntity\Registry::getEnvironment();
		}

		return $this->environment;
	}

	protected function getShopName() : string
	{
		return Main\Config\Option::get('main', 'site_name', '');
	}

	protected function getDomains() : array
	{
		$environment = $this->getEnvironment();
		$result = [];

		foreach ($environment->getSite()->getVariants() as $siteId)
		{
			$params = [
				'protocol' => 'https',
				'host' => Pay\Data\SiteDomain::getHost($siteId),
			];

			$result[] = Pay\Utils\Url::absolutizePath('', $params);
		}

		return $result;
	}
}