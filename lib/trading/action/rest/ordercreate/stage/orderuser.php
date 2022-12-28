<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderCreate\Stage;

use Bitrix\Main;
use YandexPay\Pay\Exceptions;
use YandexPay\Pay\Trading\Action\Rest\OrderCreate;
use YandexPay\Pay\Trading\Action\Rest\State;

class OrderUser
{
	protected $request;

	public function __construct(OrderCreate\Request $request)
	{
		$this->request = $request;
	}

	public function __invoke(State\OrderCalculation $state)
	{
		if (!$this->needRegister()) { return; }

		$state->userId = $this->createUser($state);
		$state->order->setUserId($state->userId);
	}

	/** @noinspection PhpCastIsUnnecessaryInspection */
	protected function needRegister() : bool
	{
		$userId = $this->request->getUserId();

		return ($userId === 0 || $userId === (int)\CSaleUser::GetAnonymousUserID());
	}

	protected function createUser(State\OrderCalculation $state) : int
	{
		$userData = $this->request->getUser();

		$user = $state->environment->getUserRegistry()->getUser([
			'EMAIL' => $userData->getEmail(),
			'PHONE' => $userData->getPhone(),
			'FIRST_NAME' => $userData->getFirstName(),
			'LAST_NAME' => $userData->getLastName(),
			'SECOND_NAME' => $userData->getSecondName(),
			'SITE_ID' => $state->setup->getSiteId(),
		]);

		$userId = $user->getId(); //todo allowAppendOrder options

		if ($userId !== null)
		{
			return $userId;
		}

		/** @var Main\ORM\Data\AddResult $installResult */
		$installResult = $user->install();

		Exceptions\Facade::handleResult($installResult);

		$userId = $installResult->getId();
		//$USER->Authorize($userId); //todo may be not auth? work do?

		return $userId;
	}
}