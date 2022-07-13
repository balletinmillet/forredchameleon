<?php

namespace Eterna\Integration\Lamoda;

use \Bitrix\Main;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;

class Good
{

    function getStockGoods($withZeroQuantity = false)
    {
        $goods = [];
        $withZeroQuantity = $withZeroQuantity ? "" : "&withZeroQuantity=0";

        $firstPageResult = Sender::sendRequest('api/v1/stock/goods?page=1&limit=400' . $withZeroQuantity);
        $pages = $firstPageResult["pages"];

        if($pages > 0 && $pages < 50)
        {
            $goods = $firstPageResult["_embedded"]["stockStates"];
            for ($i = 2; $i <= $pages; $i++)
            {
                $pageResult = Sender::sendRequest('api/v1/stock/goods?page=' .$i . '&limit=400' . $withZeroQuantity);
                if(!empty($pageResult["_embedded"]["stockStates"]))
                    $goods = array_merge($goods, $pageResult["_embedded"]["stockStates"]);
            }
        }

        return $goods;
    }

    function getStockGoodsList($goodsIds)
    {
        $queryString = "";
        for ($i = 0; $i < count($goodsIds); $i++)
        {
            $querySeparator = $i == 0 ? "?" : "&";
            $queryString .= $querySeparator . 'sku[]=' . $goodsIds[$i];
        }

        return Sender::sendRequest('api/v1/stock/goods' . $queryString);
    }

    function getStockGood($goodId)
    {
        return Sender::sendRequest('api/v1/stock/goods?sku[]=' . $goodId);
    }

    function getStockGoodQuantity($goodId)
    {
       $good = self::getStockGood($goodId);
       if($stockStates = $good["_embedded"]["stockStates"])
            return !empty($stockStates[0]["quantity"]) ? $stockStates[0]["quantity"] : 0;
       else
           return 0;

    }
}

?>