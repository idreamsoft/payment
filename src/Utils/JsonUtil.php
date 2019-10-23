<?php
namespace Payment\Utils;

class JsonUtil
{
    public static function encode($array,$options=0)
    {
        defined('JSON_UNESCAPED_UNICODE') OR define('JSON_UNESCAPED_UNICODE', 'JSON_UNESCAPED_UNICODE');
        if(version_compare(PHP_VERSION,'5.4.0','<')){
            if($options=='JSON_UNESCAPED_UNICODE'){
                return self::cnjson_encode($array);
            }
            return json_encode($array);
        }else{
            return json_encode($array, JSON_UNESCAPED_UNICODE);
        }
    }
    public static function unicode_convert_encoding($code){
        return mb_convert_encoding(pack("H*", $code[1]), "UTF-8", "UCS-2BE");
    }
    public static function unicode_encode($value){
        return preg_replace_callback('/\\\\u([0-9a-f]{4})/i',array(self,'unicode_convert_encoding'),$value);
    }
    public static function cnjson_encode($array){
        $json = json_encode($array);
        $json = self::unicode_encode($json);
        return $json;
    }
}
