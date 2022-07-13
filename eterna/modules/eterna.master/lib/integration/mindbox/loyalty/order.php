<?php

namespace Eterna\Integration\MindBox\Loyalty;

use \Bitrix\Main;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Eterna\Integration\MindBox\Api;

class Order
{

    public static function getOrderLine($cartItem, $status)
    {
        $line = [
            "lineNumber" => $cartItem["lineNumber"],
            "basePricePerItem" => $cartItem["purchasePricePerItem"],
            "quantity" => $cartItem["quantity"],
            "product" => ["ids" => ["eternaProducts" => $cartItem["productId"]]],
            "status" => ["ids" => ["externalId" => $status]]
        ];

        $siteCatalogDiscount = $cartItem["purchasePricePerItem"] - $cartItem["basePricePerItem"];
        if($siteCatalogDiscount > 0)
        {
            $line["requestedPromotions"] = [
                [
                    "type" => "discount",
                    "promotion" => ["ids" => ["externalId" => "siteCatalogDiscount"]],
                    "amount" => $siteCatalogDiscount * $cartItem["quantity"],
                ]
            ];
        }

        return $line;
    }

    public static function getOrderLines($cartItems, $status)
    {
        $lines = [];
        foreach ($cartItems as $cartItem)
            $lines[] = self::getOrderLine($cartItem, $status);

        return $lines;
    }

    public static function getOrderRequestedPromotion($couponCode, $orderDiscount)
    {
        return [
            "type" => "discount",
            "promotion" => ["ids" => ["externalId" => "XXXXXXXXXX"]],
            "coupon" => [
                "ids" => ["code" => $couponCode],
                "pool" => ["ids" => ["externalId" => "XXXXXXXXXX", "mindboxId" => "XXXXXXXXXX"]]
            ],
            "amount" => $orderDiscount,
        ];
    }

    public static function getOrderRequestedPromotions($coupons, $orderDiscount)
    {
        $promotionsArr = [];
        foreach ($coupons as $coupon)
            $promotionsArr[] = self::getOrderRequestedPromotion($coupon, $orderDiscount);

        return $promotionsArr;
    }

    public static function getOrderCoupon($couponCode)
    {
        return ["ids" => ["code" => $couponCode]];
    }

    public static function getOrderCoupons($coupons)
    {
        $couponsArr = [];
        foreach ($coupons as $coupon)
            $couponsArr[] = self::getOrderCoupon($coupon);

        return $couponsArr;
    }

    public static function getOrderPoint($pointValue, $balanceType)
    {
        return [
            "balanceType" => ["ids" => ["systemName" => $balanceType]],
            "amount" => $pointValue,
        ];
    }

    public static function getOrderPoints($points, $balanceType)
    {
        $pointsArr = [];
        foreach ($points as $pointValue)
            $pointsArr[] = self::getOrderPoint($pointValue, $balanceType);

        return $pointsArr;
    }



    public static function calculateAuthorizedCart($order)
    {
        $data = [
            "customer" => ["mobilePhone" => \Eterna\Handler\User::getUserPhone()],
            "order" => [
                "deliveryCost" => $order["deliveryCost"],
                "customFields" => ["orderDeliveryAddress" => $order["orderDeliveryAddress"]],
                "payments" => [["type" => "Наличные"]],
                "lines" => self::getOrderLines($order["items"], $order["status"]),
                //"requestedPromotions" => self::getOrderRequestedPromotions($order["coupons"]["codes"], $order["coupons"]["orderDiscount"]),
                "coupons" => self::getOrderCoupons($order["coupons"]["codes"]),
                "bonusPoints" => self::getOrderPoints($order["bonusPoints"]["points"], $order["bonusPoints"]["balanceType"]),
            ]
        ];

        $result = Api::send("Website.CalculateAuthorizedCart", $data, "sync", false);
        //self::requestLogger("loyalty_calculateAuthorizedCart", $result, $data);
        return $result;
    }

    public static function calculateUnauthorizedCart($order)
    {
        $data = [
            "order" => [
                "deliveryCost" => $order["deliveryCost"],
                "customFields" => ["orderDeliveryAddress" => $order["orderDeliveryAddress"]],
                "payments" => [["type" => "Наличные"]],
                "lines" => self::getOrderLines($order["items"], $order["status"]),
                //"requestedPromotions" => self::getOrderRequestedPromotions($order["coupons"]["codes"], $order["coupons"]["orderDiscount"]),
                "coupons" => self::getOrderCoupons($order["coupons"]["codes"]),
                "bonusPoints" => self::getOrderPoints($order["bonusPoints"]["points"], $order["bonusPoints"]["balanceType"]),
            ]
        ];

        $result = Api::send("Website.CalculateUnauthorizedCart", $data, "sync", false);
        //self::requestLogger("loyalty_calculateUnauthorizedCart", $result, $data);
        return $result;
    }

