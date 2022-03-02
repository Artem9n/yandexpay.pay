<?php

namespace YandexPay\Pay\Trading\Settings;

use Bitrix\Main;
use YandexPay\Pay\Config;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Entity;
use YandexPay\Pay\Injection;
use YandexPay\Pay\Ui;
use YandexPay\Pay\Utils;

class Options extends Reference\Skeleton
{
	use Concerns\HasMessage;

	public function isDeliveryStrict() : bool
	{
		return $this->getValue('DELIVERY_STRICT', false);
	}
	
	public function getDeliveryOptions() : Options\DeliveryCollection
	{
		return $this->getFieldsetCollection('DELIVERY_OPTIONS');
	}

	public function getPersonTypeId() : int
	{
		return (int)$this->requireValue('PERSON_TYPE_ID');
	}

	public function getSiteId() : string
	{
		return $this->requireValue('SITE_ID');
	}

	public function getPaymentCash() : ?int
	{
		$result = (int)$this->getValue('PAYSYSTEM_CASH');

		return $result > 0 ? $result : null;
	}

	public function getPaymentCard() : ?int
	{
		$result = (int)$this->getValue('PAYSYSTEM_CARD');

		return $result > 0 ? $result : null;
	}

	public function useBuyerPhone() : bool
	{
		return $this->getProperty('PHONE') !== null;
	}

	public function useBuyerEmail() : bool
	{
		return $this->getProperty('EMAIL') !== null;
	}

	public function getSuccessUrl() : string
	{
		return $this->requireValue('URL_SUCCESS');
	}

	public function getSolution() : ?string
	{
		return $this->getValue('SOLUTION') ?: null;
	}

	public function useBuyerName() : bool
	{
		$result = false;

		$allName = [
			'LAST_NAME',
			'FIRST_NAME',
			'MIDDLE_NAME'
		];

		foreach ($allName as $value)
		{
			if($this->getProperty($value) !== null)
			{
				$result = true;
				break;
			}
		}

		return $result;
	}

	public function getProperty(string $fieldName) : ?int
	{
		$result = (int)$this->getValue('PROPERTY_' . $fieldName);

		return $result > 0 ? $result : null;
	}

	protected function validateSelf() : Main\Result
	{
		$result = new Main\Result();

		if (
			!$this->useBuyerPhone()
			&& !$this->useBuyerEmail()
		)
		{
			$result->addError(new Main\Error(self::getMessage('VALIDATE_ONE_OF_EMAIL_PHONE')));
		}

		/*if (
			!$this->getPaymentCash()
			&& !$this->getPaymentCard()
		)
		{
			$result->addError(new Main\Error('¬ыберите одну из платежных систем дл€ оплаты'));
		}*/

		return $result;
	}

	public function getTabs() : array
	{
		return [
			'COMMON' => [
				'name' => self::getMessage('TAB_COMMON'),
				'sort' => 1000,
			],
		];
	}

