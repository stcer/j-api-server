<?php

namespace j\api\client;

use j\api\base\HttpClient as Client;
use Exception;

/**
 * Class SwooleYar
 * @package j\api\client
 */
class SwooleHttp extends Base {

    /**
     * @var string
     */
    public static $serverUrl = '';

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
     * @throws Exception
     */
    public function callApi($api, $args = [], $init = array()){
        $url = $this->getRemoteUrl('/api/' . str_replace('.', '/', $api), 'path');
        $query = http_build_query(['args' => $args, 'init' => $init]);
        return $this->request($url, $query);
    }

    /**
     * @param $request
     * @return mixed
     * @throws Exception
     */
    public function asyncCalls($request){
        $this->formatRequests($request);
        $url = $this->getRemoteUrl('/api/calls', 'path');
        $query = http_build_query(['data' => $request]);
        $data = $this->request($url, $query);
        $tmp = [];
        foreach($data as $key => $value) {
            if($value['code'] == 200){
                $tmp[$key] = $value['data'];
            }
        }
        return $tmp;
    }

    /**
     * @param $request
     * @return array
     */
    public function calls($request){
        return $this->asyncCalls($request);
    }

    /**
     * @param $url
     * @param $query
     * @return mixed
     * @throws Exception
     */
    protected function request($url, $query) {
        $http = new Client();

        // close CURL 100-continue
        // http://wiki.swoole.com/wiki/page/433.html
        $data = $http->post($url, $query, [
            CURLOPT_HEADER => 0,
            CURLOPT_HTTPHEADER => array('Expect:'),
            ]);
        
        //$data = $http->get($url . $query);
        $data = json_decode($data, true);

        if (is_array($data) && $data['code'] == 200) {
            return $data['data'];
        }

        throw new Exception($data['message']);
    }
}