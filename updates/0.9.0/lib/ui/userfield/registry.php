<?php
namespace YandexPay\Pay\Ui\UserField;

class Registry
{
	public static function getUserType(string $type) : array
	{
		global $USER_FIELD_MANAGER;

		$localTypeClassName = static::getLocalTypeClassName($type);

		if (!class_exists($localTypeClassName))
		{
			$result = $USER_FIELD_MANAGER->GetUserType($type);
		}
		else if (method_exists($localTypeClassName, 'GetUserTypeDescription'))
		{
			$result = $localTypeClassName::GetUserTypeDescription();
			$result['CLASS_NAME'] = $localTypeClassName;
		}
		else
		{
			$result = [
				'CLASS_NAME' => $localTypeClassName,
			];
		}

		return $result;
	}

	protected static function getLocalTypeClassName(string $type) : string
	{
		return __NAMESPACE__ . '\\' . ucfirst($type) . 'Type';
	}
}