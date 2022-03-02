<?php
namespace YandexPay\Pay\Injection\Behavior;

interface BehaviorInterface
{
	public function getTitle() : string;

	public function getFields() : array;

	public function setValues(array $values) : void;

	public function getMode() : string;

	public function getSelector() : string;

	public function install(int $injectionId) : void;

	public function uninstall(int $injectionId) : void;
}