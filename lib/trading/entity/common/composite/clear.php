<?php

namespace YandexPay\Pay\Trading\Entity\Common\Composite;

use Bitrix\Main;
use YandexPay\Pay\Reference;

class Clear extends Reference\Agent\Base
{
	public static function clear(string $domain) : bool
	{
		$query = Main\Composite\Internals\Model\PageTable::getList([
			'filter' => [
				'HOST' => $domain,
				'URI' => '%catalog%'
			],
			'select' => [ 'ID', 'URI']
		]);

		while ($page = $query->fetch())
		{
			$composite = new Main\Composite\Page($page['URI'], $domain);
			$composite->delete();
		}

		return false;
	}
}