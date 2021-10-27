<?php
namespace YandexPay\Pay\Result;

use Bitrix\Main;

class Facade
{
	/**
	 * @param Main\Result[] $results
	 *
	 * @return Main\Result
	 */
	public static function merge(...$results) : Main\Result
	{
		$target = array_shift($results);
		$targetSupportWarnings = method_exists($target, 'getWarnings');

		if ($target === null)
		{
			throw new Main\ArgumentException('cant merge empty results');
		}

		foreach ($results as $result)
		{
			// errors

			$errors = $result->getErrors();

			if (!empty($errors))
			{
				$target->addErrors($errors);
			}

			// warnings

			if ($targetSupportWarnings && method_exists($result, 'getWarnings'))
			{
				$target->addWarnings($result->getWarnings());
			}

			// data

			$data = $result->getData();

			if (!empty($data))
			{
				$targetData = (array)$target->getData();
				/** @noinspection AdditionOperationOnArraysInspection */
				$target->setData($targetData + $data);
			}
		}

		return $target;
	}
}