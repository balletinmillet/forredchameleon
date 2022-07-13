<?php
namespace Quantum;

/**
 * Базовый каталог модуля
 */
const BASE_DIR = __DIR__;
/**
 * Имя модуля
 */
const MODULE_ID = 'quantum.master';

if(stristr(dirname(__FILE__),"/local/") || stristr(dirname(__FILE__),"\\local\\")){
    $mainDir = "/local";
}
else {
    $mainDir = "/bitrix";
}

IncludeModuleLangFile(__FILE__);

$arClassBase = [
    '\Quantum\Handler\Catalog' => 'lib/handler/catalog.php',
    '\Quantum\Handler\Event' => 'lib/handler/event.php',
    '\Quantum\Handler\Basket' => 'lib/handler/basket.php',
];

$arClassRoot = [
    '\Quantum\Tools' => 'lib/tools.php',
    '\Quantum\Settings' => 'lib/settings.php',
    '\Quantum\Event' => 'lib/event.php',
];

\Bitrix\Main\Loader::registerAutoLoadClasses(
	'quantum.master',
	array_merge($arClassBase, $arClassRoot)
);