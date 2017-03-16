<?php

namespace j\api\server;

use j\api\Exception;
use j\api\base\ArrayUtils;
use j\api\base\JsonOutput;
use j\api\base\Strings;

/**
 * Class AppJson
 * news.cat.name
 * news.search
 */
class FpmApp extends Base {

    public $request;
    public $response;

    public function run(){
        $req = $this->getRequest();
        $response = $this->getResponse();

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
        } catch (Exception $e){
            $info = $e->getInfo();
            $data = [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'errors' => $info,
                ];
        } catch (\Exception $e){
            $data = [
                'code' => $e->getCode(),
                'message' => $e->getMessage()
                ];
        }

        if(ArrayUtils::gav($req, 'debug')){
            var_dump($data);
        }

        $this->log("request: {$_SERVER['REQUEST_URI']}", 'debug');
        $this->log($data, 'debug');

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
            throw new Exception("Invalid api(1)", Exception::API);
        }
        return $api;
    }

    /**
     * @return mixed
     */
    protected function getRequest(){
        if(!isset($this->request)){
            $this->request = $_REQUEST;
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
