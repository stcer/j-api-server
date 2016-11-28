# problem
*   注意文档编码

# 客户端请求数据打包方式
*   http 请求, 通过http_build_query将 args, init参数进行编码， 解决参数为数组时的情况
*   tcp client默认使用 MSGPACK 方式编码数据包
*   yar默认支持 MSGPACK, json, php序列化

# 字符编码
*   服务端api接口数据编码方式受 $server->charset控制
*   所有客户端的请求参数为utf8编码