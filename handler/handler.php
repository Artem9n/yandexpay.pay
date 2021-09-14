<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Request;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\PaySystem\Manager;
use Bitrix\Sale\PaySystem\ServiceResult;
use Yandexpay\Pay\Exceptions\Secure3dRedirect;
use Yandexpay\Pay\GateWay;

Loader::includeModule('yandexpay.pay');

class YandexPayHandler extends PaySystem\ServiceHandler implements PaySystem\IRefund, PaySystem\IPrePayable
{
	protected const STEP_3DS = '3ds';
	protected const STEP_FINISHED = 'finished';
	protected const STEP_ERRORS = 'errors';

	protected const YANDEX_TEST_MODE = 'SANDBOX';
	protected const YANDEX_PRODUCTION_MODE = 'PRODUCTION';

	/** @var \Yandexpay\Pay\GateWay\Base|null */
	protected $gateway;

	protected function getPrefix(): string
	{
		return 'YANDEX_PAY_';
	}

	/**
	 * @inheritDoc
	 */
	public function initiatePay(Payment $payment, Request $request = null) : PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		$gatewayType = $this->getHandlerMode();

		$gatewayMerchantId = $this->getParamValue($payment, $gatewayType. '_PAYMENT_GATEWAY_MERCHANT_ID');

		$params = [
			'order'                 => $this->getOrderData($payment),
			'env'                   => $this->isTestMode($payment) ? self::YANDEX_TEST_MODE : self::YANDEX_PRODUCTION_MODE,
			'merchantId'            => $this->getParamValue($payment, 'MERCHANT_ID'),
			'merchantName'          => $this->getParamValue($payment, 'MERCHANT_NAME'),
			'buttonTheme'           => $this->getParamValue($payment, 'VARIANT_BUTTON'),
			'buttonWidth'           => $this->getParamValue($payment, 'WIDTH_BUTTON'),
			'gateway'               => mb_strtolower($gatewayType),
			'gatewayMerchantId'     => $gatewayMerchantId,
			'externalId'            => $payment->getId(),
			'paySystemId'           => $this->service->getField('ID'),
			'currency'              => $payment->getField('CURRENCY')
		];

        if ($this->getGateway($gatewayType) !== null)
        {
	        $this->setExtraParams($params);

	        $showTemplateResult = $this->showTemplate($payment, 'template');

	        if ($showTemplateResult->isSuccess())
	        {
		        $result->setTemplate($showTemplateResult->getTemplate());
	        }
	        else
            {
	            $result->addErrors($showTemplateResult->getErrors());
            }
        }
        else
        {
	        $result->addError(new Main\Error('gateway not found'));
        }

