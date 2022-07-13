<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var array $arParams
 * @var array $arResult
 * @var SaleOrderAjax $component
 */

$component = $this->__component;
$component::scaleImages($arResult['JS_DATA'], $arParams['SERVICES_IMAGES_SCALING']);

if($_REQUEST["via_ajax"] != "Y")
    \Eterna\Integration\Lamoda\Delivery::setDelivery([]);


$userDetails = \Eterna\Handler\User::getUserDetails();
if(!empty($userDetails))
{
    foreach ($arResult["JS_DATA"]["ORDER_PROP"]["properties"] as $index => $item)
    {
        if($item["NAME"] == "Имя")
            $arResult["JS_DATA"]["ORDER_PROP"]["properties"][$index]["VALUE"][] = $userDetails["NAME"];
        if($item["NAME"] == "Фамилия")
            $arResult["JS_DATA"]["ORDER_PROP"]["properties"][$index]["VALUE"][] = $userDetails["LAST_NAME"];
    }
}

$customJsData = [];
$customJsData["selectedDeliveryId"] = 0;
$locationProperties = ["CITY", "STREET", "BUILDING"];
$hiddenProperties = ["DELIVERY_INTERVAL_ID", "LAMODA_ORDER_ID"];
foreach ($arResult["ORDER_PROP"]["USER_PROPS_N"] as $index => $item)
{
    if(in_array($item["CODE"], $locationProperties))
    {
        $customJsData["LOCATION"]["INPUTS"]["LIST_ID"][] = "soa-property-" . $item["ID"];
        $customJsData["LOCATION"]["INPUTS"]["soa-property-" . $item["ID"]] = $item;
    }

    if(in_array($item["CODE"], $hiddenProperties))
        $customJsData["PROPERTIES"]["HIDDEN"][] = "soa-property-" . $item["ID"];

    if($item["CODE"] == "LAMODA_ORDER_ID")
        $customJsData["PROPERTIES"]["LAMODA_ORDER_ID"] = "soa-property-" . $item["ID"];

    if($item["CODE"] == "DELIVERY_INTERVAL_ID")
        $customJsData["PROPERTIES"]["INTERVAL_INPUT_ID"] = "soa-property-" . $item["ID"];

    if($item["CODE"] == "KLADR_CODE")
        $customJsData["PROPERTIES"]["KLADR_CODE"] = "soa-property-" . $item["ID"];

    if($item["CODE"] == "COMMENT")
        $customJsData["PROPERTIES"]["COMMENT"] = "soa-property-" . $item["ID"];

}

$arResult["CUSTOM_JS_DATA"] = $customJsData;




