<?php
namespace Quantum;

use Bitrix\Main\Loader;

class Tools
{
    public static function addTolLog($obj, $key = '')
    {
        if (empty($key)) {
            $key = 'main';
        }

        $dump = print_r($obj, true) . "\r\n";
        $files = $_SERVER["DOCUMENT_ROOT"] . "/local/modules/quantum.master/log/" . $key . ".log";
        $fp = fopen($files, "a+");
        fwrite($fp, $dump);
        fclose($fp);
    }

    public static function isMobileDevice() {
        return preg_match('/ipad|iphone|android|mobile|touch/i',$_SERVER['HTTP_USER_AGENT']);
    }



}
