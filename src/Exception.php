<?php

namespace j\api;

use j\api\base\Exception as AException;

/**
 * Class Exception
 * @package j\api
 */
class Exception extends AException {
    const UNKNOWN = 4000; // 参数错误
    const Arguments = 4001; // 参数错误
    const SIGN = 4002; // 错误签名
    const API = 4003; // 无效API
    const UPLOAD_FILE = 4201; // 无文件上传
    const UPLOAD_SIZE = 4202; // 尺寸过大
    const UPLOAD_SIZE1 = 4212; // 尺寸过大
    const UPLOAD_EXT = 4203; // 扩展名错误
    const UPLOAD_SAVE = 4204; // 扩展名错误
    const UPLOAD_FREE = 4205; // 没有空间
    const UAuthentication = 403; // 验证失败
    const ULOCK = 4031; // 会员锁定
    const NOT_FOUND = 404; // 信息查询失败
    const Validate = 5001; // 数据验证失败

    /**
     * Exception constructor.
     * @param string $message
     * @param int $code
     * @param array $info
     * @param AException|null $previous
     */
    public function __construct(
        $message = "", $code = 0,
        $info = [], AException $previous = null
    ) {
        AException::__construct($message, $code, $previous);
        $this->setInfo($info);
    }
}