    public static function beginAuthroziedOrderTransaction($order)
    {
        $data = [
            "customer" => ["mobilePhone" => \Eterna\Handler\User::getUserPhone()],
            "order" => [
                "deliveryCost" => $order["deliveryCost"],
                "customFields" => ["orderDeliveryAddress" => $order["orderDeliveryAddress"]],
                "totalPrice" => $order["totalPrice"],
                //"requestedPromotions" => self::getOrderRequestedPromotions($order["coupons"]["codes"], $order["coupons"]["orderDiscount"]),
                "coupons" => self::getOrderCoupons($order["coupons"]["codes"]),
                "bonusPoints" => self::getOrderPoints($order["bonusPoints"]["points"], $order["bonusPoints"]["balanceType"]),
                "lines" => self::getOrderLines($order["items"], $order["status"]),
                "payments" => [
                    [
                        "type" => $order["payments"]["type"],
                        "id" => $order["payments"]["id"],
                        "amount" => $order["payments"]["amount"],
                    ],
                ],
                "transaction" => ["ids" => ["externalId" => $order["transactionId"]]],
            ]
        ];

        $result = Api::send("Website.BeginAuthroziedOrderTransaction", $data, "sync", false);
        //self::requestLogger("loyalty_beginAuthroziedOrderTransaction", $result, $data);
        return $result;
    }

    public static function beginUnauthroziedOrderTransaction($order)
    {
        $data = [
            "customer" => ["mobilePhone" => $order["phone"], "email" => $order["email"]],
            "order" => [
                "deliveryCost" => $order["deliveryCost"],
                "customFields" => ["orderDeliveryAddress" => $order["orderDeliveryAddress"]],
                "totalPrice" => $order["totalPrice"],
                //"requestedPromotions" => self::getOrderRequestedPromotions($order["coupons"]["codes"], $order["coupons"]["orderDiscount"]),
                "coupons" => self::getOrderCoupons($order["coupons"]["codes"]),
                "bonusPoints" => self::getOrderPoints($order["bonusPoints"]["points"], $order["bonusPoints"]["balanceType"]),
                "lines" => self::getOrderLines($order["items"], $order["status"]),
                "payments" => [
                    [
                        "type" => $order["payments"]["type"],
                        "id" => $order["payments"]["id"],
                        "amount" => $order["payments"]["amount"],
                    ],
                ],
                "transaction" => ["ids" => ["externalId" => $order["transactionId"]]],
            ]
        ];

        $result = Api::send("Website.BeginUnauthroziedOrderTransaction", $data, "sync", false);
        //self::requestLogger("loyalty_beginUnauthroziedOrderTransaction", $result, $data);
        return $result;
    }


    public static function saveOfflineOrder($order)
    {
        $customer = $order["authorized"] ?
            ["mobilePhone" => \Eterna\Handler\User::getUserPhone()] :
            ["mobilePhone" => $order["phone"], "email" => $order["email"]];

        $data = [
            "customer" => $customer,
            "order" => [
                "ids" => ["websiteEternaRussiaId" => $order["id"]],
                "deliveryCost" => $order["deliveryCost"],
                "customFields" => ["orderDeliveryAddress" => $order["orderDeliveryAddress"]],
                "totalPrice" => $order["totalPrice"],
                "lines" => self::getOrderLines($order["items"], $order["status"]),
                "payments" => [
                    [
                        "type" => $order["payments"]["type"],
                        "id" => $order["payments"]["id"],
                        "amount" => $order["payments"]["amount"],
                    ],
                ],
            ]
        ];

        $result = Api::send("Website.SaveOfflineOrder", $data, "sync");
        //self::requestLogger("loyalty_saveOfflineOrder", $result, $data);
        return $result;
    }

    public static function сommitOrderTransaction($order)
    {
        $data = [
            "order" => [
                "ids" => ["websiteEternaRussiaId" => $order["ORDER_ID"]],
                "transaction" => ["ids" => ["externalId" => $order["TRANSACTION_ID"]]]
            ]
        ];
        $result = Api::send("Website.CommitOrderTransaction", $data, "async", false);
        //self::requestLogger("loyalty_сommitOrderTransaction", $result, $data);
        return $result;
    }

    public static function rollbackOrderTransaction($order)
    {
        $data = ["order" => ["transaction" => ["ids" => ["externalId" => $order["transactionId"]]]]];
        $result = Api::send("Website.RollbackOrderTransaction", $data, "sync");
        self::requestLogger("loyalty_rollbackOrderTransaction", $result, $data);
        return $result;
    }

    public static function getOrderList($phone, $pageNumber = "1", $itemsPerPage = "200")
    {
        $data = [
            "customer" => ["mobilePhone" => $phone],
            //"customer" => ["ids" => ["mindboxId" => "1"]],
            "page" => ["pageNumber" => $pageNumber, "itemsPerPage" => $itemsPerPage]
        ];

        return Api::send("Website.GetCustomerOrders", $data, "sync", false);
    }

    public static function getOrder($mindboxOrderId)
    {
        $data = ["order" => ["ids" => ["mindboxId" => $mindboxOrderId]]];
        return Api::send("Website.GetOrder", $data, "sync", false);
    }

    public static function requestLogger($logFile, $result, $requestData)
    {
        \Eterna\Tools::addTolLog([
            "TIME" => date("d.m.Y H:i:s"),
            "USER_PHONE" => \Eterna\Handler\User::getUserPhone(),
            "RESULT" => $result,
            "REQUEST_DATA" => $requestData,
        ], $logFile);
    }

}