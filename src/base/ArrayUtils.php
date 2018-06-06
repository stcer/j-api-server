<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace j\api\base;

use Traversable;
use Exception;

/**
 * Utility class for testing and manipulation of PHP arrays.
 *
 * Declared abstract, as we have no need for instantiation.
 */
abstract class ArrayUtils {
    /**
     * @param $arr
     * @param $key
     * @param null $def
     * @return mixed|null
     */
    public static function gav($arr, $key, $def = null) {
        if(is_array($arr)){
            return array_key_exists($key, $arr) ? $arr[$key] : $def;
        }else if(is_object($arr)){
            return $arr[$key] ? $arr[$key] : $def;
        } else {
            throw(new Exception('argument arr is not a array'));
        }
    }
}
