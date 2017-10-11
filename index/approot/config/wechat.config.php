<?php

return [
    /**
     * Debug 模式，bool 值：true/false
     *
     * 当值为 false 时，所有的日志都不会记录
     */
    'debug'  => true,
    /**
     * 账号基本信息，请从微信公众平台/开放平台获取
     */
    'app_id'  => 'wx08c0209872a3db2e',         // AppID 
    'secret'  => '3af5c47b0dc4e8d88a6fc14546c5a312',     // AppSecret 
    'token'   => 'weixin',          // Token
    'aes_key' => '',                    // EncodingAESKey，安全模式下请一定要填写！！！
    /**
     * 日志配置
     *
     * level: 日志级别, 可选为：
     *         debug/info/notice/warning/error/critical/alert/emergency
     * permission：日志文件权限(可选)，默认为null（若为null值,monolog会取0644）
     * file：日志文件位置(绝对路径!!!)，要求可写权限
     */
    'log' => [
        'level'      => 'debug',
        'permission' => 0777,
        'file'       => '/tmp/easywechat.log',
    ],
    /**
     * OAuth 配置
     *
     * scopes：公众平台（snsapi_userinfo / snsapi_base），开放平台：snsapi_login
     * callback：OAuth授权完成后的回调页地址
     */
    'oauth' => [
        'scopes'   => ['snsapi_userinfo'],
        'callback' => '/index/oauth-callback',
    ],
    /**
     * 微信支付
     */
    'payment' => [
        'merchant_id'        => '1900009851',
        'key'                => '8934e7d15453e97507ef794cf7b0519d',
        'cert_path'          => ROOT_DIR . 'cert/apiclient_cert.pem', // XXX: 绝对路径！！！！
        'key_path'           => ROOT_DIR . 'cert/apiclient_key.pem',      // XXX: 绝对路径！！！！
        'notify_url'         => HTTP_SERVER . BASE_PATH . 'order-notify', // 支付结果通知网址，如果不设置则会使用配置里的默认地址
        // 'device_info'     => '013467007045764',
        // 'sub_app_id'      => '',
        // 'sub_merchant_id' => '',
        // ...
    ],
    /**
     * Guzzle 全局设置
     *
     * 更多请参考： http://docs.guzzlephp.org/en/latest/request-options.html
     */
    'guzzle' => [
        'timeout' => 3.0, // 超时时间（秒）
        //'verify' => false, // 关掉 SSL 认证（强烈不建议！！！）
    ],
];