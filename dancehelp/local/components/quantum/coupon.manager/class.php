<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use \Bitrix\Main;
use Bitrix\Main\Entity;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Engine\Contract;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Engine\ActionFilter\Authentication;
use \Bitrix\Sale\DiscountCouponsManager;
use \Quantum\Handler\Basket;

Loc::loadMessages(__FILE__);

class couponManager extends \CBitrixComponent implements Contract\Controllerable
{
    function executeComponent()
    {
        $this->clearCouponsList();
        $this->arResult["COUPONS"] = [];
        $this->arResult["PRODUCT_ID"] = $this->arParams["PRODUCT_ID"];
        $this->arResult["BASE_PRICE_NODE_ID"] = $this->arParams["BASE_PRICE_NODE_ID"];
        $this->includeComponentTemplate();
    }

    public function getApplyResultAction($arFields)
    {
        return Basket::getCouponApplyResultFromBasketByProductId($arFields["couponCode"], $arFields["productId"]);
    }

    function removeCouponAction()
    {
        $this->clearCouponsList();
    }

    function clearCouponsList()
    {
        DiscountCouponsManager::init();
        DiscountCouponsManager::clear(true);
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
            'getApplyResult' => [
                '-prefilters' => [
                    Authentication::class
                ]
            ],
            'removeCoupon' => [
                '-prefilters' => [
                    Authentication::class
                ]
            ]
        ];
    }
}