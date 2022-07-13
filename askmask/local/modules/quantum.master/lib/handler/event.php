<?php

namespace Quantum\Handler;

use \Bitrix\Main;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;

/**
 * \Quantum\Tools::addTolLog($object, "file_name"); - log
 *
 */
class Event
{
    public function OnSaleComponentOrderCreatedHandler($order, &$arUserResult, $request, &$arParams, &$arResult,  &$arPaySystemServiceAll, &$arDeliveryServiceAll)
    {
        $propertyCollection = $order->getPropertyCollection();
        $deliveryLocationProperty = $propertyCollection->getDeliveryLocation();
        $deliveryLocationValue = $deliveryLocationProperty->getValue();

        $orderSum = Order::getOrderBasketSum($order);
        $condition = Discount::getConditions("LOCATION_FREE_DELIVERY");

        $arResult["CUSTOM_FIELDS"]["FREE_DELIVERY"] = "N";
        if($orderSum > $condition["MSK_FREE_DELIVERY"]["ORDER_SUM=<"] && $deliveryLocationValue == $condition["MSK_FREE_DELIVERY"]["ZIP"])
        {
            Order::setDeliveryPrice($order);
            $arResult["CUSTOM_FIELDS"]["FREE_DELIVERY"] = "Y";
        }
        elseif($orderSum > $condition["EXCEPT_MSK_FREE_DELIVERY"]["ORDER_SUM=<"])
        {
            Order::setDeliveryPrice($order);
            $arResult["CUSTOM_FIELDS"]["FREE_DELIVERY"] = "Y";
        }
    }

    public function OnSaleComponentOrderOneStepDeliveryHandler(&$arResult, &$arUserResult, $arParams)
    {
        if($arResult["CUSTOM_FIELDS"]["FREE_DELIVERY"] == "Y")
        {
            foreach ($arResult["DELIVERY"] as $index => $item)
            {
                $arResult["DELIVERY"][$index]["PRICE"] = '0';
                $arResult["DELIVERY"][$index]["PRICE_FORMATED"] = '0 руб.';
            }
        }
    }
}