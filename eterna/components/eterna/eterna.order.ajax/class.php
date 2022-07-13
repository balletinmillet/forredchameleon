<?php

use \Bitrix\Main\Loader;
use \Bitrix\Main\Application;
use \Bitrix\Main\Engine\Contract;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Engine\ActionFilter\Authentication;
use \Eterna\Integration\Lamoda\Delivery;
use \Eterna\Handler\Catalog;
use \Eterna\Integration\MindBox\Loyalty\BasketContainer;
use \Eterna\Integration\MindBox\Loyalty\User;
use \Eterna\Tools;
use \Eterna\Integration\MindBox\Loyalty\Order;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
    die();

/**
 * @var $APPLICATION CMain
 * @var $USER CUser
 */

Loc::loadMessages(__FILE__);

class EternaOrderAjax extends \CBitrixComponent implements Contract\Controllerable
{
    function executeComponent()
    {
        $this->includeComponentTemplate();
    }

    public function getLocationAction($arFields)
    {
        $locationType = $arFields["locationType"];
        $inputText = $arFields["inputText"];
        $parentLocationId = $arFields["parentLocationId"];

        if($locationType == "CITY")
            return Delivery::getCities($inputText);
        elseif($locationType == "STREET")
            return Delivery::getStreets($parentLocationId, $inputText);
        elseif($locationType == "BUILDING")
            return Delivery::getBuildings($parentLocationId, $inputText);

        return '';
    }

    public function getDeliveriesAction($arFields)
    {
        return Delivery::getDeliveryMethods($arFields["locationId"], $arFields["itemCount"], $arFields["cartAmount"]);
    }

    public function setDeliveryAction($arFields)
    {
        return Delivery::setDelivery($arFields);
    }

    public function clearDeliveryAction($arFields)
    {
        return Delivery::clearDelivery($arFields);
    }

    public function prepareBasketItemsForCalculate($items)
    {
        $prepareBasketItems = [];
        $products = Catalog::getMindboxArticles(array_keys($items));
        $productsPrices = Catalog::getProductsPrice(array_keys($items));
        $productsPurchasingPrices = Catalog::getProductsPurchasingPrice(array_keys($items));

        foreach ($products as $productArticle => $productId)
        {
            $prepareBasketItems[] = [
                "purchasePricePerItem" => $productsPurchasingPrices[$productId],
                "basePricePerItem" => $productsPrices[$productId],
                "quantity" => $items[$productId]["quantity"],
                "productId" => $productArticle,
            ];

            BasketContainer::addArticleKEY_IdVALUE($productArticle, $productId);
        }

        return $prepareBasketItems;
    }

    public function prepareBasketItemsForTransaction($items)
    {
        $prepareBasketItems = []; $lineCounter = 1;
        $products = Catalog::getMindboxArticles(array_keys($items));
        $productsPrices = Catalog::getProductsPrice(array_keys($items));
        $productsPurchasingPrices = Catalog::getProductsPurchasingPrice(array_keys($items));

        foreach ($products as $productArticle => $productId)
        {
            $prepareBasketItems[] = [
                "purchasePricePerItem" => $productsPurchasingPrices[$productId],
                "basePricePerItem" => $productsPrices[$productId],
                "quantity" => $items[$productId]["quantity"],
                "productId" => $productArticle,
                "lineNumber" => $lineCounter,
            ];

            $lineCounter++;
            BasketContainer::addArticleKEY_IdVALUE($productArticle, $productId);
        }

        return $prepareBasketItems;
    }

    public function confirmMobilePhoneAction($arFields)
    {
        $confirmMobilePhoneResult = User::confirmMobilePhone($arFields["phone"], $arFields["code"]);
        if($confirmMobilePhoneResult["smsConfirmation"]["processingStatus"] == "MobilePhoneConfirmed")
            return  ["STATUS" => "CONFIRMED"];

        if($confirmMobilePhoneResult["status"] != "Success")
            Tools::addToLogWithDetails($confirmMobilePhoneResult, "CONFIRM_MOBILE_PHONE_RESULT", "OrderAjax_PHONE_CONFIRM");

        return ["STATUS" => "NOT_CONFIRMED"];
    }

