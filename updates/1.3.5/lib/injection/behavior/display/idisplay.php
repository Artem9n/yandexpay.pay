<?php
namespace YandexPay\Pay\Injection\Behavior\Display;

interface IDisplay
{
	public const OWN_TYPE = 'OWN';

	public function getFields() : array;

	public function getTitle() : string;

	public function getType() : string;

	public function getWidgetParameters() : array;

	public function setValues(array $values) : void;

	public function getValues() : array;
}