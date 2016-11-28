<?php

namespace j\api\client;

use Yar_client;
use Yar_Concurrent_Client;

/**
 * Class SwooleYar
 * @package j\api\client
 */
class Yar extends BaseAbstract {

    /**
     * @var string
     */
    public static $serverUrl = 'http://jzf.x1.cn/api/www/yar';

    /**
     * @var string
     */
    public $serverAddress = '';

    /**
     * @var static[]
     */
    static $instance;

    /**
     * @param $api
     * @param $args
     * @param array $init
     * @return mixed
     */
    public function callApi($api, $args, $init = array()) {
        $query = http_build_query(['init' => $init]);
        $url = ($this->serverAddress ?: static::$serverUrl) . "?api={$api}&{$query}";
        $client = new Yar_client($url);
        return call_user_func_array(array($client, "yar"), $args);
    }

    /**
     * @param $request
     * @return array
     */
    public function asyncCalls($request) {
        $this->formatRequests($request);

        $data = [];
        $server = ($this->serverAddress ?: static::$serverUrl);
        foreach ($request as $i => $r) {
            $url = $server . "?api={$r['api']}";
            Yar_Concurrent_Client::call($url, 'yar', $r['params'],
                function ($rs) use ($i, &$data) {
                    $data[$i] = $rs;
                });
        }

        Yar_Concurrent_Client::loop();
        return $data;
    }

    /**
     * @param $request
     * @return array
     */
    public function calls($request) {
        return $this->asyncCalls($request);
    }
}