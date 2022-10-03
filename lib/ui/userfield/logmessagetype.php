<?php
namespace YandexPay\Pay\Ui\UserField;

use Bitrix\Main;
use YandexPay\Pay;
use YandexPay\Pay\Reference\Concerns;

class LogMessageType extends EnumerationType
{
	use Concerns\HasMessage;

	public const FORMAT_DEBUG = 'debug';
	public const FORMAT_TEXT = 'text';

	protected static $debugCounter = 0;
	protected static $debugBase;

	public static function getAdminListViewHtml($arUserField, $arHtmlControl) : string
	{
		$value = Helper\Value::asSingle($arUserField, $arHtmlControl);

		if ($value !== null && !is_scalar($value)) { $value = print_r($value, true); }

		$value = (string)$value;

		return $value !== '' ? static::renderMessage($value) : '&nbsp;';
	}

	protected static function renderMessage($message) : string
	{
		$type = static::getMessageType($message);

		if ($type === static::FORMAT_DEBUG)
		{
			$result = static::renderDebugMessage($message);
		}
		else
		{
			$result = nl2br($message);
		}

		return $result;
	}

	protected static function getMessageType(string $message) : string
	{
		if (static::isDebugMessage($message))
		{
			$result = static::FORMAT_DEBUG;
		}
		else
		{
			$result = static::FORMAT_TEXT;
		}

		return $result;
	}

	protected static function renderDebugMessage(string $message) : string
	{
		$counter = ++static::$debugCounter;
		$contentsId = 'yapayLogMessageDebugContents' . static::getDebugBase() . $counter;
		$data = Main\Web\Json::decode($message);

		$result = sprintf('<a href="#" onclick="(new BX.CAdminDialog({ content: BX(\'%s\'), width: 800, height: 700 })).Show(); return false;">', $contentsId);
		$result .= static::getDebugPreview($data);
		$result .= '</a>';
		$result .= '<div hidden style="display: none;">';
		$result .= sprintf('<div id="%s">', $contentsId);
		$result .= '<pre style="border: 1px solid #CCC;
			    margin: 10px 0;
			    padding: 10px;
			    font-family: monospace;
			    background-color: #FEFEFA;
			    white-space: pre !important;
			    overflow: auto;">'
			. Pay\Utils\Encoding::revert(Main\Web\Json::encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))
			. '</pre>';
		$result .= '</div>';
		$result .= '</div>';

		return $result;
	}

	protected static function isDebugMessage(string $message) : bool
	{
		$result = true;

		try
		{
			Main\Web\Json::decode($message);
		}
		catch (Main\ArgumentException $exception)
		{
			$result = false;
		}

		return $result;
	}

	protected static function getDebugBase()
	{
		if (static::$debugBase === null)
		{
			static::$debugBase = randString(5);
		}

		return static::$debugBase;
	}

	protected static function mapPreviewCodes() : array
	{
		return [
			'mode',
			'orderId',
			'shippingContact',
			'shippingAddress',
			'data',
			'boundingBox',
			'pickupPointId',
			'cart',
			'reasonCode',
			'event',
			'refundAmount',
		];
	}

	protected static function getDebugPreview(array $data) : string
	{
		$result = '';
		$map = static::mapPreviewCodes();

		foreach ($map as $value)
		{
			if (isset($data[$value]))
			{
				if ($value === 'reasonCode')
				{
					$result = $data[$value];
				}
				else if ($value === 'event')
				{
					$result = sprintf('webhook: %s', $data[$value]);
				}
				else
				{
					$status = isset($data['status']) ? sprintf(' status: %s', $data['status']) : '';
					$result = sprintf('[%s]%s', $value, $status);
				}
				break;
			}
		}

		return $result;
	}
}