<?php

namespace Quantum\Handler;

use \Bitrix\Main;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;

class Discount
{
    protected static $conditions = [
        "LOCATION_FREE_DELIVERY" => [
            "MSK_FREE_DELIVERY" => [
                "ZIP" => "0000073738",
                "ORDER_SUM=<" => 3000,
            ],
            "EXCEPT_MSK_FREE_DELIVERY" => [
                "ORDER_SUM=<" => 5000,
            ]
        ]
    ];

    public static function getConditions($conditionKey)
    {
        switch ($conditionKey)
        {
            case 'LOCATION_FREE_DELIVERY':
               return self::conditionLOCATION_FREE_DELIVERY();
        }
    }

    public static function conditionLOCATION_FREE_DELIVERY()
    {
        return self::$conditions["LOCATION_FREE_DELIVERY"];
    }

}