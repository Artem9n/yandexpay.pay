<?php
namespace YandexPay\Pay\Injection\Behavior;

interface BehaviorInterface
{
	public function getTitle() : string;

	public function getFields() : array;

	public function install(int $injectionId, array $settings);

	public function uninstall(int $injectionId, array $settings);
}