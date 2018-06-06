# 设计

## 请求参数打包方式
*   http 请求, 通过http_build_query将 args, init参数进行编码， 解决参数为数组时的情况
*   tcp client默认使用 MSGPACK 方式编码数据包
*   yar默认支持 MSGPACK, json, php序列化

## 字符编码
*   服务端api接口数据编码方式受 $server->charset控制
*   所有客户端的请求参数为utf8编码

## 地址格式
*   Fpm http : server_url?api=&args=&init=
*   Swoole http: 
    1. server_url/api/API_NAME?args=&init= 
    2. server_url/calls?data=REQUESTS
*   Swoole yar: (method=yar)
    1. server_url/API_NAME?args=&init= 
    2. server_url/multiple


## 命令行工具

```
php apiServer.php [options]

Options:
    -h, print this message
    -v, debug mode
    -d, run as a daemonize mode
    -a <action>, 
        stop: stop the server 
        restart: restart the server
        status: show status
    -t <target>,
        doc: document server
        yar: yar server
        http: http server
        tcp: tcp server

# http server
php ../bin/apiServer.php -b init.inc.php -a start -t http -d

# doc server
php bin/apiServer.php -t doc -a start -b example/init.inc.php -v

```

## 配置

```
$_binDir = PATH_ROOT . '/bin/';
$_tmpDir = PATH_ROOT . '/tmp/';

return [
   'ns' => 'api\\action\\',
   'classSuffix' => '',
   'logFile' => $_tmpDir . '/log/api.log',
   'logMode' => 31,

   'doc' => [
       'port' => 8500,
       'host' => '0.0.0.0',
       'baseDir' => __DIR__ . '/action/',
       'fileSuffix' => '/Service.php$/',
       'testUrl' => 'http://w.api.jz.x2.cn/index.php?api=%action%',
   ],

   'http' => [
       'debug' => 1,
       'port' => 8501,
       'host' => '0.0.0.0',
       'daemonize' => false,
       'pid' => $_tmpDir . "/pid/http.pid",
       'log' => $_tmpDir . '/log/http_swoole.log',
       'swoole' => [
           'worker_num' => 10,
           'task_worker_num' => 10,
           'package_max_length' => 1024 * 4,
           'max_request' => 10,
       ]
   ],

   'yar' => [
       'debug' => 1,
       'port' => 8502,
       'host' => '0.0.0.0',
       'daemonize' => false,
       'pid' => $_tmpDir . "/pid/yar.pid",
       'log' => $_tmpDir . '/log/yar_swoole.log',
       'swoole' => [
           'worker_num' => 10,
           'max_request' => 20,
           'task_worker_num' => 10,
       ]
   ],

   'tcp' => [
       'debug' => 1,
       'port' => 8503,
       'host' => '0.0.0.0',
       'daemonize' => false,
       'pid' => $_tmpDir . "/pid/tcp.pid",
       'log' => $_tmpDir . '/log/tcp.log',
       'swoole' => [
           'worker_num' => 10,
           'max_request' => 20,
           'task_worker_num' => 10,
       ]
   ],
];
```

## 测试

```
# 启动测试服务
php bin/apiServer.php -t http -a start -b example/init.inc.php
php bin/apiServer.php -t tcp -a start -b example/init.inc.php 
php bin/apiServer.php -t yar -a start -b example/init.inc.php

# 运行测试
php example/
```

## 性能测试

```
# 完成44次远程设计
php example/benchmark.php

array(5) {
  ["http"]=> float(0.42368),        nginx + fpm + php7
  ["yar"]=> float(0.45212),         nginx + fpm + php7 + ext-yar
  ["httpSwoole"]=> float(0.0587),   php7 + swoole2.2
  ["yarSwoole"]=> float(0.07076),   php7 + swoole2.2
  ["tcp"]=>  float(0.06179),        php7 + swoole2.2
}
```

## TODO

1. 版本支持, 参考api-simple
2. mock数据支持, 参考 api-simple
3. server 日志, 错误及调试日志


## 异常记录

注意文档编码

```
未设置task_worker_nums
 WARNING swManager_check_exit_status: worker#6 abnormal exit, status=0, signal=11
```