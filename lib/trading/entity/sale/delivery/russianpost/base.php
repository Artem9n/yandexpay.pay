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
		return false;
	}

	public function load() : bool
	{
		return Main\Loader::includeModule('russianpost.post');
	}

	public function getServiceType() : string
	{
		return '';
	}

	public function markSelected(Sale\OrderBase $order, string $storeId = null, string $address = null) : void
	{

	}

	protected function getTariff(Sale\OrderBase $order, string $indexTo) : ?array
	{
		$result = null;

		try {

			$httpClient = new Main\Web\HttpClient();
			$account = $this->getAccount();
			$accountId = $account[$this->accountId];

			$data = $httpClient->get(sprintf('https://widget.pochta.ru/api/data/free_tariff_by_settings?id=%s&indexTo=%s&weight=%s&sumoc=%s',
				$accountId,
				$indexTo,
				(int)$order->getBasket()->getWeight(),
				(int)($order->getPrice() * 100)
			));
			$data = Main\Web\Json::decode($data);
			$result = $data;

		}
		catch (Main\SystemException $exception)
		{
			// nothing
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
			//nothing
		}

		return $result;
	}
}