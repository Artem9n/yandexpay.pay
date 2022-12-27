<?php
namespace YandexPay\Pay\Trading\Action\Rest\OrderCreate\Stage;

use YandexPay\Pay\Reference\Concerns;
use YandexPay\Pay\Trading\Action\Rest\OrderCreate\Request;
use YandexPay\Pay\Trading\Action\Rest\State;

class OrderPaySystem
{
    use Concerns\HasMessage;

	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	public function __invoke(State\OrderCalculation $state)
	{
		$this->fillPaySystem($state);
	}

	protected function fillPaySystem(State\OrderCalculation $state) : void
	{
		$paymentType = $this->request->getPaymentType();
        $data = null;
        $paySystemId = $state->options->getPaymentCard();

		if ($paymentType === 'SPLIT')
		{
            $data = [
                'PAY_SYSTEM_NAME' => self::getMessage('SPLIT_NAME'),
                'COMMENTS' => self::getMessage('SPLIT_COMMENTS'),
            ];
		}
		else if ($paymentType === 'CASH_ON_DELIVERY')
		{
			$paySystemId = $state->options->getPaymentCash();
		}

		if ((int)$paySystemId > 0)
		{
			$state->order->createPayment($paySystemId, null, $data);
		}
	}
}