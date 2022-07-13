<?php
namespace Eterna;

/**
 * Базовый каталог модуля
 */
const BASE_DIR = __DIR__;
/**
 * Имя модуля
 */
const MODULE_ID = 'eterna.master';

if(stristr(dirname(__FILE__),"/local/") || stristr(dirname(__FILE__),"\\local\\")){
    $mainDir = "/local";
}
else {
    $mainDir = "/bitrix";
}

IncludeModuleLangFile(__FILE__);

$arClassBase = [
    '\Eterna\Import\Catalog' => 'lib/import/catalog.php',
    '\Eterna\Handler\Catalog' => 'lib/handler/catalog.php',
    '\Eterna\Handler\Order' => 'lib/handler/order.php',
    '\Eterna\Handler\Basket' => 'lib/handler/basket.php',
    '\Eterna\Handler\Discount' => 'lib/handler/discount.php',
    '\Eterna\Handler\User' => 'lib/handler/user.php',
    '\Eterna\Handler\DB' => 'lib/handler/db.php',
    '\Eterna\Handler\File' => 'lib/handler/file.php',
    '\Eterna\Handler\Event' => 'lib/handler/event.php',
    '\Eterna\Handler\Content' => 'lib/handler/content.php',
    '\Eterna\Configurator\User' => 'lib/configurator/user.php',
    '\Eterna\Integration\Lamoda\EventHandler' => 'lib/integration/lamoda/eventhandler.php',
    '\Eterna\Integration\Lamoda\Sender' => 'lib/integration/lamoda/sender.php',
    '\Eterna\Integration\Lamoda\Delivery' => 'lib/integration/lamoda/delivery.php',
    '\Eterna\Integration\Lamoda\Order' => 'lib/integration/lamoda/order.php',
    '\Eterna\Integration\Lamoda\Shipment' => 'lib/integration/lamoda/shipment.php',
    '\Eterna\Integration\Lamoda\Good' => 'lib/integration/lamoda/good.php',
    '\Eterna\Integration\Lamoda\Helper' => 'lib/integration/lamoda/helper.php',
    'Eterna\Texel\SizeChart' => 'lib/integration/texel/sizechart.php',
];

/** MindBox */
$arClassMindbox = [
    '\Eterna\Integration\MindBox\EventHandler' => 'lib/integration/mindbox/eventhandler.php',
    '\Eterna\Integration\MindBox\Auth' => 'lib/integration/mindbox/auth.php',
    '\Eterna\Integration\MindBox\User' => 'lib/integration/mindbox/user.php',
    '\Eterna\Integration\MindBox\Product' => 'lib/integration/mindbox/product.php',
    '\Eterna\Integration\MindBox\Order' => 'lib/integration/mindbox/order.php',
    '\Eterna\Integration\MindBox\Api' => 'lib/integration/mindbox/api/api.php',
    '\Eterna\Integration\MindBox\Loyalty\User' => 'lib/integration/mindbox/loyalty/user.php',
    '\Eterna\Integration\MindBox\Loyalty\Order' => 'lib/integration/mindbox/loyalty/order.php',
    '\Eterna\Integration\MindBox\Loyalty\EventHandler' => 'lib/integration/mindbox/loyalty/eventhandler.php',
    '\Eterna\Integration\MindBox\Loyalty\BasketContainer' => 'lib/integration/mindbox/loyalty/basketcontainer.php',
    '\Mindbox\ExtensionCartRulesActions' => '/lib/integration/mindbox/extensionCartRulesActions.php'
    ,
];
/** MindBox End*/

$arClassRoot = [
    '\Eterna\Tools' => 'lib/tools.php',
    '\Eterna\Settings' => 'lib/settings.php',
    '\Eterna\Event' => 'lib/event.php',
];

\Bitrix\Main\Loader::registerAutoLoadClasses(
	'eterna.master',
	array_merge($arClassBase, $arClassRoot, $arClassMindbox)
);