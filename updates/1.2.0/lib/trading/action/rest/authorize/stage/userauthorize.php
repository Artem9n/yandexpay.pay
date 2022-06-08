<?php
namespace YandexPay\Pay\Trading\Action\Rest\Authorize\Stage;

use YandexPay\Pay\Trading\Action\Rest\State;
use YandexPay\Pay\Trading\Entity\Registry as EntityRegistry;

class UserAuthorize
{
	public function __invoke(State\Payment $state)
	{
		$this->authorize($state);
	}

	protected function authorize(State\Payment $state) : void
	{
		global $USER;

		$userId = $state->order->getUserId();
		$user = $state->environment->getUserRegistry()->getUser(['ID' => $userId]);
		$justRegistered = $user->isJustRegistered();
		$isAuthorized = $USER->IsAuthorized();

		if ($justRegistered && !$isAuthorized)
		{
			$USER->Authorize($userId);
		}

		if (!$isAuthorized)
		{
			if (!is_array($_SESSION['SALE_ORDER_ID']))
			{
				$_SESSION['SALE_ORDER_ID'] = [];
			}

			$_SESSION['SALE_ORDER_ID'][] = $state->order->getId();
		}
	}
}

