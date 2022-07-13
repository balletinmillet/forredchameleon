<?

namespace Eterna;

use Bitrix\Main;
use Bitrix\Main\Application;

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

        $eventManager->addEventHandler(
            "sale", "OnSaleBasketItemRefreshData", ["Eterna\\Handler\\Event", "basketItemRefreshDataHandler"]
        );
        $eventManager->addEventHandler(
            "sale", "OnSaleOrderSaved", ["Eterna\\Handler\\Event", "OnSaleOrderSavedHandler"]
        );

        if(strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome-Lighthouse'))
        {
            /** Content optimization */
            $eventManager->addEventHandler("main", "OnEndBufferContent", ["Eterna\\Handler\\Content", "deleteKernelJs"]);
            $eventManager->addEventHandler("main", "OnEndBufferContent", ["Eterna\\Handler\\Content", "deleteKernelCss"]);
            $eventManager->addEventHandler("main", "OnEndBufferContent", ["Eterna\\Handler\\Content", "changeMyContent"]);
        }
		/** Lamoda*/
        $eventManager->addEventHandler(
        	"sale", "onSaleDeliveryServiceCalculate", ["Eterna\\Integration\\Lamoda\\EventHandler", "onSaleDeliveryServiceCalculateHandler"]
        );
        $eventManager->addEventHandler(
        	"sale", "OnSaleOrderSaved", ["Eterna\\Integration\\Lamoda\\EventHandler", "OnSaleOrderSavedHandler"]
        );
        $eventManager->addEventHandler(
        	"sale", "OnBeforeBasketAdd", ["Eterna\\Integration\\Lamoda\\EventHandler", "OnBeforeBasketAddHandler"]
        );
        $eventManager->addEventHandler(
        	"sale", "OnSaleOrderBeforeSaved", ["Eterna\\Integration\\Lamoda\\EventHandler", "OnSaleOrderBeforeSavedHandler"]
        );

        /** MindBox*/
        //Auth
        $eventManager->addEventHandler(
        	"main", "OnBeforeEventSend", ["Eterna\\Integration\\MindBox\\EventHandler", "OnBeforeEventSendHandler"]
        );
        $eventManager->addEventHandler(
        	"main", "OnBeforeUserRegister", ["Eterna\\Integration\\MindBox\\EventHandler", "OnBeforeUserRegisterHandler"]
        );
        $eventManager->addEventHandler(
        	"main", "OnAfterUserRegister", ["Eterna\\Integration\\MindBox\\EventHandler", "OnAfterUserRegisterHandler"]
        );
        $eventManager->addEventHandler(
        	"main", "OnAfterUserLogin", ["Eterna\\Integration\\MindBox\\EventHandler", "OnAfterUserLoginHandler"]
        );

        $eventManager->addEventHandler(
            "main", "OnAfterUserAuthorize", ["Eterna\\Integration\\MindBox\\EventHandler", "OnAfterUserAuthorizeHandler"]
        );


        //User
        $eventManager->addEventHandler(
            "main", "OnAfterUserUpdate", ["Eterna\\Integration\\MindBox\\EventHandler", "OnAfterUserUpdateHandler"]
        );
        $eventManager->addEventHandler(
            "main", "OnBeforeUserUpdate", ["Eterna\\Integration\\MindBox\\EventHandler", "OnBeforeUserUpdateHandler"]
        );
        $eventManager->addEventHandler(
            "main", "OnAfterUserRegister", ["Eterna\\Handler\\Event", "OnAfterUserRegisterHandler"]
        );

        //Product 
        $eventManager->addEventHandler(
            "sale", "OnSaleBasketSaved", ["Eterna\\Integration\\MindBox\\EventHandler", "OnSaleBasketSavedHandler"]
        );
        //Order
        $eventManager->addEventHandler(
            "sale", "OnSaleOrderSaved", ["Eterna\\Integration\\MindBox\\EventHandler", "OnSaleOrderSavedHandler"]
        );
        $eventManager->addEventHandler(
            "sale", "OnSaleStatusOrderChange", ["Eterna\\Integration\\MindBox\\EventHandler", "OnSaleStatusOrderChangeHandler"]
        );

        /** MindBox Loyalty*/
        //Basket prices
        $eventManager->addEventHandler(
            "catalog", "OnGetOptimalPrice", ["Eterna\\Integration\\MindBox\\Loyalty\\EventHandler", "OnGetOptimalPriceHandler"]
        );

        $eventManager->addEventHandler(
            "sale", "OnSaleOrderSaved", ["Eterna\\Integration\\MindBox\\Loyalty\\EventHandler", "OnSaleOrderSavedHandler"]
        );

    }
}