    public function checkMobilePhoneAuthorizationCodeAction($arFields)
    {
        $confirmMobilePhoneResult = User::checkMobilePhoneAuthorizationCode($arFields["phone"], $arFields["code"]);
        if($confirmMobilePhoneResult["status"] == "Success")
            return  ["STATUS" => "CONFIRMED"];

        if($confirmMobilePhoneResult["status"] != "Success")
            Tools::addToLogWithDetails($confirmMobilePhoneResult, "CHECK_MOBILE_PHONE_AUTHORIZATION_CODE_RESULT", "OrderAjax_PHONE_CONFIRM");

        return ["STATUS" => "NOT_CONFIRMED"];
    }

    public function resendMobilePhoneConfirmationCodeAction($arFields)
    {
        if(User::isMobilePhoneExist($arFields["phone"]))
        {
            if(User::isMobilePhoneConfirmed($arFields["phone"]))
            {
                if($arFields["auth"] == "true")
                {
                    return [
                        "STATUS" => "ALREADY_CONFIRMED",
                        "FIELDS" => $arFields
                    ];
                }
                else
                {
                    $sendMobilePhoneAuthorizationCodeResult = User::sendMobilePhoneAuthorizationCode($arFields["phone"]);
                    if($sendMobilePhoneAuthorizationCodeResult["status"] = "Success")
                    {
                        return [
                            "STATUS" => "CODE_SENT",
                            "RESPONSE" => $sendMobilePhoneAuthorizationCodeResult,
                            "CONFIRMATION_TYPE" => "AUTHORIZATION"
                        ];
                    }
                    else
                    {
                        Tools::addToLogWithDetails($sendMobilePhoneAuthorizationCodeResult, "SEND_MOBILE_PHONE_AUTHORIZATION_CODE_RESULT", "OrderAjax_PHONE_CONFIRM");
                        return [
                            "STATUS" => "ERROR",
                            "RESPONSE" => $sendMobilePhoneAuthorizationCodeResult
                        ];
                    }
                }
            }
            else
            {
                $resendMobilePhoneConfirmationCodeResult = User::resendMobilePhoneConfirmationCode($arFields["phone"]);
                if(!array_key_exists("errorId", $resendMobilePhoneConfirmationCodeResult))
                {
                    return [
                        "STATUS" => "CODE_SENT",
                        "RESPONSE" => $resendMobilePhoneConfirmationCodeResult,
                        "CONFIRMATION_TYPE" => "CONFIRMATION",
                        "FIELDS" => $arFields
                    ];
                }
                else
                {
                    $error = [
                        "RESPONSE" => $resendMobilePhoneConfirmationCodeResult,
                        "FIELDS" => $arFields
                    ];

                    Tools::addToLogWithDetails($error, "RESEND_MOBILE_PHONE_CONFIRMATION_CODE_RESULT", "OrderAjax_PHONE_CONFIRM");
                    return [
                        "STATUS" => "ERROR",
                        "RESPONSE" => $resendMobilePhoneConfirmationCodeResult,
                        "FIELDS" => $arFields
                    ];
                }
            }
        }
        else
        {
            if($arFields["auth"] == "true")
            {
                return ["STATUS" => "PHONE_NOT_EXIST"];
            }
            else
            {
                if(!empty($arFields["customer"]))
                {
                   return $this->tryRegisterCustomerPLAndSendCode($arFields["customer"]);
                }
                else
                {
                    return ["STATUS" => "EMPTY_CUSTOMER_FIELDS"];
                }
            }
        }
    }

