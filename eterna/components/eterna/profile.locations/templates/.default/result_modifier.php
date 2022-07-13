<?
foreach ($arResult["COURIER_LOCATIONS"] as $index => $location)
{
    $displayAddress = "";
    if(!empty($location["CITY"]["value"]))
        $displayAddress .= $location["CITY"]["value"];
    if(!empty($location["STREET"]["value"]))
        $displayAddress .= " " . $location["STREET"]["value"];
    if(!empty($location["BUILDING"]["value"]))
        $displayAddress .= " " . $location["BUILDING"]["value"];
    if(!empty($location["HOUSING"]["value"]))
        $displayAddress .= " " . $location["HOUSING"]["value"];
    if(!empty($location["APARTMENT"]["value"]))
        $displayAddress .= "/" . $location["APARTMENT"]["value"];

    $arResult["COURIER_LOCATIONS"][$index]["DISPLAY_ADDRESS"] = $displayAddress;
}