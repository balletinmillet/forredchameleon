<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
use \Bitrix\Main\Localization\Loc;
?>

<?if(!empty($arResult)):?>
<div id="bx-coupon-manager-container">
    <div class="bx-soa-coupon clearfix">
        <div class="bx-soa-coupon-block">
            <div class="bx-soa-coupon-input">
                <input class="form-control bx-ios-fix" type="text" placeholder="<?=Loc::getMessage('PLACEHOLDER_QUESTION_HAVE_YOU_COUPON')?>">
            </div>
            <span class="bx-soa-coupon-items">
                <span class="bx-soa-coupon-item"></span>
            </span>
            <div class="button-block">
                <div class="button-container">
                    <a class="clear_btn clear__select coupon-apply"><?=Loc::getMessage('BUTTON_APPLY')?></a>
                </div>
                <div class="button-container">
                    <a class="clear_btn clear__select coupon-remove"><?=Loc::getMessage('BUTTON_DELETE')?></a>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    BX.QuantumCouponMaenager.init(<?=CUtil::PhpToJSObject([
        'mainContainerId' => "bx-coupon-manager-container",
        'productId' => $arResult["PRODUCT_ID"],
        'basePriceNodeId' => $arResult["BASE_PRICE_NODE_ID"],
    ])?>);
</script>

<?endif;?>


