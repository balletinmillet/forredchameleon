<?
$subscriptions = [];
$allowSubscriptions = ["EMAIL", "SMS"];
foreach ($arResult["USER_INFO"]["customer"]["subscriptions"] as $index => $subscription) {
    if($subscription["topic"] != "AwaitingList")
    {
        $pointOfContact = strtoupper($subscription["pointOfContact"]);
        if(in_array($pointOfContact, $allowSubscriptions))
            $subscriptions[$pointOfContact] = $subscription;
    }
}
$arResult["SUBSCRIPTIONS"] = $subscriptions;

if(!array_key_exists("EMAIL", $arResult["SUBSCRIPTIONS"]))
{
    $arResult["SUBSCRIPTIONS"]["EMAIL"] = [
        "brand" => "Eterna",
        "pointOfContact" => "Email",
        "isSubscribed" => false,
    ];
}

if(!array_key_exists("SMS", $arResult["SUBSCRIPTIONS"]))
{
    $arResult["SUBSCRIPTIONS"]["SMS"] = [
        "brand" => "Eterna",
        "pointOfContact" => "Sms",
        "isSubscribed" => false,
    ];
}