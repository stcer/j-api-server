<?php

namespace j\api\base;

/**
 * Class HttpClient
 * @package j\http
 */
class HttpClient {

    public $lastHead = [];
    public $timeout = 2;

	/**
	 * @var self
	 */
    private static $instance;

	/**
	 * @return HttpClient
	 */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param $url
     * @param array $param
     * @param array $options
     * @param bool $autoReferer
     * @return mixed
     */
    function get($url, $param = [], $options = []){
//        if($autoReferer && !isset($set[CURLOPT_REFERER])){
//            $set[CURLOPT_REFERER] = 'http://www.baidu.com';
//            $set[CURLOPT_USERAGENT] = 'Baiduspider+(+http://www.baidu.com/search/spider.htm)';
//        }
        if($param){
            if(is_array($param)){
                $param = http_build_query($param);
            }
            $sp = strpos($url, '?') ? '?' : '&';
            $url .= $sp . $param;
        }

        return $this->send($url, 'get', [], $options);
    }

	/**
	 * @param $url
	 * @param array $param
	 * @param array $options
	 * @return mixed
	 */
    function post($url, $param = [], $options = []){
        if(!isset($options[CURLOPT_POST])){
            $options[CURLOPT_POST] = 1;
        }

        return $this->send($url, 'post', $param, $options);
    }

	/**
	 * @param $url
	 * @param $method
	 * @param array $payload
	 * @return array|mixed
	 */
    function requestJSON($url, $method, $payload = []) {
        $set = [
            CURLOPT_FORBID_REUSE => 0,
            CURLOPT_CUSTOMREQUEST => strtolower($method),
            ];

        if (is_array($payload) && count($payload) > 0) {
            $payload = json_encode($payload); // bug?
        }

        $response = $this->send($url, $method, $payload, $set, true);
        $data = json_decode($response, true);
        if (!$data) {
            $data = array(
            	'error' => $response,
	            "code" => $this->lastHead['http_code']
                );
        }

        return $data;
    }


    /**
     * @param $url
     * @param $method
     * @param array $param
     * @param array $options
     * @param bool $header
     * @return mixed
     * @throws \Exception
     */
    protected function send($url, $method, $param = [], $options = [], $header = false){
        $curl = curl_init();
        $this->initCurlOption($curl, $url, $param, $options);

        $response = curl_exec($curl);
	    if($response === false){
		    $errno = curl_errno($curl);
	    }

        if($header){
	        $this->lastHead = curl_getinfo($curl);
        }

        curl_close($curl);

        // error
        if(isset($errno)){
            $exception = new \Exception($this->errorMsg($errno, $url, $param));
            $exception->param = $param;
            $exception->method = $method;
            throw $exception;
        }

        return $response;
    }

	/**
	 * @param $ch
	 * @param $url
	 * @param $param
	 * @param $options
	 */
    protected function initCurlOption($ch, $url, $param, $options){
        // default options
        if(!isset($options[CURLOPT_TIMEOUT])){
            $options[CURLOPT_TIMEOUT] = $this->timeout;
        }

        if(!isset($options[CURLOPT_RETURNTRANSFER])){
            $options[CURLOPT_RETURNTRANSFER] = 1;
        }

        $options[CURLOPT_URL] = $url;

        // other options
        if($param){
            if(is_array($param)){
                $param = http_build_query($param);
            }
            $options[CURLOPT_POSTFIELDS] = $param;
        }

        if(stripos($url,"https://") !== false){
            $options[CURLOPT_SSL_VERIFYPEER] = false;
            $options[CURLOPT_SSL_VERIFYHOST] = false;
        }

        // init header
	    if(isset($options['headers'])){
		    $headers = $options['headers'];
		    unset($options['headers']);
	    } else {
		    $headers = [];
	    }

        // get http result
        foreach($options as $key => $value){
	        if(is_numeric($key)){
		        curl_setopt($ch, $key, $value);
	        } else {
		        $headers[] = "{$key}: {$value}";
	        }
        }

        if($headers){
	        foreach($headers as $key => $value){
		        if(!is_numeric($key)){
			        $headers[] = "{$key}: {$value}";
		        }
	        }
	        curl_setopt($ch, CURLOPT_HEADER, 1);
	        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
    }

	/**
	 * @param $errno
	 * @param $url
	 * @param array $payload
	 * @return string
	 */
    protected function errorMsg($errno, $url, $payload = []){
        /**
         * cUrl error code reference can be found here:
         * http://curl.haxx.se/libcurl/c/libcurl-errors.html
         */
        switch ($errno) {
            case CURLE_UNSUPPORTED_PROTOCOL:
                $error = "Unsupported protocol ";
                break;
            case CURLE_FAILED_INIT:
                $error = "Internal cUrl error?";
                break;
            case CURLE_URL_MALFORMAT:
                $error = "Malformed URL [$url] -d " . json_encode($payload);
                break;
            case CURLE_COULDNT_RESOLVE_PROXY:
                $error = "Couldnt resolve proxy";
                break;
            case CURLE_COULDNT_RESOLVE_HOST:
                $error = "Couldnt resolve host";
                break;
            case CURLE_COULDNT_CONNECT:
                $error = "Couldnt connect to host, ElasticSearch down?";
                break;
            case CURLE_OPERATION_TIMEOUTED:
                $error = "Operation timed out on [$url]";
                break;
            default:
                $error = "Unknown error";
                if ($errno == 0)
                    $error .= ". Non-cUrl error";
                break;
        }
        return $error;
    }

	/**
	 * @param $requests
	 * @return array
	 */
    function multiRequest($requests){
        $conn = array();
        $mh = curl_multi_init();
        foreach($requests as $i => $row){
            $conn[$i] = curl_init();
            if(!isset($row['param'])){
                $row['param'] = [];
            }

            if(!isset($row['set'])){
                $row['set'] = [];
            }

            $this->initCurlOption($conn[$i], $row['url'], $row['param'], $row['set']);
            curl_multi_add_handle ($mh,$conn[$i]);
        }

        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active and $mrc == CURLM_OK) {
            if (curl_multi_select($mh) != -1) {
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }

        $res = array();
        foreach ($requests as $i => $url) {
            $res[$i] = curl_multi_getcontent($conn[$i]);
            curl_close($conn[$i]);
        }

        return $res;
    }

	/**
	 * @param $urls
	 * @return array
	 */
    public static function curlMul($urls){
        $handle  = array(); $i = 0;
        $mh = curl_multi_init(); // multi curl handler
        foreach ($urls as $url) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_TIMEOUT, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_multi_add_handle($mh, $ch); // 把 curl resource 放进 multi curl
            $handle[$i++] = $ch;
        }

        $active = null;
        do {
            $mrc = curl_multi_exec ( $mh, $active ); //当无数据，active=true
        } while ( $mrc == CURLM_CALL_MULTI_PERFORM ); //当正在接受数据时

        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($mh) != -1) {
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }

        $headInfo = $rs = $errors = [];
        foreach ($handle as  $k => $conn) {
            $err = curl_error ( $conn );
            if($errors){
                $errors[] = $err;
            } else {
                $headInfo[] = curl_getinfo ( $conn ); //返回头信息
                $rs[] = curl_multi_getcontent ( $conn ); //返回头信息
            }
            curl_close($conn);
            curl_multi_remove_handle($mh, $conn);
        }
        curl_multi_close($mh);
        return [$headInfo, $rs, $errors];
    }
}