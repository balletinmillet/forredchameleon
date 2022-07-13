<?php

namespace Eterna\Handler;

use \Bitrix\Main;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Sale\Order as SaleOrder;

Loader::includeModule("sale");
Loader::includeModule("catalog");

class Order
{
    protected static $orderDeliveryAddressPropertyCodes = ["CITY", "STREET", "BUILDING", "HOUSING", "APARTMENT"];
    protected  static $statuses  = [
        "New to be confirmed" => "NC",
        "Confirmed" => "CF",
        "Ready for shipment" => "AW",
        "Shipped" => "DI",
        "Given to delivery" => "GD",
        "On shelf" => "OS",
        "Arrived to LME" => "AL",
        "Left LME" => "SL",
        "In Delivery" => "ID",
        "Delivered" => "DL",
        "Not delivered" => "ND",
        "Canceled" => "Cl",
        "Returned" => "RJ",
    ];

    public static function getPropertyByCode($propertyCollection, $code)
    {
        foreach ($propertyCollection as $property)
        {
            if($property->getField('CODE') == $code)
                return $property;
        }
    }

    public static function getPropertyByCodes($propertyCollection, $codes)
    {
        $properties = [];
        foreach ($codes as $index => $code)
        {
            $property = self::getPropertyByCode($propertyCollection, $code);
            if(!empty($property))
                $properties[$code] = $property->getValue();
        }
        
        return $properties;
    }

    public static function getOrderDeliveryAddress($order)
    {
        $orderDeliveryAddress = "";
        $propertyCollection = $order->getPropertyCollection();
        $properties = self::getPropertyByCodes($propertyCollection, self::$orderDeliveryAddressPropertyCodes);

        foreach ($properties as $propertyCode => $propertyValue)
        {
            if($propertyCode == "CITY")
                $orderDeliveryAddress .= $propertyValue;
            elseif(!empty($propertyValue))
                $orderDeliveryAddress .= " " . $propertyValue;
        }

        return $orderDeliveryAddress;
    }

    public static function setOrderStatus($orderId, $orderStatus)
    {
        if(empty($orderId) || empty($orderStatus))
            return 'empty params';

        $order = SaleOrder::load($orderId);

        if(empty($order))
            return 'order with id ' . $orderId . ' not found';

        if($order->getId() > 0)
        {
            $order->setField('STATUS_ID', self::$statuses[$orderStatus]);
            $order->save();
            return true;
        }

        return 'order with id ' . $orderId . ' not found';

    }

    public static function updateOrderProperty($orderId, $propertyCode, $propertyValue)
    {
        $order = SaleOrder::load($orderId);
        $propertyCollection = $order->getPropertyCollection();
        $property = self::getPropertyByCode($propertyCollection, $propertyCode);
        $property->setValue($propertyValue);
        $order->save();
    }


    public static function getList($userIds = [], $selectFields = ["ID"], $onlyOrdersIds = true): array
    {
        global $USER;
        if($userIds == null)
            $userIds = [$USER->GetID()];

        $orders = [];
        $CSaleOrder = \CSaleOrder::GetList(array("DATE_INSERT" => "ASC"), ["USER_ID" => $userIds], false, false, $selectFields);

        if(count($userIds) > 1)
        {
            while ($order = $CSaleOrder->Fetch())
                $orders[$order["USER_ID"]][] = $order;
        }
        else
        {
            if($onlyOrdersIds)
            {
                while ($order = $CSaleOrder->Fetch())
                    $orders[] = $order["ID"];
            }
            else
            {
                while ($order = $CSaleOrder->Fetch())
                    $orders[] = $order;
            }
        }

        return $orders;
    }

    public static function getOrdersProperties($orderIds, $propertyCodes): array
    {
        $ordersProperties = [];
        $dbOrderProps = \CSaleOrderPropsValue::GetList(array("SORT" => "ASC"), array("ORDER_ID" => $orderIds, "CODE" => $propertyCodes));
        while ($arOrderProps = $dbOrderProps->GetNext())
            $ordersProperties[$arOrderProps["ORDER_ID"]][$arOrderProps["CODE"]] = $arOrderProps["VALUE"];

        return $ordersProperties;
    }

    public static function getOrdersProperty($orderIds, $propertyCode): array
    {
        $ordersProperty = [];
        $ordersProperties = self::getOrdersProperties($orderIds, [$propertyCode]);
        foreach ($ordersProperties as $orderId => $item)
            $ordersProperty[$orderId] = $item[$propertyCode];

        return $ordersProperty;
    }

}
