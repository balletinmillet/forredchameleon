<?php

namespace Eterna\Integration\Lamoda;

use \Bitrix\Main;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;


class Delivery
{
    public static function getCities($cityName)
    {
        return Sender::sendRequest('api/v1/addresses/city?name=' . $cityName);
    }

    public static function getStreets($cityId, $streetName)
    {
        return Sender::sendRequest('api/v1/addresses/street?parentId=' . $cityId . "&name=" . $streetName);
    }

    public static function getBuildings($streetId, $buildingName)
    {
        return Sender::sendRequest('api/v1/addresses/building?parentId=' . $streetId . "&name=" . $buildingName);
    }

    public static function getDeliveries($aoid)
    {
        return Sender::sendRequest('api/v1/delivery_info?aoid=' . $aoid);
    }

    public static function getDeliveryMethods($addressObjectId, $itemCount, $cartAmount)
    {
        return Sender::sendRequest('api/v1/delivery_methods?address_object_id=' . $addressObjectId
            . '&item_count=' . $itemCount . '&cart_amount=' . $cartAmount);
    }

    public static function setDelivery($arFields)
    {
        $_SESSION["LAMODA"]["DELIVERY"] = [
            "NAME" =>  !empty($arFields["delivery"]["checkoutMethodName"]) ? $arFields["delivery"]["checkoutMethodName"] : "",
            "PRICE" => !empty($arFields["delivery"]["checkoutMethodDeliveryPrice"]) ? $arFields["delivery"]["checkoutMethodDeliveryPrice"] : 0,
            "FREE_AMOUNT_PRICE" => !empty($arFields["delivery"]["checkoutMethodFreeDeliveryNetThreshold"]) ? $arFields["delivery"]["checkoutMethodFreeDeliveryNetThreshold"] : 0,
            "INTERVAL_ID" => !empty($arFields["delivery"]["intervalId"]) ? $arFields["delivery"]["intervalId"] : "",
        ];
        
        return $_SESSION["LAMODA"]["DELIVERY"];
    }

    public static function clearDelivery($arFields)
    {
        $_SESSION["LAMODA"]["DELIVERY"] = [];
        return "success";
    }

    public static function getPickupPoints($addressObjectId, $itemCount, $cartAmount)
    {
        return Sender::sendRequest('api/v1/pickup_points?aoid=' . $addressObjectId . '&item_count=' . $itemCount . '&cart_amount=' . $cartAmount);
    }

    public static function getPickupPointInfo($pickupPointId, $addressObjectId, $itemCount, $cartAmount)
    {
        return Sender::sendRequest('api/v1/pickup_points/' . $pickupPointId . '?aoid=' . $addressObjectId . '&item_count=' . $itemCount . '&cart_amount=' . $cartAmount);
    }

}

?>