	public function getFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return
			$this->getDeliveryFields($environment, $siteId)
			+ $this->getPaymentFields($environment, $siteId)
			+ $this->getCouponFields($environment, $siteId)
			+ $this->getBuyerProperties($environment, $siteId)
			+ $this->getAddressFields($environment, $siteId)
			+ $this->getCommentFields($environment, $siteId)
			+ $this->getSuccessUrlFields($environment, $siteId)
			+ $this->getSolutionFields($environment, $siteId)
			+ $this->getEditSolutionFields($environment, $siteId)
			+ $this->getInjectionFields($environment, $siteId);
	}

	public function getPurchaseUrl() : string
	{
		return Utils\Url::absolutizePath(BX_ROOT . '/tools/' . Config::getModuleName() . '/purchase.php');
	}

	protected function getDeliveryFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		$deliveryOptions = $this->getDeliveryOptions();
		$coordsResult = $environment->getDelivery()->testAdminPickupCoords($siteId);

		return [
			'DELIVERY_STRICT' => [
				'TYPE' => 'boolean',
				'GROUP' => self::getMessage('DELIVERY_GROUP'),
				'NAME' => self::getMessage('DELIVERY_STRICT'),
				'SORT' => 1000,
			],
			'DELIVERY_OPTIONS' => $deliveryOptions->getFieldDescription($environment, $siteId) + [
				'TYPE' => 'fieldset',
				'NAME' => self::getMessage('DELIVERY_OPTIONS'),
				'SORT' => 1010,
				'NOTE' => implode('<br />', $coordsResult->getErrorMessages()),
			],
		];
	}

	protected function getPaymentFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return [
			'PAYSYSTEM_CASH' => [
				'TYPE' => 'enumeration',
				'GROUP' => self::getMessage('PAYSYSTEM'),
				'NAME' => self::getMessage('CASH'),
				'SORT' => 2010,
				'VALUES' => $environment->getPaySystem()->getEnum($siteId, [
					'!=ACTION_FILE' => 'yandexpay',
				]),
				'SETTINGS' => [
					'CAPTION_NO_VALUE' => self::getMessage('CASH_NO_VALUE'),
				],
			],
			'PAYSYSTEM_CARD' => [
				'TYPE' => 'enumeration',
				'MANDATORY' => 'Y',
				'NAME' => self::getMessage('CARD'),
				'SORT' => 2010,
				'VALUES' => $environment->getPaySystem()->getEnum($siteId, [
					'=ACTION_FILE' => 'yandexpay',
				]),
			],
		];
	}

	protected function getCouponFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return [
			'ALLOW_ENTER_COUPON' => [
				'TYPE' => 'boolean',
				'NAME' => self::getMessage('ALLOW_ENTER_COUPON'),
				'SORT' => 2050,
			],
		];
	}

	protected function getBuyerProperties(Entity\Reference\Environment $environment, string $siteId) : array
	{
		$propertyEnum = $environment->getProperty()->getEnum($this->getPersonTypeId());

		return [
			'PROPERTY_LAST_NAME' => [
				'TYPE' => 'orderProperty',
				'GROUP' => self::getMessage('BUYER'),
				'NAME' => self::getMessage('PROPERTY_LAST_NAME'),
				'SORT' => 3009,
				'VALUES' => $propertyEnum,
				'SETTINGS' => [
					'TYPE' => 'LAST_NAME',
					'CAPTION_NO_VALUE' => self::getMessage('NO_PROMPT'),
				],
			],
			'PROPERTY_FIRST_NAME' => [
				'TYPE' => 'orderProperty',
				'NAME' => self::getMessage('PROPERTY_FIRST_NAME'),
				'SORT' => 3010,
				'VALUES' => $propertyEnum,
				'SETTINGS' => [
					'TYPE' => 'FIRST_NAME',
					'CAPTION_NO_VALUE' => self::getMessage('NO_PROMPT'),
				],
			],
			'PROPERTY_MIDDLE_NAME' => [
				'TYPE' => 'orderProperty',
				'NAME' => self::getMessage('PROPERTY_MIDDLE_NAME'),
				'SORT' => 3011,
				'VALUES' => $propertyEnum,
				'SETTINGS' => [
					'TYPE' => 'MIDDLE_NAME',
					'CAPTION_NO_VALUE' => self::getMessage('NO_PROMPT'),
				],
			],
			'PROPERTY_EMAIL' => [
				'TYPE' => 'orderProperty',
				'NAME' => self::getMessage('PROPERTY_EMAIL'),
				'SORT' => 3012,
				'VALUES' => $propertyEnum,
				'SETTINGS' => [
					'TYPE' => 'EMAIL',
					'CAPTION_NO_VALUE' => self::getMessage('NO_PROMPT'),
				],
			],
			'PROPERTY_PHONE' => [
				'TYPE' => 'orderProperty',
				'NAME' => self::getMessage('PROPERTY_PHONE'),
				'SORT' => 3013,
				'VALUES' => $propertyEnum,
				'SETTINGS' => [
					'TYPE' => 'PHONE',
					'CAPTION_NO_VALUE' => self::getMessage('NO_PROMPT'),
				],
			],
		];
	}

	protected function getAddressFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		$propertyEnum = $environment->getProperty()->getEnum($this->getPersonTypeId());

		$propertyFields = [];
		$keys = [
			'ZIP',
			'CITY',
			'ADDRESS',
		];
		$sort = 3100;

		foreach ($keys as $key)
		{
			$propertyFields['PROPERTY_' . $key] = [
				'NAME' => static::getMessage('ADDRESS_' . $key, null, $key),
				'TYPE' => 'orderProperty',
				'GROUP' => static::getMessage('ADDRESS'),
				'SORT' => $sort++,
				'VALUES' => $propertyEnum,
				'SETTINGS' => [
					'TYPE' => $key,
					'CAPTION_NO_VALUE' => self::getMessage('NO_VALUE'),
				],
			];
		}

		return  $propertyFields;
	}

	protected function getCommentFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return [
			'ALLOW_ENTER_COMMENT' => [
				'TYPE' => 'boolean',
				'NAME' => self::getMessage('ALLOW_ENTER_COMMENT'),
				'SORT' => 3150,
				'SETTINGS' => [
					'DEFAULT_VALUE' => Ui\UserField\BooleanType::VALUE_TRUE,
				],
			],
		];
	}

	protected function getSuccessUrlFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		$solution = $this->makeSolutionDefault($environment, $siteId);
		$context = [
			'SITE_DIR' => $environment->getSite()->getDir($siteId)
		];

		return [
			'URL_SUCCESS' => [
				'TYPE' => 'string',
				'NAME' => self::getMessage('URL_SUCCESS'),
				'GROUP' => self::getMessage('YANDEX_PAY'),
				'SORT' => 4000,
				'SETTINGS' => [
					'DEFAULT_VALUE' => $solution !== null ? $solution->getOrderPath($context) : '/personal/order/make/',
				],
			],
		];
	}

	protected function getSolutionFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		$solutions = $this->makeSolutions($environment, $siteId);
		$solution = $this->makeSolutionDefault($environment, $siteId);

		return [
			'SOLUTION' => [
				'TYPE' => 'enumeration',
				'NAME' => self::getMessage('SOLUTION'),
				'SORT' => 4010,
				'VALUES' => $solutions,
				'SETTINGS' => [
					'CAPTION_NO_VALUE' => self::getMessage('NO_DEFAULTS'),
					'DEFAULT_VALUE' => $solution !== null ? $solution->getType() : null
				]
			]
		];
	}

	protected function getEditSolutionFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return [
			'EDIT_SOLUTION' => [
				'TYPE' => 'boolean',
				'NAME' => self::getMessage('EDIT_SOLUTION'),
				'SORT' => 4020,
				'SETTINGS' => [
					'DEFAULT_VALUE' => Ui\UserField\BooleanType::VALUE_FALSE,
				],
			]
		];
	}

	protected function makeSolutions(Entity\Reference\Environment $environment, string $siteId) : array
	{
		$result = [];
		$siteTemplates = $environment->getSite()->getTemplate($siteId);

		foreach (Injection\Solution\Registry::getTypes() as $type)
		{
			$solution = Injection\Solution\Registry::getInstance($type);

			$isMatch = $solution->isMatch(['TEMPLATES' => $siteTemplates]);

			if (!$isMatch) { continue; }

			$result[$type] = [
				'ID' => $type,
				'VALUE' => $solution->getTitle(),
			];
		}

		return $result;
	}

	protected function makeSolutionDefault(Entity\Reference\Environment $environment, string $siteId) : ?Injection\Solution\Skeleton
	{
		$result = null;
		$siteTemplates = $environment->getSite()->getTemplate($siteId);

		foreach (Injection\Solution\Registry::getTypes() as $type)
		{
			$solution = Injection\Solution\Registry::getInstance($type);
			$isMatch = $solution->isMatch(['TEMPLATES' => $siteTemplates]);

			if (!$isMatch) { continue; }

			$result = $solution;
			break;
		}

		return $result;
	}

	protected function getInjectionFields(Entity\Reference\Environment $environment, string $siteId) : array
	{
		return [
			'INJECTION' => [
				'TYPE' => 'reference',
				'MULTIPLE' => 'Y',
				'GROUP' => self::getMessage('YANDEX_PAY'),
				'NAME' => self::getMessage('INJECTION'),
				'SORT' => 5000,
				'SETTINGS' => [
					'DEFAULT_VALUE' => $this->makeInjectionDefaults($environment, $siteId),
					'DATA_CLASS' => Injection\Setup\RepositoryTable::class,
					'REFERENCE' => [ 'ID' => 'TRADING_ID' ],
					'SUMMARY' => '#BEHAVIOR# (#SETTINGS.ELEMENT_IBLOCK#) (#SETTINGS.ORDER_PATH#) (#SETTINGS.BASKET_PATH#)',
					'LAYOUT' => 'summary',
					'MODAL_WIDTH' => 600,
					'MODAL_HEIGHT' => 450,
				],
				'DEPEND' => [
					'EDIT_SOLUTION' => [
						'RULE' => Utils\Userfield\DependField::RULE_ANY,
						'VALUE' => true,
					],
				],
			],
		];
	}

	protected function makeInjectionDefaults(Entity\Reference\Environment $environment, string $siteId) : array
	{
		$solution = $this->makeSolutionDefault($environment, $siteId);

		if ($solution === null) { return []; }

		$result = [];
		$map = $solution->getDefaults([
			'SITE_ID' => $siteId,
			'SITE_DIR' => $environment->getSite()->getDir($siteId),
			'IBLOCK' => $environment->getCatalog()->getIblock($siteId),
		]);

		foreach ($map as $type => $options)
		{
			$result[] = [
				'BEHAVIOR' => $type,
				'SETTINGS' => $this->prefixInjectionDefaults(mb_strtoupper($type . '_'), $options),
			];
		}

		return $result;
	}

	protected function prefixInjectionDefaults(string $prefix, array $values) : array
	{
		$result = [];

		foreach ($values as $key => $value)
		{
			$result[$prefix . $key] = $value;
		}

		return $result;
	}

	protected function getFieldsetCollectionMap() : array
	{
		return [
			'DELIVERY_OPTIONS' => Options\DeliveryCollection::class,
		];
	}
}