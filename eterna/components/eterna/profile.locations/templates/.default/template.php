<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
use \Bitrix\Main\Localization\Loc;
?>
<?if(!empty($arResult)):?>
    <form id="profile-location-form" action="/" class="styled-form s1 form-city-delivery has-validation-callback">
        <div class="location-container">
            <div class="courier-locations">
                <div class="row">
                <?foreach ($arResult["COURIER_LOCATIONS"] as $index => $location):?>
                    <div class="col-md-5 padding-5">
                        <div class="location-item">
                           <div class="item-title">
                                <?=Loc::getMessage('TITLE_COURIER_DELIVERY')?>
                           </div>
                            <div class="item-address">
                                <?=$location["DISPLAY_ADDRESS"]?>
                            </div>
                        </div>
                    </div>
                <?endforeach;?>
                <?if(count($arResult["COURIER_LOCATIONS"]) < 4):?>
                    <div class="col-12 padding-5">
                        <div class="c-form__field mt-3 mb-3 save">
                            <div class="c-form__input-wrapper ">
                                <a class="styled-button s2" value="Добавить" name="add"><?=Loc::getMessage('BUTTON_ADD_LOCATIONS')?></a>
                            </div>
                        </div>
                    </div>
                </div>
                <?endif;?>
            </div>
        </div>
<?if(count($arResult["COURIER_LOCATIONS"]) < 4):?>
        <div id="location-popup" class="" style="display: none">

        <div class="location-form-title">
            Укажите адрес
        </div>
        <div class="fields-container">
            <div class="input-wrapper">

                <div class="soa-property-container">
                    <input autocomplete="off" type="text" name="CITY" data-autocomplete="city"
                           value="" class="ui-autocomplete-input" placeholder="<?=Loc::getMessage('PLACEHOLDER_CITY')?>">
                </div>

                <div class="soa-property-container">
                    <input autocomplete="off" type="text" name="STREET" data-autocomplete="street"
                           value="" class="ui-autocomplete-input" placeholder="<?=Loc::getMessage('PLACEHOLDER_STREET')?>">
                </div>

                <div class="soa-property-container">
                    <input autocomplete="off" type="text" name="BUILDING" data-autocomplete="building"
                           value="" class="ui-autocomplete-input" placeholder="<?=Loc::getMessage('PLACEHOLDER_BUILDING')?>">
                </div>

                <div class="soa-property-container">
                    <input autocomplete="off" type="text" name="HOUSING" data-autocomplete="housing"
                           value="" class="ui-autocomplete-input" placeholder="<?=Loc::getMessage('PLACEHOLDER_HOUSING')?>">
                </div>

                <div class="soa-property-container">
                    <input autocomplete="off" type="text" name="APARTMENT" data-autocomplete="apartment"
                           value="" class="ui-autocomplete-input" placeholder="<?=Loc::getMessage('PLACEHOLDER_APARTMENT')?>">
                </div>

            <ul id="ui-id-1" tabindex="0" class="ui-menu ui-widget ui-widget-content ui-autocomplete ui-front" style="display: none;"></ul>
            </div>
        </div>
        <div class="c-form__field mt-3 mb-3 save">
            <div class="result-message-container"></div>
            <div class="row">
                <div class="c-form__input-wrapper col-12">
                    <a class="styled-button s2" value="<?=Loc::getMessage('BUTTON_SAVE')?>" name="save"><?=Loc::getMessage('BUTTON_SAVE')?></a>
                </div>
            </div>
        </div>
        </div>
    </form>
<?endif;?>
    <script>
        BX.EternaLocationHandler.init(<?=CUtil::PhpToJSObject([
            'mainContainerId' => "profile-location-form",
            'locationFormId' => 'location-popup',
            'ajaxPath' => $arResult["AJAX_PATH"],
        ])?>);
    </script>
<?endif;?>

