<?php

namespace Eterna\Integration\MindBox\Loyalty;

use \Bitrix\Main;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;

class BasketContainer
{
    protected static $products = [];
    protected static $productsArticleKEY_IdVALUE = [];

    public static function setProductFields($productArticle, $fields)
    {
        $productId = self::getProductIdByArticle($productArticle);
        if(!empty($productId))
            $_SESSION["LOYALTY_BASKET"][$productId] = $fields;
    }

    public static function getProductFields($productId)
    {
        return $_SESSION["LOYALTY_BASKET"][$productId];
    }

    public static function addArticleKEY_IdVALUE($productArticle, $productId)
    {
        self::$productsArticleKEY_IdVALUE[$productArticle] = $productId;
    }

    public static function getProductIdByArticle($productArticle)
    {
       return self::$productsArticleKEY_IdVALUE[$productArticle];
    }
}