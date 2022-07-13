<?
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

if (class_exists('quantum_master')) {
    return;
}

Class quantum_master extends CModule
{
    var $MODULE_ID = "quantum.master";
    var $PARTNER_NAME;
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;
    var $MODULE_GROUP_RIGHTS = "Y";

    function quantum_master()
    {
        $arModuleVersion = array();

        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path."/version.php");


        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }

        $this->MODULE_NAME = Loc::getMessage('SCP_MODULE_NAME');
        $this->PARTNER_NAME = GetMessage("SCP_PARTNER_NAME");

        $this->MODULE_DESCRIPTION = Loc::getMessage("SCP_MODULE_DESCRIPTION");
    }

    function InstallFiles($arParams = array())
    {
        return true;
    }

    function UnInstallFiles()
    {
        return true;
    }

    function InstallDB($arParams = array())
    {
        return true;
    }


    function UnInstallDB($arParams = array())
    {
        return true;
    }

    function createDataStorage()
    {
        return true;
    }

    function deleteDataStorage(){
        return true;
    }

	/**
	 * Install event
	 *
	 * @return bool
	 */
	public function RegisterEvent()
	{
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->registerEventHandlerCompatible("main","onPageStart", $this->MODULE_ID, "\\Quantum\\Event", "onPageStart");
            return true;
	}

	/**
	 * Uninstall event
	 *
	 * @return bool
	 */
	public function UnRegisterEvent()
	{
      $eventManager = \Bitrix\Main\EventManager::getInstance();
      $eventManager->unRegisterEventHandler("main","onPageStart", $this->MODULE_ID, "\\Quantum\\Event", "onPageStart");
			return true;
	}

    function DoInstall()
    {
        global $DB, $APPLICATION, $step, $USER;
        if($USER->IsAdmin())
        {
            ModuleManager::registerModule($this->MODULE_ID);

	        $this->InstallFiles();
	        $this->RegisterEvent();
	        
            $APPLICATION->IncludeAdminFile(Loc::getMessage("SCP_BASE_MODULE_INSTALL_DO"), $_SERVER["DOCUMENT_ROOT"]."/local/modules/quantum.master/install/step.php");
        }
    }

    function DoUninstall()
    {
        global $DB, $APPLICATION, $step, $USER;
        if($USER->IsAdmin())
        {
            ModuleManager::unregisterModule($this->MODULE_ID);

	        $this->UnInstallFiles();
            $this->UnRegisterEvent();

            $APPLICATION->IncludeAdminFile(Loc::getMessage("SCP_BASE_MODULE_UNINSTALL_DO"), $_SERVER["DOCUMENT_ROOT"]."/local/modules/quantum.master/install/unstep.php");
        }
    }
}
?>