<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Loader;
use \Bitrix\Main\Application;
use \Bitrix\Main\Engine\Contract;
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\ActionFilter\Authentication;
use \Eterna\Handler\User;

/**
 * @var $APPLICATION CMain
 * @var $USER CUser
 */

Loc::loadMessages(__FILE__);

class ProfileLocations extends \CBitrixComponent implements Contract\Controllerable
{
    function executeComponent()
    {
        $this->arResult["COURIER_LOCATIONS"] = User::getUserLocations();
        $this->includeComponentTemplate();
    }

    public function saveLocationAction($arFields)
    {
        $addLocationResult = false;

        if(!empty($arFields["locations"]))
            $addLocationResult = User::addUserLocation($arFields["locations"]);

        return [
            "FIELDS" => $arFields,
            "STATUS" => $addLocationResult,
        ];
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
            'saveLocation' => [
                '-prefilters' => [
                    Authentication::class
                ]
            ],
        ];
    }

}