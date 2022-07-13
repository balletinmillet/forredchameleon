<?php

namespace Quantum\Handler;

use \Bitrix\Main;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Sale\DiscountCouponsManager;
use \Bitrix\Sale\Order;
use \Bitrix\Sale\Basket as BitrixBasket;
use \Bitrix\Currency\CurrencyManager;
use \Bitrix\Main\Context;
use \Bitrix\Sale\Discount;
use \Bitrix\Sale\Fuser;

Loader::includeModule('iblock');

class Basket
{
    public static function getCouponApplyResultFromBasket($couponCode, $products)
    {
        DiscountCouponsManager::init();
        DiscountCouponsManager::clear();
        DiscountCouponsManager::add($couponCode);

        $order = Order::create(SITE_ID, $GLOBALS['USER']->GetId());
        $basket = BitrixBasket::create(SITE_ID);

        foreach ($products as $product)
        {
            $item = $basket->createItem('catalog', $product["ID"]);
            $item->setFields(
                [
                    'NAME' => $product['NAME'],
                    'QUANTITY' => 1,
                    'CURRENCY' => CurrencyManager::getBaseCurrency(),
                    'LID' => Context::getCurrent()->getSite(),
                    'PRODUCT_PROVIDER_CLASS' => '\Bitrix\Catalog\Product\CatalogProvider',
                ]
            );
        }

        $order->setBasket($basket);
        $discounts = Discount::buildFromOrder($order);
        $discounts->calculate();
        return $discounts->getApplyResult(true);
    }

    public static function getCouponApplyResultFromBasketByProductId($couponCode, $productId)
    {
        $products = [["ID" => $productId, "NAME" => ""]];
        $applyResult = self::getCouponApplyResultFromBasket($couponCode, $products);

        if(array_key_exists($couponCode, $applyResult["COUPON_LIST"]))
            return $applyResult["PRICES"]["BASKET"]["n1"];

        return [];
    }

    function deleteProducts($products)
    {
        if(empty($products))
            return false;

        $basketProductItems = [];
        $basket = BitrixBasket::loadItemsForFUser(Fuser::getId(), Context::getCurrent()->getSite());
        $basketItems = $basket->getBasketItems();
        if($basketItems)
        {
            foreach($basketItems as $basketItem)
            {
                if(in_array($basketItem->getField('PRODUCT_ID'), $products))
                    $basketProductItems[] = $basketItem;
            }
        }

        if($basketProductItems)
        {
            foreach ($basketProductItems as $index => $basketProductItem)
                $result[] = $basketProductItem->delete();

            $basket->save();
        }

        return true;
    }

    function isProductsInBasket($products)
    {
        $productsInBasket = [];
        $basket = BitrixBasket::loadItemsForFUser(Fuser::getId(), Context::getCurrent()->getSite());
        $basketItems = $basket->getBasketItems();
        if($basketItems)
        {
            foreach($basketItems as $basketItem)
                $productsInBasket[$basketItem->getField('PRODUCT_ID')] = in_array($basketItem->getField('PRODUCT_ID'), $products) ? "Y" : "N";
        }

        return $productsInBasket;
    }
}

