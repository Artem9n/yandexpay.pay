<?php

namespace YandexPay\Pay\Gateway;

use Bitrix\Main;
use Bitrix\Main\ORM\EntityError;
use YandexPay\Pay\Reference;

class OtherHandlerMode extends Reference\Event\Regular
{
	use Reference\Concerns\HasMessage;

	public static function getHandlers() : array
	{
		return [
			[
				'module' => 'sale',
				'event' => '\\Bitrix\\Sale\\Internals\\PaySystemAction::' . Main\ORM\Data\DataManager::EVENT_ON_BEFORE_ADD,
				'method' => 'onBeforeUpdate',
			],
			[
				'module' => 'sale',
				'event' => '\\Bitrix\\Sale\\Internals\\PaySystemAction::' . Main\ORM\Data\DataManager::EVENT_ON_BEFORE_UPDATE,
				'method' => 'onBeforeUpdate',
			],
		];
	}

	public static function onBeforeUpdate(Main\ORM\Event $event) : Main\ORM\EventResult
	{
		$result = new Main\ORM\EventResult();

		$fields = $event->getParameter('fields');

		if ($fields['ACTION_FILE'] !== 'yandexpay') { return $result; }

		if ($fields['PS_MODE'] !== Manager::OTHER) { return $result; }

		$result->addError(new EntityError(self::getMessage('NOT_GATEWAY_OTHER')));

		return $result;
	}
}