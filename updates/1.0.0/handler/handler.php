<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Request;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\PaySystem\ServiceResult;
use YandexPay\Pay\Config;
use YandexPay\Pay\Exceptions\Secure3dRedirect;
use YandexPay\Pay\Gateway;
use YandexPay\Pay\Reference\Assert;
use YandexPay\Pay\Utils\Url;

Loader::includeModule('yandexpay.pay');

class YandexPayHandler extends PaySystem\ServiceHandler implements PaySystem\IRefund
{
	public const REQUEST_SIGN = 'yandexpay';

	protected const STEP_3DS = '3ds';
	protected const STEP_FINISHED = 'finished';
	protected const STEP_FAILURE = 'errors';

	protected const YANDEX_TEST_MODE = 'SANDBOX';
	protected const YANDEX_PRODUCTION_MODE = 'PRODUCTION';

	/** @var Gateway\Base|null */
	protected $gateway;

	/**
	 * @inheritDoc
	 */
	public function initiatePay(Payment $payment, Request $request = null) : PaySystem\ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		try
		{
	        $this->setExtraParams($this->getParams($payment));

	        $showTemplateResult = $this->showTemplate($payment, 'template');

	        if ($showTemplateResult->isSuccess())
	        {
		        $result->setTemplate($showTemplateResult->getTemplate());
				$this->setRedirectUrl($payment);
	        }
	        else
            {
	            $result->addErrors($showTemplateResult->getErrors());
            }
        }
        catch (Main\SystemException $exception)
        {
	        $result->addError(new Main\Error($exception->getMessage()));
        }

        return $result;
	}

	protected function setRedirectUrl(Payment $payment) : void
	{
		global $APPLICATION;

		unset($_SESSION['yabackurl']);
		$_SESSION['yabackurl'] = Url::absolutizePath() . $APPLICATION->GetCurPageParam();
	}

	protected function getParams(Payment $payment) : array
	{
		$gateway = $this->wakeUpGateway($payment);

		return [
			'requestSign'           => static::REQUEST_SIGN,
			'order'                 => $this->getOrderData($payment),
			'env'                   => $this->isTestMode($payment) ? self::YANDEX_TEST_MODE : self::YANDEX_PRODUCTION_MODE,
			'merchantId'            => $this->getParamValue($payment, 'MERCHANT_ID'),
			'merchantName'          => $this->getParamValue($payment, 'MERCHANT_NAME'),
			'buttonTheme'           => $this->getParamValue($payment, 'VARIANT_BUTTON'),
			'buttonWidth'           => $this->getParamValue($payment, 'WIDTH_BUTTON'),
			'gateway'               => $gateway->getGatewayId(),
			'gatewayMerchantId'     => $gateway->getMerchantId(),
			'externalId'            => $payment->getId(),
			'paySystemId'           => $this->service->getField('ID'),
			'currency'              => $payment->getField('CURRENCY'),
			'notifyUrl'             => $this->getParamValue($payment, 'NOTIFY_URL'),
		];
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
			if ($basketItem->getFinalPrice() <= 0) { continue; }

			$result['items'][] = [
				'label'     => $basketItem->getField('NAME'),
				'amount'    => number_format($basketItem->getFinalPrice(), 2, '.', '')
			];
		}

		if ($deliveryPrice > 0)
		{
			$result['items'][] = [
				'label'     => Main\Localization\Loc::getMessage('ORDER_DELIVERY'),
				'amount'    => number_format($deliveryPrice, 2, '.', '')
			];
		}

		return $result;
	}

	protected function getParamValue(Payment $payment, $code)
	{
		$code = Config::getLangPrefix() . $code;

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
			$gateway = $this->wakeUpGateway($payment);
			$gateway->refund();

			$result->setOperationType(PaySystem\ServiceResult::MONEY_LEAVING);
		}
		catch (Main\SystemException $exception)
		{
			$result->addError(new Main\Error($exception->getMessage()));
		}

		return $result;
	}

	/**
	 * @param \Bitrix\Sale\Payment|null $payment
	 *
	 * @return bool
	 */
	public function isTestMode(Payment $payment = null): bool
	{
		return ($this->getBusinessValue($payment, Config::getLangPrefix() . 'TEST_MODE') === 'Y');
	}

	public function getGateway(): Gateway\Base
	{
		$type = $this->getHandlerMode();

		Assert::notNull($type, 'gatewayType');

		return Gateway\Manager::getProvider($type);
	}

	public function wakeUpGateway(Payment $payment) : Gateway\Base
	{
		$gateway = $this->getGateway();
		$params = $this->getParamsBusValue($payment);

		$gateway->setParameters($params);
		$gateway->setPayment($payment);

		return $gateway;
	}

	public function processRequest(Payment $payment, Request $request): ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		try
		{
			$gateway = $this->wakeUpGateway($payment);
			$resultData = $gateway->startPay();

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
				'message'   => Main\Localization\Loc::getMessage('SUCCESS'),
			]);
		}
		catch (Secure3dRedirect $exception)
		{
			$result->setData([
				'state'     => self::STEP_3DS,
				'success'   => true,
				'action'    => $exception->getUrl(),
				'params'    => $exception->getParams(),
				'method'    => $exception->getMethod(),
				'termUrl'   => $exception->getTermUrl(),
				'view'      => $exception->getView()
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
		$externalId = $request->get('externalId');

		if (!empty($externalId)) { return $externalId; }

		$gatewayType = $this->getHandlerMode();

		if (empty($gatewayType)) { return null; }

		$gateway = $this->getGateway();

		$gateway->setParameters($this->getParamsBusValue());

		return $gateway->getPaymentIdFromRequest();
	}

	public function sendResponse(ServiceResult $result, Request $request): void
	{
		$errors = $result->getErrorMessages();
		$response = $result->getData();

		if (!empty($errors))
		{
			$response = [
				'state'     => self::STEP_FAILURE,
				'success'   => false,
				'message'    => $errors
			];
		}

		if ($request->get('accept') === 'json')
		{
			echo Main\Web\Json::encode($response);
		}
		else
		{
			$this->renderResponseHtml($response, $request);
		}
	}

	protected function renderResponseHtml(array $data, Request $request): void
	{
		if ($request->get('backurl') !== null)
		{
			LocalRedirect($request->get('backurl'), true);
			die();
		}

		ShowMessage($data['message']);
	}

	/**
	 * @return array
	 */
	public static function getHandlerModeList(): array
	{
		return Gateway\Manager::getHandlerModeList();
	}

	public function getHandlerMode(): ?string
	{
		return $this->service->getField('PS_MODE');
	}

	public function isNewWindow(): bool
	{
		return $this->service->getField('NEW_WINDOW') === 'Y';
	}
}