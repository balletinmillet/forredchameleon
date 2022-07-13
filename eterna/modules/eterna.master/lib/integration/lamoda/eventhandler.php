<?php

namespace Eterna\Integration\Lamoda;

use \Bitrix\Main;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\EventResult;
use \Eterna\Handler\Order;
use \Eterna\Handler\Catalog;
use \Eterna\Integration\Lamoda\Order as LamodaOrder;

class EventHandler
{
    public function onSaleDeliveryServiceCalculateHandler($event)
    {
        $shipment = $event->getParameter('SHIPMENT');
        $order = $shipment->getOrder();
        $orderPrice = $order->getPrice();

        if($_SESSION["LAMODA"]["DELIVERY"]["FREE_AMOUNT_PRICE"] > $orderPrice)
        {
            $result = $event->getParameter('RESULT');
            $result->setDeliveryPrice($_SESSION["LAMODA"]["DELIVERY"]["PRICE"]);
            $event->addResult(
                new EventResult(EventResult::SUCCESS, ['RESULT' => $result])
            );
        }
    }
    
    public function OnSaleOrderSavedHandler(Main\Event $event)
    {
        $order = $event->getParameter("ENTITY");
        $isNew = $event->getParameter("IS_NEW");

        if ($isNew)
        {
            $propertyCollection = $order->getPropertyCollection();
            $propertyCodes = ["ORDER_DESCRIPTION", "NAME", "LAST_NAME", "PHONE", "EMAIL", "CITY", "STREET", "BUILDING", "APARTMENT", "DELIVERY_INTERVAL_ID", "KLADR_CODE"];
            $propertyValues = Order::getPropertyByCodes($propertyCollection, $propertyCodes);

            $lamodaParams = [
                "orderNr" => $order->getId(),
                "deliveryIntervalId" => $propertyValues["DELIVERY_INTERVAL_ID"],
                "deliveryPrice" => $order->getDeliveryPrice(),
                "comment" => nl2br($order->getField('USER_DESCRIPTION')),
                "shippingType" => "fulfilment",
                "paymentMethod" => "COD",
                "source" => "lamoda.ru",
                "options" => [
                    "autoconfirm" => false
                ],
                "customerInfo" => [
                    "firstName" => $propertyValues["NAME"],
                    "lastName" => $propertyValues["LAST_NAME"],
                    "middleName" => "",
                    "phone" => "+".$propertyValues["PHONE"],
                    "email" => $propertyValues["EMAIL"],
                    "address" => [
                        "city" => $propertyValues["CITY"],
                        "street" => $propertyValues["STREET"],
                        "apartment" => $propertyValues["APARTMENT"],
                        "houseNum" => $propertyValues["BUILDING"],
                        "kladrCode" => $propertyValues["KLADR_CODE"],
                    ]
                ]
            ];

            $basketItems = array();
            $basket = $order->getBasket();
            foreach ($basket as $basketItem)
            {
                $basketItems[$basketItem->getField('PRODUCT_ID')] = [
                    "description" => $basketItem->getField('NAME'),
                    "quantity" => $basketItem->getField('QUANTITY'),
                    "paidPrice" => $basketItem->getField('PRICE'),
                    "price" => $basketItem->getField('PRICE'),
                ];
            }

            $lamodaBasketItems = [];
            $productsProperties = Catalog::getProductsProperties(array_keys($basketItems), ["ARTICLE", "SIZE_MANUFACTURER", "VAT"], true);
            foreach ($productsProperties as $id => $productsProperty)
            {
                $productQuantity = $basketItems[$id]["quantity"];
                if($productQuantity > 1)
                {
                    $sku = Catalog::getLamodaArticle($productsProperty["ARTICLE"], $productsProperty["SIZE_MANUFACTURER"]);
                    for ($i = 0; $i < $productQuantity; $i++)
                    {
                        $lamodaBasketItems[] = [
                            "sku" => $sku,
                            "description" =>  $basketItems[$id]["description"],
                            //"quantity" => $basketItems[$id]["quantity"],
                            "cogsPrice" => $productsProperty["PURCHASING_PRICE"],
                            "paidPrice" => $basketItems[$id]["price"],
                            "price" => $basketItems[$id]["price"],
                            "totalDiscount" => $productsProperty["PURCHASING_PRICE"] - $basketItems[$id]["price"],
                            "vat" => 10
                        ];
                    }
                }
                else
                {
                    $lamodaBasketItems[] = [
                        "sku" => Catalog::getLamodaArticle($productsProperty["ARTICLE"], $productsProperty["SIZE_MANUFACTURER"]),
                        "description" =>  $basketItems[$id]["description"],
                        //"quantity" => $basketItems[$id]["quantity"],
                        "cogsPrice" => $productsProperty["PURCHASING_PRICE"],
                        "paidPrice" => $basketItems[$id]["price"],
                        "price" => $basketItems[$id]["price"],
                        "totalDiscount" => $productsProperty["PURCHASING_PRICE"] - $basketItems[$id]["price"],
                        "vat" => 10
                    ];
                }
            }

            $lamodaParams["items"] = $lamodaBasketItems;
            $lamodaOrderHandler = new LamodaOrder();
            $lamodaOrderHandler->createOrder($lamodaParams);
            
        }
    }
}

?>