        return $result;
	}

	protected function getOrderData(Payment $payment): array
	{
		$result = [];

		$order = $payment->getOrder();

		if ($order === null) { return $result; }

		$deliveryPrice = $order->getDeliveryPrice();
		$basket = $order->getBasket();

		if ($basket === null) { return $result; }

		$result['id'] = (string)$order->getId();
		$result['total'] = number_format($payment->getSum(), 2, '.', '');

		/** @var \Bitrix\Sale\BasketItem $basketItem */
		foreach ($basket as $basketItem)
		{
			$result['items'][] = [
				'label'     => $basketItem->getField('NAME'),
				'amount'    => number_format($basketItem->getFinalPrice(), 2, '.', '')
			];
		}

		if ($deliveryPrice > 0)
		{
			$result['items'][] = [
				'label'     => 'delivery',
				'amount'    => number_format($deliveryPrice, 2, '.', '')
			];
		}

		return $result;
	}

	protected function getParamValue(Payment $payment, $code)
	{
		$prefix = $this->getPrefix();

		$code = $prefix . $code;

		return $this->getBusinessValue($payment, $code);
	}

	/**
	 * @inheritDoc
	 */
	public function getCurrencyList(): array
	{
		return ['RUB'];
	}

	public function refund(Payment $payment, $refundableSum): PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		try
		{
			$gatewayType = $this->getHandlerMode();

			$gatewayProvider = $this->getGateway($gatewayType);
			$gatewayProvider->setPayParams($this->getParamsBusValue($payment));

			$gatewayProvider->refund($payment, $refundableSum);

			$result->setOperationType(PaySystem\ServiceResult::MONEY_LEAVING);
		}
		catch (Main\SystemException $exception)
		{
			$result->addError(new Main\Error($exception->getMessage()));
		}

		return $result;
	}

	public function initPrePayment(Payment $payment = null, Request $request)
	{
		// TODO: Implement initPrePayment() method.
	}

	public function getProps()
	{
		// TODO: Implement getProps() method.
	}

	public function payOrder($orderData = array())
	{
		// TODO: Implement payOrder() method.
	}

	public function setOrderConfig($orderData = array())
	{
		// TODO: Implement setOrderConfig() method.
	}

	public function basketButtonAction($orderData)
	{
		// TODO: Implement basketButtonAction() method.
	}

	/**
	 * @param \Bitrix\Sale\Payment|null $payment
	 *
	 * @return bool
	 */
	protected function isTestMode(Payment $payment = null): bool
	{
		return ($this->getBusinessValue($payment, $this->getPrefix() . 'TEST_MODE') === 'Y');
	}

	protected function getGateway(string $type): GateWay\Base
	{
		if ($this->gateway === null)
		{
			$this->gateway = GateWay\Manager::getProvider($type);
		}

		return $this->gateway;
	}

	public function processRequest(Payment $payment, Request $request): ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		$gatewayType = $this->getHandlerMode();

		try
		{
			$gatewayProvider = $this->getGateway($gatewayType);

			$gatewayProvider->setPayParams($this->getParamsBusValue($payment));

			$resultData = $gatewayProvider->startPay($payment, $request);

			if (!empty($resultData))
			{
				$fields = [
					'PS_STATUS'         => 'Y',
					'PS_RESPONSE_DATE'  => new Main\Type\DateTime()
				] + $resultData;

				if (!$payment->isPaid())
				{
					$result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
					$result->setPsData($fields);
				}
			}

			$result->setData([
				'state'     => self::STEP_FINISHED,
				'success'   => true,
				'message'   => Main\Localization\Loc::getMessage('SUCCESS')
			]);
		}
		catch (Secure3dRedirect $exception)
		{
			$result->setData([
				'state'     => self::STEP_3DS,
				'redirect'  => $exception->getUrl(),
				'data'      => $exception->getData(),
				'key'       => $exception->getKey(),
			]);
		}
		catch (Main\SystemException $exception)
		{
			$result->addError(new Main\Error($exception->getMessage()));
		}

		return $result;
	}

	public function getPaymentIdFromRequest(Request $request)
	{
		$result = null;

		$externalId = $request->get('externalId');

		if (!empty($externalId)) { return $externalId; }

		$gatewayType = $this->getHandlerMode();

		if (empty($gatewayType)) { return null; }

		$gateway = $this->getGateway($gatewayType);

		$result = $gateway->getPaymentIdFromRequest($request);

		return $result;
	}

	/**
	 * @param Request $request
	 * @param int     $paySystemId
	 *
	 * @return bool
	 */
	public static function isMyResponse(Request $request, $paySystemId): bool
	{
		$result = false;

		self::readFromStream($request);

		$paySystemIdRequest = $request->get('paySystemId');

		if ((int)$paySystemIdRequest === (int)$paySystemId) { return true; }

		$paySystem = \Bitrix\Sale\PaySystem\Manager::getObjectById($paySystemId);

		if ($paySystem === null) { return $result; }

		$actionFile = $paySystem->getField('ACTION_FILE');

		[$className, $handlerType] = Manager::includeHandler($actionFile);

		/** @var $handler $this  */
		$handler = new $className($handlerType, $paySystem);

		if (!($handler instanceof self)) { return $result; }

		$params = $handler->getParamsBusValue();

		$gatewayType = $handler->getHandlerMode();

		if (empty($gatewayType)) { return $result; }

		$gateway = $handler->getGateway($gatewayType);

		$gateway->setPayParams($params);

		$result = $gateway->isMyResponse($request, $paySystemId);

		return $result;
	}

	public function sendResponse(ServiceResult $result, Request $request): void
	{
		$errors = $result->getErrorMessages();

		$response = $result->getData();

		if (!empty($errors))
		{
			$response = [
				'state'     => self::STEP_ERRORS,
				'success'   => false,
				'errors'    => $errors
			];
		}

		echo Main\Web\Json::encode($response);
	}

	/**
	 * @return array
	 */
	public static function getHandlerModeList(): array
	{
		return GateWay\Manager::getHandlerModeList();
	}

	protected function getHandlerMode(): string
	{
		return $this->service->getField('PS_MODE');
	}

	public function isNewWindow(): bool
	{
		return $this->service->getField('NEW_WINDOW') === 'Y';
	}

	protected static function readFromStream(Request $request): void
	{
		$values = Main\Web\Json::decode(file_get_contents("php://input"));

		if (!empty($values)) { $request->setValues($values); }
	}
}