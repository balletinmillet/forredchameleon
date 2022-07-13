<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use \Bitrix\Main\Loader;
use \Bitrix\Main\Application;
use \Bitrix\Main\Engine\Contract;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\ActionFilter\Authentication;
use \Eterna\Handler\User as EternaUser;
use \Eterna\Integration\MindBox\Loyalty\User as LoyaltyUser;
use \Eterna\Integration\MindBox\User as MindBoxUser;
use \Eterna\Integration\MindBox\Api;

/**
 * @var $APPLICATION CMain
 * @var $USER CUser
 */

Loc::loadMessages(__FILE__);

class EternaSubscription extends \CBitrixComponent implements Contract\Controllerable
{
    protected $pointOfContacts = ["Sms", "Email", "Viber", "MobilePush", "WebPush",];

    function executeComponent()
    {
        $userInfo = LoyaltyUser::getDataBySiteInfo();
        if(empty($userInfo["customer"]["subscriptions"]))
            $userInfo["customer"]["subscriptions"] = $this->getDefaultSubscriptionObject();

        $this->arResult["USER_INFO"] = $userInfo;
        $this->includeComponentTemplate();
    }

    public function saveSubscriptionAction($arFields)
    {
        $subscriptions = [];
        $result = [];
        if (!empty($arFields["subscriptions"])) {
            foreach ($arFields["subscriptions"] as $index => $subscription)
                $subscriptions[] = $subscription;

            $userDetails = EternaUser::getUserDetails();
            if(!empty($userDetails))
            {
                $userDetails["MINDBOX_SUBSCRIPTIONS"] = $subscriptions;
                $result["USER_DETAILS"] = MindBoxUser::upated($userDetails);
                $result["EDIT_RESULT"] = Api::send("Website.EditCustomer", $result["USER_DETAILS"], "sync", false);
            }
        }

        return $result;
    }

    function getDefaultSubscriptionObject(): array
    {
        $subscriptionObject = [];
        foreach ($this->pointOfContacts as $pointOfContact)
        {
            $subscriptionObject[] = [
                "brand" => "Eterna",
                "pointOfContact" => $pointOfContact,
                "topic" => "AwaitingList",
                "isSubscribed" => true,
            ];
        }

        return $subscriptionObject;
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
            'saveSubscription' => [
                '-prefilters' => [
                    Authentication::class
                ]
            ],
        ];
    }

}