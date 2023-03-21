<?php
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */
namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\Edost;

use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Entity\Sale\Delivery\AbstractAdapter;

class Base extends AbstractAdapter
{
	use Concerns\HasMessage;

	protected $title;
	protected $format;

	public function isMatch(Sale\Delivery\Services\Base $service) : bool
	{
		if (!($service instanceof Sale\Delivery\Services\AutomaticProfile)) { return false; }

		if (!$this->load()) { return false; }

		$code = $service->getCode();

		if (mb_strpos($code, 'edost') === false) { return false; }

		$profile = \CDeliveryEDOST::GetEdostProfile($service->getId());
		$tariffId = (int)$profile['tariff'];
		$format = \edost_class::GetFormat($profile);

		if (in_array($tariffId, [35,56,57,58])) // shop office
		{
			$format = 'office';
		}
		else if(in_array($tariffId, [31,32,33,34])) // shop courier
		{
			$format = 'door';
		}

		$this->title = $service->getName();

		return in_array($format, $this->format);
	}

	public function load() : bool
	{
		return Main\Loader::includeModule('edost.delivery');
	}

	public function providerType() : ?string
	{
		$result = null;

		$providerMap = [
			'BOXBERRY',
			'RUSSIAN_POST',
			'CDEK',
			'PICKPOINT',
			//'PEK',
			//'FIVEPOST',
			//'OZON_ROCKET',
			//'DPD',
			//'YANDEX_DELIVERY',
		];

		foreach ($providerMap as $providerType)
		{
			$searchPart = self::getMessage(sprintf('PART_PROVIDER_%s', $providerType));
			if (mb_strpos(mb_strtolower($this->title), $searchPart) === false) { continue; }
			$result = $providerType;
			break;
		}

		return $result;
	}

	protected function addressCode(Sale\Order $order) : string
	{
		return '';
	}

	protected function zipCode(Sale\Order $order) : string
	{
		return '';
	}
}
