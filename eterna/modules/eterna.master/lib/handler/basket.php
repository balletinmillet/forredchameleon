<?php

namespace Eterna\Handler;

use \Bitrix\Main;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Eterna\Handler\Catalog;
use \Eterna\Integration\Lamoda\Good;
use \Bitrix\Sale\Basket as SaleBasket;
use \Bitrix\Sale\Fuser;
use \Bitrix\Main\Context;

Loader::includeModule("sale");
Loader::includeModule("catalog");

class Basket
{
    function checkItemsStockAvailability($basketItems): array
    {
        $result = [];
        $unavailableProducts = [];
        $catalogProductIds = array_keys($basketItems);
        $id_article_matchArray = Catalog::getLamodaArticles($catalogProductIds);
        $productArticles = array_keys($id_article_matchArray);
        $stockGoodsList = Good::getStockGoodsList($productArticles);
        if($stockGoodsList["_embedded"]["stockStates"])
        {
            foreach ($stockGoodsList["_embedded"]["stockStates"] as $index => $item)
            {
                $catalogProductId = $id_article_matchArray[$item["sku"]];
                if ($item["quantity"] < $basketItems[$catalogProductId]["QUANTITY"])
                    $unavailableProducts[$catalogProductId] = $basketItems[$catalogProductId];
            }
        }
        
        if(!empty($unavailableProducts))
        {
            $result["ERROR_CODE"] = "UNAVAILABLE_IN_STOCK";
            $result["UNAVAILABLE_GOODS"] = $unavailableProducts;
        }

        return $result;
    }

    function getBasketItemsNumber(): int
    {
        $basketItemsNumber = 0;
        $basket = SaleBasket::loadItemsForFUser(Fuser::getId(), Context::getCurrent()->getSite());
        foreach ($basket as $index => $item)
        {
            if($item->getField('CAN_BUY') != "N")
                $basketItemsNumber++;
        }

        return $basketItemsNumber;
    }

    function deleteUnavailableProducts()
    {
        $unavailableItems = [];
        $basket = SaleBasket::loadItemsForFUser(Fuser::getId(), Context::getCurrent()->getSite());
        foreach ($basket as $index => $item)
        {
            if($item->getField('CAN_BUY') == "N")
                $unavailableItems[] = $item->getId();
        }

        if(!empty($unavailableItems))
        {
            foreach ($unavailableItems as $unavailableItemId)
                $basket->getItemById($unavailableItemId)->delete();

            $basket->save();
        }
    }
}