    public function tryRegisterCustomerPLAndSendCode($arFields)
    {
        $registerCustomerPLResult = User::registerCustomerPL($arFields);
        if($registerCustomerPLResult["status"] == "Success")
        {
            return [
                "STATUS" => "CODE_SENT",
                "RESPONSE" => $registerCustomerPLResult,
                "CONFIRMATION_TYPE" => "CONFIRMATION",
            ];
        }
        else
        {
            if(!empty($registerCustomerPLResult["validationMessages"]))
            {
                $validationMessages = [];
                foreach ($registerCustomerPLResult["validationMessages"] as $index => $validationMessage)
                    $validationMessages[] = $validationMessage["message"];

                return [
                    "STATUS" => "VALIDATION_ERROR",
                    "RESPONSE" => $validationMessages
                ];
            }
            else
            {
                Tools::addToLogWithDetails($registerCustomerPLResult, "REGISTER_CUSTOMER_PL_RESULT", "OrderAjax_PHONE_CONFIRM");
                return [
                    "STATUS" => "ERROR",
                    "RESPONSE" => $registerCustomerPLResult
                ];
            }
        }
    }

    public function beginAuthroziedOrderTransactionAction($arFields)
    {
        $transactionId = Catalog::getUniqId("mindox-transaction-" . hash('sha256', $_COOKIE["mindboxDeviceUUID"]));
        $order = [
            "transactionId" => $transactionId,
            "deliveryCost" => $arFields["deliveryCost"],
            "orderDeliveryAddress" => $arFields["orderDeliveryAddress"],
            "status" => $arFields["status"],
            "items" => self::prepareBasketItemsForTransaction($arFields["items"]),
            "totalPrice" => $arFields["totalPrice"],
            "coupons" => ["codes" => $arFields["coupons"]],
            "bonusPoints" => [
                "balanceType" => $arFields["bonusPoints"]["balanceType"],
                "points" => $arFields["bonusPoints"]["points"],
            ],
            "payments" => [
                "type" => $arFields["payments"]["type"],
                "id" => $arFields["payments"]["id"],
                "amount" => $arFields["payments"]["amount"],
            ],
        ];

        return Order::beginAuthroziedOrderTransaction($order);
    }

    public function beginUnauthroziedOrderTransactionAction($arFields)
    {
        $transactionId = Catalog::getUniqId("mindox-transaction-" . hash('sha256', $_COOKIE["mindboxDeviceUUID"]));
        $order = [
            "phone" => $arFields["phone"],
            "email" => $arFields["email"],
            "transactionId" => $transactionId,
            "deliveryCost" => $arFields["deliveryCost"],
            "orderDeliveryAddress" => $arFields["orderDeliveryAddress"],
            "status" => $arFields["status"],
            "items" => self::prepareBasketItemsForTransaction($arFields["items"]),
            "totalPrice" => $arFields["totalPrice"],
            "coupons" => ["codes" => $arFields["coupons"]],
            "bonusPoints" => [
                "balanceType" => $arFields["bonusPoints"]["balanceType"],
                "points" => $arFields["bonusPoints"]["points"],
            ],
            "payments" => [
                "type" => $arFields["payments"]["type"],
                "id" => $arFields["payments"]["id"],
                "amount" => $arFields["payments"]["amount"],
            ],
        ];

        $beginUnauthroziedOrderTransaction = Order::beginUnauthroziedOrderTransaction($order);
        Tools::addToLogWithDetails($beginUnauthroziedOrderTransaction, "UNAUTH_USER", "LOYALTY_BEGIN_CREATE_ORDER");
        return $beginUnauthroziedOrderTransaction;
    }

    public function saveOfflineOrderAction($arFields)
    {
        $order = [
            "authorized" => $arFields["authorized"],
            "phone" => $arFields["phone"],
            "email" => $arFields["email"],
            "deliveryCost" => $arFields["deliveryCost"],
            "orderDeliveryAddress" => $arFields["orderDeliveryAddress"],
            "status" => $arFields["status"],
            "items" => self::prepareBasketItemsForTransaction($arFields["items"]),
            "totalPrice" => $arFields["totalPrice"],
            "payments" => [
                "type" => $arFields["payments"]["type"],
                "id" => $arFields["payments"]["id"],
                "amount" => $arFields["payments"]["amount"],
            ],
        ];

        return Order::saveOfflineOrder($order);
    }


