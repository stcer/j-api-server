<?php

namespace j\api\base;

/**
 * String handling class for utf-8 data
 * Wraps the phputf8 library
 * All functions assume the validity of utf-8 strings.
 *
 * @static
 * @package        j.Framework
 * @subpackage    Utilities
 * @since        1.5
 */
abstract class Strings {
    /**
     * @param $str
     * @return array|mixed|string
     */
    public static function toGbk($str){
        if(is_object($str)){
            $str = (array)$str;
        }
        return is_array($str)
            ? array_map(array(__CLASS__, __METHOD__), $str)
            : mb_convert_encoding($str, 'gbk', 'utf-8')
            ;
    }

    /**
     * @param $str
     * @param string $charset
     * @return array
     */
    public static function utf8($str, $charset = 'gbk') {
        if(is_array($str)){
            foreach ($str as $k => $v) {
                if(is_array($v)){
                    $str[$k] = self::utf8($v);
                }else{
                    $str[$k] = mb_convert_encoding($v, 'utf-8', $charset);
                }
            }
        }else{
            $str = mb_convert_encoding($str, 'utf-8', $charset);
        }
        return $str;
    }
}
