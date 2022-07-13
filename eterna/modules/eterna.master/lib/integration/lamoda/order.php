<?php

namespace Eterna\Integration\Lamoda;

use \Bitrix\Main;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;

class Order
{
    public function __construct() {}

    public function createOrder($params)
    {
        return Sender::sendRequest('api/v1/orders',  $params, "POST");
    }

    public function getOrders()
    {
        return Sender::sendRequest('api/v1/orders');
    }
}

?>