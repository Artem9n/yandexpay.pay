<?php
namespace YandexPay\Pay\Trading\Action\Rest\ButtonData\Stage;

use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay\Exceptions;
use YandexPay\Pay\Trading\Action\Rest\OrderCreate\Request;
use YandexPay\Pay\Trading\Action\Rest\State;

class SearchUser
{
	public function __invoke(State\OrderCalculation $state)
	{
		global $USER;

		$state->userId = $USER->IsAuthorized() ? (int)$USER->GetID() : null;
		$state->fUserId = Sale\FUser::getId(true);
	}
}