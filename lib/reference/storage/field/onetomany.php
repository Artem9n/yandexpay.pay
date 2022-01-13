<?php
namespace YandexPay\Pay\Reference\Storage\Field;

use Bitrix\Main\ORM;

class OneToMany extends ORM\Fields\Relations\OneToMany
{
	public function __construct($name, $referenceEntity, $referenceName)
	{
		parent::__construct($name, $referenceEntity, $referenceName);

		if (method_exists($this, 'configureCascadeDeletePolicy'))
		{
			$this->configureCascadeDeletePolicy(ORM\Fields\Relations\CascadePolicy::FOLLOW);
		}
	}
}