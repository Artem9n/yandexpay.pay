<?php

namespace YandexPay\Pay\Reference\Storage;

interface HasView
{
	public static function getView() : View;
}