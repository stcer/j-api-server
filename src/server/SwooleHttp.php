<?php

namespace j\api\server;

use Exception;
use j\network\http\Server;
use j\network\http\Request;
use j\network\http\Response;
use j\api\base\JsonPretty;
use j\api\base\Strings;

/**
 * Class SwooleHttp
 * @package j\api
 *
 */
class SwooleHttp extends Base {
    /**
     * bind ip address
     * @var string
     */
    public $host = '0.0.0.0';
    public $port = '8061';
    public $options = [];

    /**
     * @var Server
     */
    protected $server;

    /**
     * @var callback
     */
    public $onServerCreate;

    /**
     * @param $request
     * @param $api
     * @return array
     */
    function process($request, $api) {
        if ($this->charset == 'gbk') {
            $request = Strings::toGbk($request);
        }

        $api = str_replace('/', ".", $api);
        $init = isset($request['init']) ? $request['init'] : array();
        $args = isset($request['args']) ? $request['args'] : array();
        if(!$args && isset($request['params'])){
            $args = $request['params'];
        }

        try {
            $this->log($api, 'debug');
            $rs = $this->execute($api, $init, $args, $request);
            $data = [
                'code' => 200,
                'data' => $rs
            ];
            $this->log($data, 'debug');
            return $data;
        } catch (Exception $e) {
            $data = [
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ];
            $this->log($e->getMessage(), 'error');
            $this->log($e->getTraceAsString(), 'error');
            return $data;
        }
    }

    /**
     * 启动服务运行
     * @param null $log
     */
    public function run($log = null){
        $server = $this->server = new Server($this->host, $this->port);

        $server->cgiPathPrefix = "/api/";
        $server->dynamicParser = array($this, 'handle'); // 动态请求调试
        $server->setOption($this->options);

        // reg task for calls
        $server->regTask('process', array($this, 'process'));

        if(is_callable($this->onServerCreate)){
            call_user_func($this->onServerCreate, $server);
        }

        $server->run();
    }

    /**
     * @param string $actionPath
     * @param Request $request
     * @param Response $response
     * @throws Exception
     */
    public function handle($request, $response, $actionPath){
        //$timer = new Timer();
        $api = trim($actionPath, '/');
        $api = trim($api, '?');
        $api = trim($api, '&');
        $reqArgs = $this->getRequestParams($request);

        if($api == 'calls'){
            // 批量请求
            if(!isset($reqArgs['data']) || !($data = $reqArgs['data'])){
                throw new Exception("Invalid request for calls");
            }

            $requests = [];
            foreach($data as $index => $call) {
                if(!isset($call['api'])){
                    throw new Exception("Invalid request api for calls");
                }
                $requests[] = ['process', [$call, $call['api']]];
            }

            $taskManager = $this->server->taskManager;
            $taskManager->doTasks($requests, function($data, $context) {
                list($request, $response) = $context;
                $this->response($request, $response, ['code' => 200, 'data' => $data]);
            }, [$request, $response]);

            return;
        }

        if(!$api){
            if(isset($reqArgs['api'])){
                $api = $reqArgs['api'];
            } else {
                throw new Exception("Invalid request");
            }
        }

        $data = $this->process($reqArgs, $api);
        $this->response($request, $response, $data);
//        echo $timer->stop() . "\n";
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function getRequestParams($request){
        $args = $request->get;
        if(isset($request->post) && $request->post) {
            $args = $request->post + $args;
        }
        $this->normalizesRequest($args);
        return $args;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $data
     */
    protected function response($request, $response, $data) {
        if ($this->charset == 'gbk') {
            $data = Strings::utf8($data);
        }

        $data = json_encode($data);
        if ($request->get('pretty')) {
            $data = JsonPretty::indent($data);
        }

        $data = str_replace('"{}"', '{}', $data);
        if ($callback = $request->get("callback")) {
            $data = $callback . "({$data});";
            $response->headerContentType("application/x-javascript");
        } else {
            $response->headerContentType("application/json");
        }
        $response->send($data);
    }
}