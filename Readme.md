# problem
*   注意文档编码

# 请求参数打包方式
*   http 请求, 通过http_build_query将 args, init参数进行编码， 解决参数为数组时的情况
*   tcp client默认使用 MSGPACK 方式编码数据包
*   yar默认支持 MSGPACK, json, php序列化

# 字符编码
*   服务端api接口数据编码方式受 $server->charset控制
*   所有客户端的请求参数为utf8编码

# 地址格式
*   Fpm http : server_url?api=&args=&init=
*   Swoole http: 
    1. server_url/api/API_NAME?args=&init= 
    2. server_url/calls?data=REQUESTS
*   Swoole yar: (method=yar)
    1. server_url/API_NAME?args=&init= 
    2. server_url/multiple


# 默认文档

swoole http server自带默认文档, 服务端可做如下配置
```
$server->getDocReader()->setApiPath(__DIR__ . '/action/');
$server->getDocReader()->apiFilePattern = '/Service.php$/';
$server->getLoader()->classSuffix = 'Service';
$server->testUrl = 'http://api.j7.x1.cn/?api=%action%';
```