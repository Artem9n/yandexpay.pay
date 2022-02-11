<?php
namespace YandexPay\Pay\Injection\Solution;

use YandexPay\Pay\Reference\Assert;

class Utils
{
	public static function matchTemplates(string $name, array $context) : bool
	{
		if (!isset($context['TEMPLATES'])) { return  false; }

		$result = false;

		foreach ($context['TEMPLATES'] as $template)
		{
			if (mb_strpos($template, $name) !== 0) { continue; }

			$result = true;
			break;
		}

		return $result;
	}
}