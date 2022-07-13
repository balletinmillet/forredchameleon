<?
if($_GET["catalog_import"] != 1)
{
    @set_time_limit(0);
    @ignore_user_abort(true);
}

use Bitrix\Main\Localization\Loc;
use	Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
Loc::loadMessages(__FILE__);
$request = HttpApplication::getInstance()->getContext()->getRequest();
$module_id = htmlspecialcharsbx($request["mid"] != "" ? $request["mid"] : $request["id"]);
Loader::includeModule($module_id);

global $APPLICATION;

$aTabs = array(
	array(
		"DIV" 	  => "edit",
		"TAB" 	  => Loc::getMessage("SCP_CATALOG_IMPORT"),
		"TITLE"   => Loc::getMessage("SCP_CATALOG_IMPORT"),
	),
);

$tabControl = new CAdminTabControl(
	"tabControl",
	$aTabs
);


$tabControl->Begin();?>
    <form action="<? echo($APPLICATION->GetCurPage()); ?>?mid=<? echo($module_id); ?>&lang=<? echo(LANG); ?>" method="post" name="form_opt" id="form_opt">
        <?$tabControl->BeginNextTab();?>
        <?if($_GET["catalog_import"] != 1):?>
        <tr>
            <td width="40%" class="adm-detail-content-cell-l"><? echo(Loc::GetMessage("SCP_CATALOG_IMPORT_FILE_PATH_TITLE")); ?></td>
            <td width="60%" class="adm-detail-content-cell-r">
                <input type="text" id="CATALOG_IMPORT_FILE_PATH" name="CATALOG_IMPORT_FILE_PATH" size="30" value="<?=\COption::GetOptionString($module_id, "CATALOG_IMPORT_FILE_PATH", "");?>">
                <input type="button" value="<? echo(Loc::GetMessage("SCP_CHOSE_FILE")); ?>" onclick="BtnClick(1)">
            </td>
        </tr>
        <tr>
        <?endif;?>

            <?
            if($_GET["catalog_import"] == 1)
            {
                \Eterna\Import\Catalog::load();
            }

            ?>
        </tr>


        <?$tabControl->Buttons(); ?>
        <?if($_GET["catalog_import"] != 1):?>
            <input type="submit" name="apply" value="<? echo(Loc::GetMessage("SCP_BUTTON_SAVE")); ?>" class="adm-btn-save" />
            <input type="submit" name="import" value="<? echo(Loc::GetMessage("SCP_BUTTON_LOAD")); ?>" class="adm-btn-save" />
        <?else:?>
        <input type="submit" name="to_import_catalog" value="<? echo(Loc::GetMessage("SCP_BUTTON_TO_CATALOG_SETTINGS")); ?>" class="adm-btn-save" />
         <?endif;?>
        <? echo(bitrix_sessid_post()); ?>

    </form>
<?
$tabControl->End();
?>

<?
/*
 * --------------------------------------------------------------------------
 * Сохранения параметров модуля
 * */

if($request->isPost() && check_bitrix_sessid()){
    
    if($request["to_import_catalog"])
        LocalRedirect($APPLICATION->GetCurPage()."?mid=".$module_id."&lang=".LANG);

    if($request["import"])
        LocalRedirect($APPLICATION->GetCurPage()."?mid=".$module_id."&catalog_import=1"."&lang=".LANG);

    if($request["apply"])
    {
        $optionKeys = ["CATALOG_IMPORT_FILE_PATH"];
        foreach ($optionKeys as $optionKey)
        {
            $optionValue = $request->getPost($optionKey);
            if($optionValue){
                \COption::SetOptionString($module_id, $optionKey, $optionValue);
            }
        }
    }

    LocalRedirect($APPLICATION->GetCurPage()."?mid=".$module_id."&lang=".LANG);
}
?>

<script>
    var mess_SESS_EXPIRED = 'Ошибка файлового диалога: Сессия пользователя истекла';
    var mess_ACCESS_DENIED = 'Ошибка файлового диалога: У вас недостаточно прав для использования диалога выбора файла';
    window.BtnClick = function(bLoadJS, Params)
    {
        if (!Params)
            Params = {};

        var UserConfig;
        UserConfig =
            {
                site : 'ru',
                path : '/upload',
                view : "list",
                sort : "type",
                sort_order : "asc"
            };
        if (!window.BXFileDialog)
        {
            if (bLoadJS !== false)
                BX.loadScript('/bitrix/js/main/file_dialog.js');
            return setTimeout(function(){window['BtnClick'](false, Params)}, 50);
        }

        var oConfig =
            {
                submitFuncName : 'BtnClickResult',
                select : 'F',
                operation: 'S',
                showUploadTab : true,
                showAddToMenuTab : false,
                site : 'ru',
                path : '/upload',
                lang : 'ru',
                fileFilter : 'csv',
                allowAllFiles : true,
                saveConfig : true,
                sessid: "<?=bitrix_sessid();?>",
                checkChildren: true,
                genThumb: true,
                zIndex: 2500				};

        if(window.oBXFileDialog && window.oBXFileDialog.UserConfig)
        {
            UserConfig = oBXFileDialog.UserConfig;
            oConfig.path = UserConfig.path;
            oConfig.site = UserConfig.site;
        }

        if (Params.path)
            oConfig.path = Params.path;
        if (Params.site)
            oConfig.site = Params.site;

        oBXFileDialog = new BXFileDialog();
        oBXFileDialog.Open(oConfig, UserConfig);
    };
    window.BtnClickResult = function(filename, path, site, title, menu)
    {
        path = jsUtils.trim(path);
        path = path.replace(/\\/ig,"/");
        path = path.replace(/\/\//ig,"/");
        if (path.substr(path.length-1) == "/")
            path = path.substr(0, path.length-1);
        var full = (path + '/' + filename).replace(/\/\//ig, '/');
        if (path == '')
            path = '/';

        var arBuckets = [];
        if(arBuckets[site])
        {
            full = arBuckets[site] + filename;
            path = arBuckets[site] + path;
        }

        if ('F' == 'D')
            name = full;

        document.form_opt.CATALOG_IMPORT_FILE_PATH.value = full;
        BX.fireEvent(document.form_opt.CATALOG_IMPORT_FILE_PATH, 'change');
    };
    if (window.jsUtils)
    {
        jsUtils.addEvent(window, 'load', function(){jsUtils.loadJSFile('/bitrix/js/main/file_dialog.js');}, false);
    }
</script>
