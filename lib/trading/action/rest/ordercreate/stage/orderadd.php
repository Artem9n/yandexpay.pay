<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderCreate\Stage;

use Bitrix\Main;
use Bitrix\Sale;
use YandexPay\Pay\Exceptions;
use YandexPay\Pay\Trading\Action\Rest\OrderCreate\Request;
use YandexPay\Pay\Trading\Action\Rest\State;

class OrderAdd
{
	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	public function __invoke(State\OrderCalculation $state)
	{
		$this->addOrder($state);
	}

	protected function addOrder(State\OrderCalculation $state) : void
	{
		//global $USER;

		$externalId = $state->order->getId();
		$saveResult = $state->order->add($externalId);

		Exceptions\Facade::handleResult($saveResult);

		$saveData = $saveResult->getData();

		if (!isset($saveData['ID']))
		{
			$errorMessage = 'ORDER_ACCEPT_SAVE_RESULT_ID_NOT_SET'; //$this->getLang('ORDER_ACCEPT_SAVE_RESULT_ID_NOT_SET');
			throw new Main\SystemException($errorMessage);
		}

		/*if (!$USER->IsAuthorized()) // TODO user id, auth
		{
			if (!is_array($_SESSION['SALE_ORDER_ID']))
			{
				$_SESSION['SALE_ORDER_ID'] = [];
			}

			$_SESSION['SALE_ORDER_ID'][] = (int)$saveData['INTERNAL_ID'];
		}

		return (string)$saveData['ID'];*/
	}
}