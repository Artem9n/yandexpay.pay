<?php
namespace YandexPay\Pay\Injection\Solution;

abstract class Skeleton
{
	abstract public function getTitle() : string;

	abstract public function getType() : string;

	public function isMatch(array $context = []) : bool
	{
		if (!isset($context['TEMPLATES'])) { return  false; }

		$result = false;

		foreach ($context['TEMPLATES'] as $template)
		{
			if (mb_strpos($template, $this->getType()) === false) { continue; }

			$result = true;
		}

		return $result;
	}

	abstract public function getDefaults(array $context = []) : array;
}