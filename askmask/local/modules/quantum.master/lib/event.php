<?

namespace Quantum;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Sale\DiscountCouponsManager;
Loader::includeModule('sale');

class Event
{
	public static function onPageStart()
	{
		self::setupEventHandlers();
	}

	/**
	 * Добавляет обработчики событий
	 *
	 * @return void
	 */
	protected static function setupEventHandlers()
	{
		$eventManager = Main\EventManager::getInstance();
        $eventManager->addEventHandler("main", "OnBeforeProlog", ["Quantum\\Handler\\Event", "OnBeforePrologHandler"]);
        $eventManager->addEventHandler("main", "OnAfterUserAuthorize", ["Quantum\\Handler\\Event", "OnAfterUserAuthorizeHandler"]);
        $eventManager->addEventHandler("main", "OnAfterUserLogout", ["Quantum\\Handler\\Event", "OnAfterUserLogoutHandler"]);
        $eventManager->addEventHandler("sale", "OnSaleComponentOrderCreated", ["Quantum\\Handler\\Event", "OnSaleComponentOrderCreatedHandler"]);
        $eventManager->addEventHandler("sale", "OnSaleComponentOrderOneStepDelivery", ["Quantum\\Handler\\Event", "OnSaleComponentOrderOneStepDeliveryHandler"]);
    }
}
