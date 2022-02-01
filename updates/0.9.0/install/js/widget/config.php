<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

return [
	'js' => './build/widget.js',
	'css' => './widget.css',
	'rel' => [
		'main.core.polyfill',
		'yandexpay.sdk',
	],
];