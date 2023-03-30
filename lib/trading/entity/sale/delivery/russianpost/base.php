<?php

namespace YandexPay\Pay\Trading\Entity\Sale\Delivery\RussianPost;

use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay\Trading\Entity\Sale\Delivery\AbstractAdapter;

/** @property \Sale\Handlers\Delivery\RussianpostProfile $service */
class Base extends AbstractAdapter
{
	protected $title;
	protected $accountId;

	public function isMatch(Sale\Delivery\Services\Base $service) : bool
	{
		if (!($service instanceof \Sale\Handlers\Delivery\RussianpostProfile)) { return false; }

		$code = $service->getCode();

		$this->title = $service->getNameWithParent();

		return $code === $this->codeService;
	}

	public function load() : bool
	{
		return Main\Loader::includeModule('russianpost.post');
	}

	protected function getTariff(Sale\Order $order, string $indexTo) : ?array
	{
		$result = null;

		try {

			$httpClient = new Main\Web\HttpClient();
			$account = $this->getAccount();

			$params = [
				'id' => $account[$this->accountId],
				'indexTo' => $indexTo,
				'weight' => (int)$order->getBasket()->getWeight(),
				'sumoc' => (int)($order->getPrice() * 100),
			];

			$url = 'https://widget.pochta.ru/api/data/free_tariff_by_settings?' . http_build_query($params);
			$data = $httpClient->get($url);
			$data = Main\Web\Json::decode($data);
			$result = $data;

		}
		catch (Main\SystemException $exception)
		{
			trigger_error('getTariff: ' . $exception->getMessage(), E_USER_WARNING);
		}

		return $result;
	}

	protected function getAccount() : ?array
	{
		$result = null;

		try {

			$httpClient = new Main\Web\HttpClient();
			$httpClient->setHeader('Content-Type', 'application/json');

			$formatData = Main\Web\Json::encode([
				'accountId' => Main\Config\Option::get('russianpost.post', 'GUID_ID'),
				'accountType' => 'bitrix_cms',
			]);

			$httpClient->post('https://widget.pochta.ru/api/sites/public_show', $formatData);
			$data = Main\Web\Json::decode($httpClient->getResult());
			$result = $data;
		}
		catch (Main\SystemException $exception)
		{
			trigger_error('getAccount: ' . $exception->getMessage(), E_USER_WARNING);
		}

		return $result;
	}

	protected function fillTariff(Sale\Order $order, string $zip)
	{
		$tariff = $this->getTariff($order, $zip);

		if ($tariff !== null)
		{
			/** @var \Bitrix\Sale\PropertyValue $property */
			foreach ($order->getPropertyCollection() as $property)
			{
				if ($property->getField('CODE') === 'RUSSIANPOST_TYPEDLV')
				{
					$property->setValue($tariff['type']);
					break;
				}
			}
		}
	}

	public function providerType() : ?string
	{
		return 'RUSSIAN_POST';
	}

	protected function zipCode(Sale\Order $order) : string
	{
		return (string)\Russianpost\Post\Optionpost::get('zip', true, $order->getSiteId());
	}

	protected function addressCode(Sale\Order $order) : string
	{
		return (string)\Russianpost\Post\Optionpost::get('address', true, $order->getSiteId());
	}
}