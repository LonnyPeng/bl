<?php

return array(
    'default/index'     => '',
    'wechat/callback' => 'oauth-callback',
    'common/qrcode'     => 'qrcode/<key?:.*>.png',
    'order/info'        => 'order-<key?:\d{7,10}>',
    '*/*'               => '<_controller:[^/]+>/<_action:[^/]+>',
);
