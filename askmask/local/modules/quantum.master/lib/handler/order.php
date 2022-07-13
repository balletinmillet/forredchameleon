<?php

namespace Quantum\Handler;

use \Bitrix\Main;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
Loader::includeModule('sale');

class Order
{
    public static function getOrderBasketSum($order)
    {
        $orderSum = 0;
        $basket = $order->getBasket();
        foreach ($basket as $basketItem)
            $orderSum += $basketItem->getField('PRICE') * $basketItem->getQuantity();

        return $orderSum;
    }

    public static function setDeliveryPrice($order, $price = 0)
    {
        $shipmentCollection = $order->getShipmentCollection();
        foreach($shipmentCollection as $shipment) {
            if(!$shipment->isSystem())
                $shipment->setBasePriceDelivery($price, false);
        }
    }
}