    public function calculateAuthorizedCartAction($arFields)
    {
        $order = [
            "deliveryCost" => $arFields["deliveryCost"],
            "orderDeliveryAddress" => $arFields["orderDeliveryAddress"],
            "status" => $arFields["status"],
            "items" => self::prepareBasketItemsForCalculate($arFields["items"]),
            "coupons" => [
                "codes" => $arFields["coupons"],
            ],
            "bonusPoints" => [
                "balanceType" => $arFields["bonusPoints"]["balanceType"],
                "points" => $arFields["bonusPoints"]["points"],
            ]
        ];

        $calculatedOrderResult = Order::calculateAuthorizedCart($order);
        self::applyCalculatedOrderResult($calculatedOrderResult);
        return $calculatedOrderResult;
    }

    public function calculateUnauthorizedCartAction($arFields)
    {
        $order = [
            "deliveryCost" => $arFields["deliveryCost"],
            "orderDeliveryAddress" => $arFields["orderDeliveryAddress"],
            "status" => $arFields["status"],
            "items" => self::prepareBasketItemsForCalculate($arFields["items"]),
            "coupons" => [
                "codes" => $arFields["coupons"],
            ],
            "bonusPoints" => [
                "balanceType" => $arFields["bonusPoints"]["balanceType"],
                "points" => $arFields["bonusPoints"]["points"],
            ]
        ];

        $calculatedOrderResult = Order::calculateUnauthorizedCart($order);
        self::applyCalculatedOrderResult($calculatedOrderResult);
        return $calculatedOrderResult;
    }

    public function applyCalculatedOrderResult($calculatedOrderResult)
    {
        foreach ($calculatedOrderResult["order"]["lines"] as $index => $line)
        {
            $fields = [
                "BASE_PRICE" => $line["basePricePerItem"],
                "DISCOUNT_PRICE" => !empty($line["discountedPriceOfLine"]) ? $line["discountedPriceOfLine"] / $line["quantity"] : $line["basePricePerItem"],
            ];
            
            BasketContainer::setProductFields($line["product"]["ids"]["eternaProducts"], $fields);
        }
    }

    /**
     * Возвращает массив для подписи параметров вызываемого компонента
     *
     * @return array
     */
    protected function listKeysSignedParameters()
    {
        return [];
    }

    /**
     * Возвращает массив онфигураций ajax-запросов
     *
     * @return array
     */
    public function configureActions()
    {
        return [
            'getLocation' => [
                '-prefilters' => [
                    Authentication::class
                ]
            ],
            'getDeliveries' => [
                '-prefilters' => [
                    Authentication::class
                ]
            ],
            'setDelivery' => [
                '-prefilters' => [
                    Authentication::class
                ]
            ],
            'clearDelivery' => [
                '-prefilters' => [
                    Authentication::class
                ]
            ],
            'calculateAuthorizedCart' => [
                '-prefilters' => [
                    Authentication::class
                ]
            ],
            'calculateUnauthorizedCart' => [
                '-prefilters' => [
                    Authentication::class
                ]
            ],
            'beginAuthroziedOrderTransaction' => [
                '-prefilters' => [
                    Authentication::class
                ]
            ],
            'beginUnauthroziedOrderTransaction' => [
                '-prefilters' => [
                    Authentication::class
                ]
            ],
            'saveOfflineOrder' => [
                '-prefilters' => [
                    Authentication::class
                ]
            ],
            'resendMobilePhoneConfirmationCode' => [
                '-prefilters' => [
                    Authentication::class
                ]
            ],
            'confirmMobilePhone' => [
                '-prefilters' => [
                    Authentication::class
                ]
            ],
            'checkMobilePhoneAuthorizationCode' => [
                '-prefilters' => [
                    Authentication::class
                ]
            ],
        ];
    }

}

