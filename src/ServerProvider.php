<?php

namespace j\api;

use j\api\base\ArrayUtils;
use j\api\server\Base;
use j\apiDoc\AppServer;
use j\apiDoc\Document;
use j\di\Container;
use j\di\ServiceProviderInterface;

use j\api\server\FpmApp as FpmApp;
use j\api\server\FpmYar as YarFpm;
use j\api\server\SwooleHttp as HttpSwoole;
use j\api\server\SwooleYar as YarSwoole;
use j\api\server\SwooleTcp as TcpSwoole;

use j\network\http\Server as HttpServer;
use j\network\tcp\Server as TcpServer;
use ReflectionClass;
use syar\Server as YarServer;

use j\error\Exception;
use j\log\Log;
use j\log\File as FileLog;
use j\base\Config;

class ServerProvider implements ServiceProviderInterface{

    /**
     * @var Config
     */
    protected $config;

    protected static $loader;

    /**
     * @param Base $app
     */
    protected function initApp($app){
        $app->isInner = true;
        $app->loader = $this->getLoader();
        $app->setLogger($this->getApiLog());
    }

    protected function getLoader(){
        if(!isset(self::$loader)){
            $loader = self::$loader = Loader::getInstance();
            $loader->setNsPrefix($this->getConf('apiServer.ns'));
            $loader->setClassSuffix($this->getConf('apiServer.classSuffix' , ''));
        }
        return self::$loader;
    }

    protected function getApiLog(){
        $file = $this->getConf('apiServer.logFile', PATH_ROOT . '/tmp/log/api.log');
        $log = new FileLog($file);
        $log->setMask($this->getConf('apiServer.logMode', 31));
        return $log;
    }

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $container A container instance
     * @throws
     */
    public function register($container){
        $this->config = $container->get('config');

        $container->set('httpFpm', function(){
            $app = new FpmApp();
            $this->initApp($app);
            return $app;
        });

        $container->set('yarFpm', function(){
            $app = new YarFpm();
            $this->initApp($app);
            return $app;
        });

        $container->set('httpSwoole', function(){
            $options = $this->getConf('apiServer.http', $this->getConf('apiServer.httpSwoole'));
            return $this->createServer(HttpSwoole::class, $options);
        });

        $container->set('yarSwoole', function(){
            $options = $this->getConf('apiServer.yar', $this->getConf('apiServer.yarSwoole'));
            return $this->createServer(YarSwoole::class, $options);
        });

        $container->set('apiServer.tcp', function(){
            $options = $this->getConf('apiServer.tcp');
            return $this->createServer(TcpSwoole::class, $options);
        });

        $container->set('apiServer.doc', function(){
            $options = $this->getConf('apiServer.doc');
            return $this->createDocServer($options);
        });
    }
    
    protected function getConf($key, $def = null){
        return $this->config->get($key, $def);
    }

    /**
     * @param $className
     * @param $options
     * @return HttpSwoole|YarSwoole|TcpSwoole
     * @throws Exception
     */
    protected function createServer($className, $options){
        if(!$options){
            throw new Exception("Invalid config for apiServer.tcp");
        }

        $server = new $className();
        if(isset($options['port'])){
            $server->port = $options['port'];
        }
        if(isset($options['host'])){
            $server->host = $options['host'];
        }

        $server->options['pid_file'] = $options['pid'];
        $server->options['daemonize'] = $options['daemonize'];
        $server->onServerCreate = function($server) use($options){
            /**
             * @var TcpServer|YarServer|HttpServer $server
             */
            $server->setLogger($this->getServerLog($options));
            if(isset($options['task_worker_num'])){
                $server->setOption('task_worker_num', $options['task_worker_num']);
            }
            if(isset($options['worker_num'])){
                $server->setOption('worker_num', $options['worker_num']);
            }
            if(isset($options['max_request'])){
                $server->setOption('max_request', $options['max_request']);
            }

            if(isset($options['swoole'])){
                $server->setOption($options['swoole']);
            }
        };

        $this->initApp($server);
        return $server;
    }

    /**
     * @param $options
     * @return FileLog|Log|null
     */
    protected function getServerLog($options){
        if(isset($options['debug']) && $options['debug']){
            return new Log();
        } elseif(isset($options['log'])) {
            return new FileLog($options['log']);
        }
        return null;
    }

    /**
     * 创建 doc server
     * @param $options
     * @return AppServer
     * @throws
     */
    protected function createDocServer($options){
        $apiPath = $options['baseDir'];
        $apiFileSuffix = $options['fileSuffix'];
        $testUrl = $options['testUrl'];
        $port = ArrayUtils::gav($options, 'port');
        $host = ArrayUtils::gav($options, 'host');

        $loader = $this->getLoader();
        $doc = new Document($apiPath, $apiFileSuffix);
        $doc->setClassMaker(function($api) use ($loader){
            // 使用loader生成api class
            return $loader->getClass($api, [], true);
        });

        $server = new AppServer();
        if($host){
            $server->host = $host;
        }
        if($port){
            $server->port = $port;
        }

        $server->docMaker = $doc;
        $server->testUrl = $testUrl;
        $server->setCallback('document.getApiDocument', function($api, $args, $initParams) use($loader, $doc){
            // api class 文档
            $object = $loader->getClass($args['name'], $initParams);
            $class = get_class($object);
            $data = $doc->getApiDocument($class);

            // 获取 api.model 接口文档
            if($object instanceof Base
                && method_exists($object, 'getModel')
                && ($model = $object->getModel())
            ){
                $refClass = new ReflectionClass(get_class($model));
                $data['document'] .= "\n" . $refClass->getDocComment();

                // 允许暴露的api
                $allows = null;
                if(method_exists($object, 'getDefaultModelMethods')){
                    $allows = $object->getDefaultModelMethods();
                }

                // 合并 model的method
                $methods = $doc->docGenerator->genMethodDocuments($refClass, $allows);
                $data['method'] = array_merge($data['method'], $methods) ;
            }
            return $data;
        });
        $server->onServerCreate = function($server) use($options){
            /** @var HttpServer $server */
            $server->setLogger($this->getServerLog($options));
            if(isset($options['swoole'])){
                $server->setOption($options['swoole']);
            }
        };
        return $server;
    }
}