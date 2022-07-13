<?php

namespace Quantum\Handler;

use \Bitrix\Main;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Sale\Fuser;
use \Bitrix\Main\Context;
use \Bitrix\Sale\Basket as SaleBasket;


Loader::includeModule('iblock');

class Basket
{
    function deleteProducts($products)
    {
        if(empty($products))
            return false;

        $basketProductItems = [];
        $basket = SaleBasket::loadItemsForFUser(Fuser::getId(), Context::getCurrent()->getSite());
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
        $basket = SaleBasket::loadItemsForFUser(Fuser::getId(), Context::getCurrent()->getSite());
        $basketItems = $basket->getBasketItems();
        if($basketItems)
        {
            foreach($basketItems as $basketItem)
                $productsInBasket[$basketItem->getField('PRODUCT_ID')] = in_array($basketItem->getField('PRODUCT_ID'), $products) ? "Y" : "N";
        }

        return $productsInBasket;
    }
}

