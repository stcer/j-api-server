<?php

namespace j\api\server;

use j\api\Exception;
use j\tool\ArrayUtils;
use j\api\JsonOutput;
use j\tool\Strings;

/**
 * Class AppJson
 * news.cat.name
 * news.search
 */
class FpmApp extends Base {

    public $request;
    public $response;

    public function run(){
        $this->log("request: {$_SERVER['REQUEST_URI']}", 'debug');

        $req = $this->getRequest();
        if($this->charset == 'gbk'){
            $req = Strings::toGbk($req);
        }

        try{
            $api = $this->getApi($req);
            $init = (array)ArrayUtils::gav($req, 'init');
            $args = (array)ArrayUtils::gav($req, 'args');
            $rs = $this->execute($api, $init, $args, $req);

            $data = [
                'code' => 200,
                'data' => $rs
                ];
        } catch (\Exception $e){
            $data = [
                'code' => $e->getCode(),
                'message' => $e->getMessage()
                ];
            if(method_exists($e, 'getInfo')){
                $data['errors'] = $e->getInfo();
            }

            // log error message
            $this->log($e->getTraceAsString(), 'error');
        }

        if(ArrayUtils::gav($req, 'debug')){
            var_dump($data);
        }

        $this->log($req, 'debug');
        $this->log($data, 'debug');

        $response = $this->getResponse();
        $response->send($data, ArrayUtils::gav($req, 'pretty', false), $this->charset);
    }

    /**
     * @params array $req
     * @return mixed|null|string
     * @throws Exception
     */
    protected function getApi($req){
        if(isset($req['q'])){
            $api = $req['q'];
        }else{
            $api = ArrayUtils::gav($req, 'api');
        }

        $api = preg_replace('#\\/#', '.', $api);
        if(!preg_match('/^[a-zA-Z\.]+$/', $api)){
            throw new Exception("Invalid api(1)", Exception::API_NOT_FOUND);
        }
        return $api;
    }

    /**
     * @return mixed
     */
    protected function getRequest(){
        if(!isset($this->request)){
            $this->request = $_REQUEST;
            $this->normalizesRequest($this->request);
        }
        return $this->request;
    }

    /**
     * @return JsonOutput
     */
    protected function getResponse(){
        if(!isset($this->response)){
            $this->response = new JsonOutput();
        }
        return $this->response;
    }
}
