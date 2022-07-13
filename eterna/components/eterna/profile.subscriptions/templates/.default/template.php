<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
use \Bitrix\Main\Localization\Loc;
?>
<div class="container">
    <?if(!empty($arResult)):?>
    <div id="subscribe-form" class="profile-block-shown user-info">
        <div class="c-form">
            <div class="title">
                <?=Loc::getMessage('TITLE_NOTIFICATIONS')?>
            </div>
            <div class="sub-title">
                <?=Loc::getMessage('SUB_TITLE_SUBSCRIBE')?>
            </div>
            <div class="c-fields subscription-block">
                <?foreach ($arResult["SUBSCRIPTIONS"] as $index => $subscription):?>
                <div class="c-form__field c-form__field_subscription2">
                    <div class="row">
                        <label class="col-12 col-sm-10 col-lg-10">
                            <div class="c-form__field_subscription2__row row no-gutters">
                                <input type="checkbox" value="<?=$subscription["isSubscribed"]?>"
                                       name="<?=strtoupper($subscription["pointOfContact"])?>"
                                       id="<?=strtoupper($subscription["pointOfContact"])?>"
                                    <?if($subscription["isSubscribed"] == "Y"):?> checked <?endif;?>>
                                <i></i>
                                <span><?=$subscription["pointOfContact"]?></span></div>
                        </label>
                    </div>
                </div>
                <?endforeach;?>
            </div>
            <div class="c-form__field mt-4 mb-3 save">
                <div class="result-message-container">
                </div>
                <div class="row">
                    <div class="c-form__input-wrapper col-12 col-md-10 col-lg-4">
                        <a class="styled-button s2" value="<?=Loc::getMessage('BUTTON_SAVE')?>" name="save"><?=Loc::getMessage('BUTTON_SAVE')?></a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>


<script>
    BX.EternaSubscribe.init(<?=CUtil::PhpToJSObject([
        'mainContainerId' => "subscribe-form",
        'result' => $arResult["SUBSCRIPTIONS"],
    ])?>);
</script>
<?endif;?>


