<?php
namespace Eterna;

use Bitrix\Main\Loader;

class Tools
{
    private static $IBLOCK_CATALOG_ID = 7;

    protected static $currencyDesignations = array(
        "USD" => array("POSITION" => "BEFORE", "VALUE" => "$"),
        "RUB" => array("POSITION" => "AFTER", "VALUE" => " Р"),
        "EUR" => array("POSITION" => "BEFORE", "VALUE" => "€"),
    );

    public static function get_string_between($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    public static function convertPrice($price, $from, $to)
    {
        $convertedPrice = \CCurrencyRates::ConvertCurrency($price, $from, $to);
        return self::formatPrice(number_format($convertedPrice, 2, '.', ' '), $to);
    }

    //\Eterna\Tools::addToLogWithDetails(["test"], "TITLE_TEST" ,"addToLogWithDetails");
    public static function addToLogWithDetails($obj, $objTitle = 'TITLE', $fileName = '')
    {
        self::addTolLog("------------------ " . date("d.m.Y H:i:s") . " ------------------" , $fileName);
        self::addTolLog($objTitle . ":", $fileName);
        self::addTolLog($obj, $fileName);
    }

    //\Eterna\Tools::addTolLog($obj, "logFileName")
    public static function addTolLog($obj, $key = '')
    {
        if (empty($key)) {
            $key = 'main';
        }

        $dump = print_r($obj, true) . "\r\n";
        $files = $_SERVER["DOCUMENT_ROOT"] . "/local/modules/eterna.master/log/" . $key . ".log";
        $fp = fopen($files, "a+");
        fwrite($fp, $dump);
        fclose($fp);
    }

    public static function convertString($str, $from = "CP1251", $to = "UTF-8")
    {
        return iconv($from, $to, $str);
    }

    function num2word($num, $words)
    {
        $num = $num % 100;
        if ($num > 19) {
            $num = $num % 10;
        }
        switch ($num) {
            case 1: {
                return($words[0]);
            }
            case 2: case 3: case 4: {
            return($words[1]);
        }
            default: {
                return($words[2]);
            }
        }
    }

    public static function isMobileDevice() {
        return preg_match('/ipad|iphone|android|mobile|touch/i',$_SERVER['HTTP_USER_AGENT']);
    }

    public static function serializeArray($object, $siteRootFilePath)
    {
        $filePath = $_SERVER["DOCUMENT_ROOT"] . $siteRootFilePath;
        $data = serialize($object);
        file_put_contents($filePath, $data);
    }

    public static function unserializeArray($siteRootFilePath)
    {
        $filePath = $_SERVER["DOCUMENT_ROOT"] . $siteRootFilePath;
        $data = file_get_contents($filePath);
        return unserialize($data);
    }

    public static function translitString($s)
    {
        $s = (string) $s; // преобразуем в строковое значение
        $s = strip_tags($s); // убираем HTML-теги
        $s = str_replace(array("\n", "\r"), " ", $s); // убираем перевод каретки
        $s = preg_replace("/\s+/", ' ', $s); // удаляем повторяющие пробелы
        $s = trim($s); // убираем пробелы в начале и конце строки
        $s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s); // переводим строку в нижний регистр (иногда надо задать локаль)
        $s = strtr($s, array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya','ъ'=>'','ь'=>''));
        $s = preg_replace("/[^0-9a-z-_ ]/i", "", $s); // очищаем строку от недопустимых символов
        $s = str_replace(" ", "-", $s); // заменяем пробелы знаком минус
        return $s; // возвращаем результат
    }

}
