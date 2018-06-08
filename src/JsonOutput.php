<?php

namespace j\api;

use j\tool\JsonPretty;

/**
 * Class AppJson
 * news.cat.name
 * news.search
 */
class JsonOutput{
    function send($data, $pretty = false, $charset = 'utf8'){
        if($charset == 'gbk'){
            $data = self::utf8($data);
        }

        $json = json_encode($data);
        if($pretty){
            $json = JsonPretty::indent($json);
        }

        $json = str_replace('"{}"', '{}', $json);
        if(isset($_REQUEST['callback']) && ($callback = $_REQUEST['callback']) && is_string($callback)){
            header('Content-type: application/x-javascript');
            echo $callback ."({$json});";
        } else {
            header('Content-type: application/json');
            echo $json;
        }
    }

    public static function utf8($str, $charset = 'gbk') {
        if(is_array($str)){
            foreach ($str as $k => $v) {
                if(is_array($v)){
                    $str[$k] = self::utf8($v);
                }elseif(!is_object($v)){
                    $str[$k] = mb_convert_encoding($v, 'utf-8', $charset);
                }
            }
        }else{
            $str = mb_convert_encoding($str, 'utf-8', $charset);
        }
        return $str;
    }
}
