<?php

namespace Eterna\Handler;

use \Bitrix\Main;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;


class Discount
{
    private static $ETERNAGIFT_coupon = [
        "COUPON_CODE" => "ETERNAGIFT",
        "USER_DISCOUNT_FIELD" => "UF_EXPIRED_ETERNAGIFT"
    ];

    private static $wearesorry_coupon = [
        "COUPON_CODE" => "wearesorry",
        "USER_DISCOUNT_FIELD" => "UF_EXPIRED_WEARESORRY"
    ];

    private static $_15NY22_coupon = [
        "COUPON_CODE" => "15NY22",
        "USER_DISCOUNT_FIELD" => "UF_EXPIRED_15NY22"
    ];

    public static function checkCouponsAvailability()
    {
        self::checkETERNAGIFTCouponAvailability();
        //self::checkWearesorryCouponAvailability();
        self::check15NY22CouponAvailability();
    }

    public static function setCouponETERNAGIFTExpiredStatus()
    {
        self::setExpiredStatus(self::$ETERNAGIFT_coupon["USER_DISCOUNT_FIELD"]);
    }

    private static function checkETERNAGIFTCouponAvailability()
    {
        $couponExpired = self::isDiscountExpired(self::$ETERNAGIFT_coupon["USER_DISCOUNT_FIELD"]);
        if($couponExpired)
            self::deleteCoupon(self::$ETERNAGIFT_coupon["COUPON_CODE"]);
    }
    
    public static function setCouponWearesorryExpiredStatus()
    {
        self::setExpiredStatus(self::$wearesorry_coupon["USER_DISCOUNT_FIELD"]);
    }

    private static function checkWearesorryCouponAvailability()
    {
        $couponExpired = self::isDiscountExpired(self::$wearesorry_coupon["USER_DISCOUNT_FIELD"]);
        if($couponExpired)
            self::deleteCoupon(self::$wearesorry_coupon["COUPON_CODE"]);
    }

    public static function setCoupon15NY22ExpiredStatus()
    {
        self::setExpiredStatus(self::$_15NY22_coupon["USER_DISCOUNT_FIELD"]);
    }

    private static function check15NY22CouponAvailability()
    {
        $couponExpired = self::isDiscountExpired(self::$_15NY22_coupon["USER_DISCOUNT_FIELD"]);
        if($couponExpired)
            self::deleteCoupon(self::$_15NY22_coupon["COUPON_CODE"]);
    }

    private static function addCoupon($couponCode)
    {
        \Bitrix\Sale\DiscountCouponsManager::add($couponCode);
    }

    private static function deleteCoupon($couponCode)
    {
        \Bitrix\Sale\DiscountCouponsManager::delete($couponCode);
    }

    private static function isDiscountExpired($userFieldName)
    {
        global $USER;
        $userId = $USER->GetID();
        if($userId)
        {
            $filter = array("ID" => $userId);
            $params = array('SELECT' => array($userFieldName), 'NAV_PARAMS' => array('nTopCount' => 1), 'FIELDS' => array('ID'));
            $data = \CUser::GetList(($by = "ID"), ($order = "DESC"), $filter, $params);

            while ($arUser = $data->Fetch()) {
                if($arUser[$userFieldName] == 1)
                    return true;
            }
        }
        else
        {
            return true;
        }

        return false;
    }

    public static function setExpiredStatus($userFieldName)
    {
        global $USER;
        $userId = $USER->GetID();
        if($userId)
        {
            $fields = array($userFieldName => 1);
            $user = new \CUser;
            $res = $user->Update($userId, $fields);
            $_SESSION[$userFieldName] = "Y";
        }
    }
}
