<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Request;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\PaySystem\ServiceResult;
use YandexPay\Pay\Exceptions\Secure3dRedirect;
use YandexPay\Pay\Gateway;
use YandexPay\Pay\Reference\Assert;

Loader::includeModule('yandexpay.pay');

class YandexPayHandler extends PaySystem\ServiceHandler implements PaySystem\IRefund, PaySystem\IPrePayable, Main\Type\IRequestFilter
{
	public const REQUEST_SIGN = 'yandexpay';

	protected const STEP_3DS = '3ds';
	protected const STEP_FINISHED = 'finished';
	protected const STEP_FAILURE = 'errors';

	protected const YANDEX_TEST_MODE = 'SANDBOX';
	protected const YANDEX_PRODUCTION_MODE = 'PRODUCTION';

	/** @var Gateway\Base|null */
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
		global $APPLICATION;

		$result = new PaySystem\ServiceResult();

		$gatewayType = $this->getHandlerMode();
		$gatewayMerchantId = $this->getParamValue($payment, $gatewayType. '_PAYMENT_GATEWAY_MERCHANT_ID');

		$params = [
			'requestSign'           => static::REQUEST_SIGN,
			'order'                 => $this->getOrderData($payment),
			'env'                   => $this->isTestMode($payment) ? self::YANDEX_TEST_MODE : self::YANDEX_PRODUCTION_MODE,
			'merchantId'            => $this->getParamValue($payment, 'MERCHANT_ID'),
			'merchantName'          => $this->getParamValue($payment, 'MERCHANT_NAME'),
			'buttonTheme'           => $this->getParamValue($payment, 'VARIANT_BUTTON'),
			'buttonWidth'           => $this->getParamValue($payment, 'WIDTH_BUTTON'),
			'cardNetworks'          => $this->getCardNetworks($payment),
			'gateway'               => mb_strtolower($gatewayType),
			'gatewayMerchantId'     => $gatewayMerchantId,
			'externalId'            => $payment->getId(),
			'paySystemId'           => $this->service->getField('ID'),
			'currency'              => $payment->getField('CURRENCY')
		];

		try
		{
	        $this->setExtraParams($params);

	        $showTemplateResult = $this->showTemplate($payment, 'template');

	        if ($showTemplateResult->isSuccess())
	        {
		        $result->setTemplate($showTemplateResult->getTemplate());

				$server = Main\Context::getCurrent()->getServer();
		        $request = Main\Context::getCurrent()->getRequest();
		        $host = $request->isHttps() ? 'https' : 'http';
				$url = $host . '://' . $server->get('SERVER_NAME') . $APPLICATION->GetCurPage() . '?ORDER_ID=' . $payment->getOrderId();
		        $_SESSION['yabackurl'] = $url;
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

	protected function getCardNetworks(Payment $payment) : array
	{
		$result = [];

		$parameters = $this->getParamsBusValue($payment);
		$str = 'YANDEX_CARD_NETWORK_';
		$strLength = mb_strlen($str);

		foreach ($parameters as $code => $value)
		{
			$position = mb_strpos($code, $str);

			if ($position !== false && $value === 'Y')
			{
				$cardName = mb_substr($code, $strLength);
				$result[] = $cardName;
			}
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
			$gatewayProvider = $this->getGateway($payment);
			$gatewayProvider->setParameters($this->getParamsBusValue($payment));

			$gatewayProvider->refund();

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

	protected function getGateway(Payment $payment = null, Request $request = null): Gateway\Base
	{
		$type = $this->getHandlerMode();

		Assert::notNull($type, 'gatewayType');

		return Gateway\Manager::getProvider($type, $payment, $request);
	}

	public function processRequest(Payment $payment, Request $request): ServiceResult
	{
		$result = new PaySystem\ServiceResult();

		try
		{
			$gatewayProvider = $this->getGateway($payment, $request);

			$gatewayProvider->setParameters($this->getParamsBusValue($payment));

			$resultData = $gatewayProvider->startPay();

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
		$this->readFromStream($request);

		$externalId = $request->get('externalId');

		if (!empty($externalId)) { return $externalId; }

		$gatewayType = $this->getHandlerMode();

		if (empty($gatewayType)) { return null; }

		$gateway = $this->getGateway(null, $request);

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
			LocalRedirect($request->get('backurl'));
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

	protected function readFromStream(Request $request): void
	{
		$request->addFilter($this);
	}

	public function filter(array $values): array
	{
		try
		{
			$rawInput = file_get_contents('php://input');
			$postData = Main\Web\Json::decode($rawInput);

			$result = [
				'post' => $postData,
			];
		}
		catch (\Exception $exception)
		{
			$result = [];
		}

		return $result;
	}
}